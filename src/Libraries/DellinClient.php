<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Libraries;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use SergeevPasha\Dellin\DTO\Delivery;
use SergeevPasha\Dellin\DTO\DellinTrack;
use SergeevPasha\Dellin\Exceptions\DellinApiException;
use SergeevPasha\Dellin\Helpers\DellinHelper;

class DellinClient
{
    /**
     * Dellin recommends a 10 s connection timeout; the calculator alone takes up to ~10 s to answer.
     */
    private const CONNECT_TIMEOUT = 10;
    private const TIMEOUT         = 30;

    private const ERROR_ORDER_NOT_FOUND = 717001;

    /**
     * Dellin key.
     *
     * @var string
     */
    private string $key;

    /**
     * HTTP client.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    private ClientInterface $http;

    /**
     * Constructor
     *
     * @param string                          $key
     * @param \GuzzleHttp\ClientInterface|null $http
     *
     * @return void
     */
    public function __construct(string $key, ?ClientInterface $http = null)
    {
        $this->key  = $key;
        $this->http = $http ?? new GuzzleClient();
    }

    /**
     * Authorize a User to get modified prices.
     *
     * @param string|null $login
     * @param string|null $password
     *
     * @return array
     */
    public function authorize(?string $login = null, ?string $password = null): array
    {
        $data = $this->request(
            'v2/customers/login',
            [
                'login'    => $login ?? config('dellin.login'),
                'password' => $password ?? config('dellin.password'),
            ]
        );
        if (isset($data['sessionID'])) {
            $session = $this->request(
                'v1/customers/session_info',
                [
                    'sessionId' => $data['sessionID'],
                ]
            );
            $data    = array_merge($data, $session);
        }

        return $data;
    }

    /**
     * Send request to Dellin API.
     *
     * 4xx answers with a JSON body are returned as is, callers read Dellin's "errors" from them.
     *
     * @param string $path
     * @param array  $params
     * @param string $method
     *
     * @return array
     * @throws \SergeevPasha\Dellin\Exceptions\DellinApiException on a transport error or timeout,
     *     HTTP 429, HTTP 5xx or a body that is not a JSON object
     */
    public function request(string $path, array $params = [], string $method = 'POST'): array
    {
        return $this->send($path, $params, $method)['body'];
    }

    /**
     * @param string $path
     * @param array  $params
     * @param string $method
     *
     * @return array{status: int, body: array}
     * @throws \SergeevPasha\Dellin\Exceptions\DellinApiException
     */
    private function send(string $path, array $params, string $method = 'POST'): array
    {
        $params['appkey'] = $this->key;
        try {
            $response = $this->http->request($method, "https://api.dellin.ru/$path.json", [
                'headers'         => [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json'            => $params,
                'http_errors'     => false,
                'decode_content'  => false,
                'connect_timeout' => self::CONNECT_TIMEOUT,
                'timeout'         => self::TIMEOUT,
            ]);
        } catch (GuzzleException $exception) {
            throw DellinApiException::transport($path, $exception);
        }

        $status = $response->getStatusCode();
        $raw    = (string) $response->getBody();
        $body   = json_decode($raw, true);

        if ($status === 429 || $status >= 500 || !is_array($body)) {
            throw DellinApiException::response($path, $status, is_array($body) ? $body : null, $raw);
        }

        return ['status' => $status, 'body' => $body];
    }

    /**
     * Send a request with a session. A cached session Dellin rejects (HTTP 401) is replaced
     * by a fresh one and the request is retried once.
     *
     * @param string      $path
     * @param array       $params
     * @param string|null $session Explicit session; it is never replaced
     *
     * @return array{status: int, body: array}
     * @throws \SergeevPasha\Dellin\Exceptions\DellinApiException
     */
    private function sendWithSession(string $path, array $params, ?string $session = null): array
    {
        $cached   = $session === null;
        $session  = $session ?? $this->getSession();
        $response = $this->send($path, self::withSession($params, $session));

        if ($response['status'] === 401 && $cached && $session !== null) {
            $this->forgetSession();
            $response = $this->send($path, self::withSession($params, $this->getSession()));
        }

        if ($response['status'] === 401) {
            throw DellinApiException::response($path, 401, $response['body']);
        }

        return $response;
    }

    private static function withSession(array $params, ?string $session): array
    {
        return $session === null ? $params : ['sessionID' => $session] + $params;
    }

    /**
     * Regions 01-09 have 24-digit codes in Dellin: the same code with a leading zero
     * makes v2/public/kladr answer HTTP 500.
     */
    private static function normalizeCode(string $code): string
    {
        return strlen($code) === 25 && $code[0] === '0' ? substr($code, 1) : $code;
    }

    /**
     * Get or fetch a cached session ID.
     * Session lives 30 days; cache for 28 to avoid using an about-to-expire one.
     * Returns null if credentials are not configured or Dellin rejected them.
     */
    public function getSession(): ?string
    {
        $login    = config('dellin.login');
        $password = config('dellin.password');

        if (!$login || !$password) {
            return null;
        }

        return Cache::remember(self::sessionCacheKey($login), now()->addDays(28), function () use ($login, $password) {
            $data = $this->request('v3/auth/login', compact('login', 'password'));
            return $data['data']['sessionID'] ?? null;
        });
    }

    /**
     * Forget the cached session (call after receiving a session-expired error).
     */
    public function forgetSession(): void
    {
        $login = config('dellin.login');
        if ($login) {
            Cache::forget(self::sessionCacheKey($login));
        }
    }

    private static function sessionCacheKey(string $login): string
    {
        return 'dellin_session_' . md5($login);
    }

    /**
     * Find track by number.
     *
     * An order Dellin does not know (error 717001 or no orders in the answer) comes back
     * with status null and notFound = true.
     *
     * @param string $trackNumber
     *
     * @return \SergeevPasha\Dellin\DTO\DellinTrack
     * @throws \SergeevPasha\Dellin\Exceptions\DellinApiException when Dellin could not answer
     */
    public function findByTrackNumber(string $trackNumber): DellinTrack
    {
        $path     = 'v3/orders';
        $response = $this->sendWithSession($path, ['docIds' => [$trackNumber]]);

        $body     = $response['body'];
        $notFound = self::hasErrorCode($body, self::ERROR_ORDER_NOT_FOUND)
            || (array_key_exists('orders', $body) && $body['orders'] === []);

        // A 2xx without orders (gateway "Unauthorized", an empty body) is a failure, not an unknown order
        if (!$notFound && ($response['status'] >= 400 || !isset($body['orders'][0]))) {
            throw DellinApiException::response($path, $response['status'], $body);
        }

        return DellinTrack::fromArray($body);
    }

    private static function hasErrorCode(array $body, int $code): bool
    {
        foreach ((array) ($body['errors'] ?? []) as $error) {
            if (is_array($error) && (int) ($error['code'] ?? 0) === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get Dellin cities CSV File path.
     *
     * @return string|null
     */
    public function getCitiesCSV(): ?string
    {
        $data = $this->request('v1/public/cities');
        return $data['url'] ?? null;
    }

    /**
     * Get Dellin places CSV File path.
     *
     * @return string|null
     */
    public function getPlacesCSV(): ?string
    {
        $data = $this->request('v1/public/places');
        return $data['url'] ?? null;
    }

    /**
     * Get Streets CSV File path.
     *
     * @return string|null
     */
    public function getStreetsCSV(): ?string
    {
        $data = $this->request('v1/public/streets');
        return $data['url'] ?? null;
    }

    /**
     * Find a city by query string.
     *
     * @param string $query
     *
     * @return array
     */
    public function findCity(string $query): array
    {
        return $this->request(
            'v2/public/kladr',
            [
                'q' => $query,
            ]
        );
    }

    /**
     * Find a city by its Dellin KLADR code.
     *
     * @param string $code 24 or 25 digits
     *
     * @return array
     */
    public function findCityByCode(string $code): array
    {
        return $this->request('v2/public/kladr', ['code' => self::normalizeCode($code)]);
    }

    /**
     * Find a street by query string and City ID.
     *
     * @param int    $city
     * @param string $query
     *
     * @return array
     */
    public function findCityStreet(int $city, string $query): array
    {
        return $this->request(
            'v1/public/kladr_street',
            [
                'cityID' => $city,
                'street' => $query,
            ]
        );
    }

    /**
     * Get every terminal in the city.
     *
     * @param int $city
     * @param bool $arrival
     * @param bool|null $express
     * @return array
     */
    public function getCityTerminals(int $city, bool $arrival = true, ?bool $express = null): array
    {
        $direction = $arrival ? 'arrival' : 'derival';
        $params = [
            'cityID'    => $city,
            'direction' => $direction,
        ];

        if ($express !== null) {
            $params['express'] = $express;
        }

        return $this->request('v1/public/request_terminals', $params);
    }

    /**
     * Get available package types.
     *
     * @return array
     */
    public function getAvailablePackages(): array
    {
        return $this->request('v1/references/packages');
    }

    /**
     * Get special requirements for your cargo handling.
     *
     * @return array
     */
    public function getSpecialRequirements(): array
    {
        return $this->request('v1/references/services');
    }

    /**
     * Get the dimensions and weight above which a cargo place is oversized, per service kind
     * (auto, express, avia). A zero threshold means no limit.
     *
     * @return array
     */
    public function getOversizedThresholds(): array
    {
        return $this->request('v1/oversized_cargo_thresholds');
    }

    /**
     * Get Orders History.
     *
     * @param array $ordersIds
     * @return array
     */
    public function getOrdersHistory(array $ordersIds): array
    {
        return $this->request('v3/orders/statuses_history', [
            'docIds' => $ordersIds
        ]);
    }

    /**
     * Get Order History.
     *
     * @param string $orderId
     * @return array
     */
    public function getOrderHistory(string $orderId): array
    {
        return $this->getOrdersHistory([$orderId]);
    }

    /**
     * Get Counterparties data
     *
     * @param string $session
     * @param bool   $expanded
     *
     * @return array
     */
    public function getCounterparties(string $session, bool $expanded = false): array
    {
        return $this->request(
            'v2/counteragents',
            [
                'sessionID' => $session,
                'fullInfo'  => $expanded
            ]
        );
    }

    /**
     * Get calculated price.
     *
     * Validation errors come back as {"errors":[...]} with HTTP 400.
     *
     * @param \SergeevPasha\Dellin\DTO\Delivery $delivery
     *
     * @return array
     * @throws \SergeevPasha\Dellin\Exceptions\DellinApiException
     */
    public function getPrice(Delivery $delivery): array
    {
        $builder         = new RequestBuilder();
        $deliveryRequest = [
            'deliveryType' => $builder->buildDeliveryType($delivery->deliveryType),
            'arrival'      => $builder->buildArrival($delivery->arrival),
            'derival'      => $builder->buildDerival($delivery->derival),
        ];
        if ($delivery->packages) {
            $deliveryRequest['packages'] = $builder->buildPackages($delivery->packages);
        }
        if ($delivery->acDocs) {
            $deliveryRequest['accompanyingDocuments'] = $builder->buildAcDocs($delivery->acDocs);
        }
        if ($delivery->requester) {
            $members = [
                'members' => [
                    'requester' => $builder->buildRequester($delivery->requester),
                ]
            ];
        } else {
            $members = [
                'members' => []
            ];
        }
        $cargo   = $builder->buildCargo($delivery->cargo);
        $payment = $builder->buildPayment($delivery->payment);
        $request = array_merge(
            [
                'delivery' => $deliveryRequest,
                'cargo'    => $cargo,
                'payment'  => $payment,
            ],
            $members
        );
        $data    = DellinHelper::removeNullValues($request);
        return $this->sendWithSession('v2/calculator', $data, $delivery->session)['body'];
    }
}
