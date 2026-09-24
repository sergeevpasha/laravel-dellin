<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Tests\Unit\Libraries;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use SergeevPasha\Dellin\DTO\Delivery;
use SergeevPasha\Dellin\Exceptions\DellinApiException;
use SergeevPasha\Dellin\Libraries\DellinClient;
use SergeevPasha\Dellin\Tests\TestCase;

class DellinClientTest extends TestCase
{
    private const LOGIN = '+70000000000';

    private MockHandler $handler;

    /**
     * @var array<int, array{request: \Psr\Http\Message\RequestInterface, options: array}>
     */
    private array $history = [];

    private DellinClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'cache.default'   => 'array',
            'dellin.login'    => self::LOGIN,
            'dellin.password' => 'secret',
        ]);

        $this->handler = new MockHandler();
        $stack         = HandlerStack::create($this->handler);
        $stack->push(Middleware::history($this->history));
        $this->client = new DellinClient('key', new GuzzleClient(['handler' => $stack]));
    }

    public function testRequestReturnsJsonBodyOfClientErrors(): void
    {
        $this->queue(400, ['errors' => [['code' => 110001, 'detail' => 'КЛАДР должен состоять из цифр']]]);

        $data = $this->client->request('v2/calculator');

        $this->assertSame(110001, $data['errors'][0]['code']);
    }

    public function testRequestSendsTimeoutsAndAppKey(): void
    {
        $this->queue(200, ['cities' => []]);

        $this->client->request('v2/public/kladr', ['q' => 'Уфа']);

        $options = $this->history[0]['options'];
        $this->assertSame(10, $options['connect_timeout']);
        $this->assertSame(30, $options['timeout']);
        $this->assertSame(['q' => 'Уфа', 'appkey' => 'key'], $this->sentBody(0));
    }

    public function testRequestThrowsOnRateLimit(): void
    {
        $this->queue(429, ['error' => ['code' => '429', 'description' => 'Превышена допустимая частота запросов.']]);

        try {
            $this->client->request('v2/public/kladr', ['q' => 'Уфа']);
            $this->fail('DellinApiException expected');
        } catch (DellinApiException $exception) {
            $this->assertSame(429, $exception->getStatus());
            $this->assertStringContainsString('v2/public/kladr', $exception->getMessage());
        }
    }

    public function testRequestThrowsOnServerErrorWithTextBody(): void
    {
        $this->handler->append(new Response(500, [], 'Internal server error'));

        try {
            $this->client->request('v2/public/kladr', ['code' => '0200000100000000000000000']);
            $this->fail('DellinApiException expected');
        } catch (DellinApiException $exception) {
            $this->assertSame(500, $exception->getStatus());
            $this->assertStringContainsString('Internal server error', $exception->getMessage());
        }
    }

    public function testRequestThrowsOnNonJsonSuccessBody(): void
    {
        $this->handler->append(new Response(200, [], '<html><body>Access denied</body></html>'));

        $this->expectException(DellinApiException::class);
        $this->expectExceptionMessage('HTTP 200');

        $this->client->request('v3/orders');
    }

    public function testRequestWrapsTransportErrors(): void
    {
        $this->handler->append(new ConnectException('cURL error 28: timed out', new Request('POST', 'v3/orders')));

        try {
            $this->client->request('v3/orders');
            $this->fail('DellinApiException expected');
        } catch (DellinApiException $exception) {
            $this->assertNull($exception->getStatus());
            $this->assertInstanceOf(ConnectException::class, $exception->getPrevious());
        }
    }

    public function testFindByTrackNumberRenewsRejectedSessionOnce(): void
    {
        Cache::put($this->sessionKey(), 'stale');
        $this->queue(401, ['errors' => 'Unauthorized']);
        $this->queue(200, ['data' => ['sessionID' => 'fresh']]);
        $this->queue(200, ['orders' => [['orderId' => '62727177', 'stateName' => 'Заказ завершен']]]);

        $track = $this->client->findByTrackNumber('62727177');

        $this->assertSame('Заказ завершен', $track->status);
        $this->assertFalse($track->notFound);
        $this->assertSame('stale', $this->sentBody(0)['sessionID']);
        $this->assertStringEndsWith('v3/auth/login.json', (string) $this->history[1]['request']->getUri());
        $this->assertSame('fresh', $this->sentBody(2)['sessionID']);
        $this->assertSame('fresh', Cache::get($this->sessionKey()));
    }

    public function testFindByTrackNumberThrowsWhenFreshSessionIsRejectedToo(): void
    {
        Cache::put($this->sessionKey(), 'stale');
        $this->queue(401, ['errors' => 'Unauthorized']);
        $this->queue(200, ['data' => ['sessionID' => 'fresh']]);
        $this->queue(401, ['errors' => 'Unauthorized']);

        try {
            $this->client->findByTrackNumber('62727177');
            $this->fail('DellinApiException expected');
        } catch (DellinApiException $exception) {
            $this->assertSame(401, $exception->getStatus());
            $this->assertStringContainsString('Unauthorized', $exception->getMessage());
        }
        $this->assertCount(3, $this->history);
    }

    public function testFindByTrackNumberFlagsUnknownOrder(): void
    {
        Cache::put($this->sessionKey(), 'session');
        $this->queue(400, [
            'metadata' => ['status' => 400],
            'errors'   => [['code' => 717001, 'title' => 'Заказ не найден', 'detail' => 'Заказ 1 не найден']],
        ]);

        $track = $this->client->findByTrackNumber('1');

        $this->assertNull($track->status);
        $this->assertTrue($track->notFound);
    }

    /**
     * @dataProvider successfulAnswersWithoutOrder
     */
    public function testFindByTrackNumberThrowsOnSuccessfulAnswerWithoutOrder(array $body): void
    {
        Cache::put($this->sessionKey(), 'session');
        $this->queue(200, $body);

        $this->expectException(DellinApiException::class);

        $this->client->findByTrackNumber('1');
    }

    public function successfulAnswersWithoutOrder(): array
    {
        return [
            'gateway unauthorized' => [['errors' => 'Unauthorized']],
            'status in metadata'   => [['metadata' => ['status' => 401]]],
            'empty body'           => [[]],
        ];
    }

    public function testFindByTrackNumberFlagsEmptyOrderList(): void
    {
        Cache::put($this->sessionKey(), 'session');
        $this->queue(200, ['orders' => []]);

        $this->assertTrue($this->client->findByTrackNumber('1')->notFound);
    }

    public function testFindByTrackNumberThrowsOnOtherClientErrors(): void
    {
        Cache::put($this->sessionKey(), 'session');
        $this->queue(400, ['errors' => [['code' => 110001, 'detail' => 'Неверный формат']]]);

        $this->expectException(DellinApiException::class);
        $this->expectExceptionMessage('110001');

        $this->client->findByTrackNumber('bad');
    }

    public function testFindByTrackNumberThrowsOnRateLimit(): void
    {
        Cache::put($this->sessionKey(), 'session');
        $this->queue(429, ['error' => ['code' => '429', 'description' => 'Превышена допустимая частота запросов.']]);

        $this->expectException(DellinApiException::class);

        $this->client->findByTrackNumber('62727177');
    }

    public function testLoginServerErrorThrowsInsteadOfTypeError(): void
    {
        $this->handler->append(new Response(502, [], '<html>Bad Gateway</html>'));

        try {
            $this->client->getSession();
            $this->fail('DellinApiException expected');
        } catch (DellinApiException $exception) {
            $this->assertSame(502, $exception->getStatus());
        }
    }

    public function testGetPriceRenewsRejectedCachedSession(): void
    {
        Cache::put($this->sessionKey(), 'stale');
        $this->queue(401, ['errors' => 'Unauthorized']);
        $this->queue(200, ['data' => ['sessionID' => 'fresh']]);
        $this->queue(200, ['data' => ['price' => 1000]]);

        $data = $this->client->getPrice(Delivery::fromArray($this->deliveryData()));

        $this->assertSame(1000, $data['data']['price']);
        $this->assertSame('fresh', $this->sentBody(2)['sessionID']);
    }

    public function testGetPriceDoesNotReplaceExplicitSession(): void
    {
        Cache::put($this->sessionKey(), 'cached');
        $this->queue(401, ['errors' => 'Unauthorized']);

        try {
            $this->client->getPrice(Delivery::fromArray($this->deliveryData() + ['session_id' => 'explicit']));
            $this->fail('DellinApiException expected');
        } catch (DellinApiException $exception) {
            $this->assertSame(401, $exception->getStatus());
        }
        $this->assertSame('explicit', $this->sentBody(0)['sessionID']);
        $this->assertCount(1, $this->history);
        $this->assertSame('cached', Cache::get($this->sessionKey()));
    }

    public function testGetPriceReturnsValidationErrors(): void
    {
        Cache::put($this->sessionKey(), 'session');
        $this->queue(400, ['errors' => [['code' => 110004, 'fields' => ['delivery.derival.address']]]]);

        $data = $this->client->getPrice(Delivery::fromArray($this->deliveryData()));

        $this->assertSame(110004, $data['errors'][0]['code']);
    }

    public function testFindCityByCodeDropsLeadingZeroOfShortRegionCodes(): void
    {
        $this->queue(200, ['cities' => []]);
        $this->queue(200, ['cities' => []]);

        $this->client->findCityByCode('0200000100000000000000000');
        $this->client->findCityByCode('6600000100000000000000000');

        $this->assertSame('200000100000000000000000', $this->sentBody(0)['code']);
        $this->assertSame('6600000100000000000000000', $this->sentBody(1)['code']);
    }

    private function queue(int $status, array $body): void
    {
        $this->handler->append(new Response($status, [], json_encode($body, JSON_UNESCAPED_UNICODE)));
    }

    private function sentBody(int $index): array
    {
        return json_decode((string) $this->history[$index]['request']->getBody(), true);
    }

    private function sessionKey(): string
    {
        return 'dellin_session_' . md5(self::LOGIN);
    }

    private function deliveryData(): array
    {
        return [
            'delivery_type'         => '0',
            'arrival_shipping_type' => '1',
            'arrival_terminal_id'   => '1',
            'derival_produce_date'  => '2100-10-10',
            'derival_shipping_type' => '1',
            'derival_terminal_id'   => '2',
            'cargo_quantity'        => '1',
            'cargo_length'          => '1',
            'cargo_height'          => '1',
            'cargo_width'           => '1',
            'cargo_weight'          => '1',
            'cargo_total_volume'    => '1',
            'cargo_total_weight'    => '1',
            'payment_city'          => '6600000100000000000000000',
            'payment_type'          => '1',
        ];
    }
}
