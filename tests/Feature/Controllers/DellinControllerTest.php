<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Tests\Feature\Controllers;

use Mockery;
use Exception;
use Illuminate\Http\JsonResponse;
use SergeevPasha\Dellin\Tests\TestCase;
use SergeevPasha\Dellin\Enum\PaymentType;
use SergeevPasha\Dellin\Enum\DeliveryType;
use SergeevPasha\Dellin\Enum\ShippingType;
use SergeevPasha\Dellin\Enum\RequesterRole;
use Illuminate\Validation\ValidationException;
use SergeevPasha\Dellin\Libraries\DellinClient;
use SergeevPasha\Dellin\Http\Controllers\DellinController;
use SergeevPasha\Dellin\Http\Requests\DellinTerminalRequest;
use SergeevPasha\Dellin\Http\Requests\DellinQueryCityRequest;
use SergeevPasha\Dellin\Http\Requests\DellinQueryStreetRequest;
use SergeevPasha\Dellin\Http\Requests\DellinCalculatePriceRequest;
use SergeevPasha\Dellin\Http\Requests\DellinCounterpartiesRequest;

class DellinControllerTest extends TestCase
{
    /**
     * Default Response.
     *
     * @var array
     */
    protected array $defaultResponse = [
        'data' => [
            'counteragents' => 'text'
        ],
        'cities' => 'text',
        'terminals' => 'text',
        'streets' => 'text',
    ];

    /**
     * Controller.
     *
     * @var \SergeevPasha\Dellin\Http\Controllers\DellinController
     */
    protected DellinController $controller;

    /**
     * Set Up requirements.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $client = Mockery::mock(DellinClient::class);
        $client->shouldReceive('findCity')->andReturn($this->defaultResponse);
        $client->shouldReceive('getCityTerminals')->andReturn($this->defaultResponse);
        $client->shouldReceive('findCityStreet')->andReturn($this->defaultResponse);
        $client->shouldReceive('getAvailablePackages')->andReturn($this->defaultResponse);
        $client->shouldReceive('getSpecialRequirements')->andReturn($this->defaultResponse);
        $client->shouldReceive('getCounterparties')->andReturn($this->defaultResponse);
        $client->shouldReceive('getPrice')->andReturn($this->defaultResponse);
        $this->app->instance(DellinClient::class, $client);
        $this->controller = $this->app->make(DellinController::class);
    }

    public function testResponseOrFail()
    {
        $request = [
            'value' => 'text',
        ];
        $expected = [
            'data' => 'text'
        ];
        $result = $this->controller->responseOrFail($request, 'value');
        $this->assertEqualsCanonicalizing($expected, $result);
        $result = $this->controller->responseOrFail($request);
        $this->assertEqualsCanonicalizing($expected, $result['data']);
        $this->expectException(Exception::class);
        $this->controller->responseOrFail([], 'text');
    }
    
    public function testQueryCity()
    {
        $request = new DellinQueryCityRequest([
            'query' => 'string',
        ]);
        $method = $this->controller->queryCity($request);
        $this->assertInstanceOf(JsonResponse::class, $method);
    }

    public function testGetTerminals()
    {
        $request = new DellinTerminalRequest([
            'arrival' => '1',
        ]);
        $method = $this->controller->getTerminals(1, $request);
        $this->assertInstanceOf(JsonResponse::class, $method);
    }

    public function testQueryStreet()
    {
        $request = new DellinQueryStreetRequest([
            'query' => 'string',
        ]);
        $method = $this->controller->queryStreet(1, $request);
        $this->assertInstanceOf(JsonResponse::class, $method);
    }

    public function testGetCounterparties()
    {
        $request = new DellinCounterpartiesRequest([
            'session_id' => 'string',
            'expand' => '1',
        ]);
        $method = $this->controller->getCounterparties($request);
        $this->assertInstanceOf(JsonResponse::class, $method);
    }

    public function testGetAvailablePackages()
    {
        $method = $this->controller->getAvailablePackages();
        $this->assertInstanceOf(JsonResponse::class, $method);
    }

    public function testGetSpecialRequirements()
    {
        $method = $this->controller->getSpecialRequirements();
        $this->assertInstanceOf(JsonResponse::class, $method);
    }

    public function testCalculateDeliveryPrice()
    {
        $request = new DellinCalculatePriceRequest([
            'delivery_type'         => DeliveryType::getRandomValue(),
            'arrival_shipping_type' => ShippingType::getRandomValue(),
            'derival_produce_date'  => '2100-10-10',
            'derival_shipping_type' => ShippingType::getRandomValue(),
            'requester_role'        => RequesterRole::getRandomValue(),
            'requester_uid'         => '1234567890',
            'quantity'              => '1',
            'cargo_length'          => '1',
            'cargo_height'          => '1',
            'cargo_width'           => '1',
            'cargo_weight'          => '1',
            'cargo_total_volume'    => '1',
            'cargo_total_weight'    => '1',
            'ac_docs_send'          => '1',
            'ac_docs_return'        => '1',
            'payment_city'          => '110011001100',
            'payment_type'          => PaymentType::getRandomValue(),
            'packages[0][uid]'      => '1100',
            'packages[0][count]'    => '1',
        ]);
        $method = $this->controller->calculateDeliveryPrice($request);
        $this->assertInstanceOf(JsonResponse::class, $method);
    }

    
    public function testCalculateDeliveryPriceFails()
    {
        $request = new DellinCalculatePriceRequest([
            'delivery_type'         => DeliveryType::getRandomValue(),
            'arrival_shipping_type' => ShippingType::getRandomValue(),
            'derival_produce_date'  => '2100-10-10',
            'derival_shipping_type' => ShippingType::getRandomValue(),
            'requester_role'        => RequesterRole::getRandomValue(),
            'requester_uid'         => '1234567890',
            'quantity'              => '1',
            'cargo_length'          => '1',
            'cargo_height'          => '1',
            'cargo_width'           => '1',
            'cargo_weight'          => '1',
            'cargo_total_volume'    => '1',
            'cargo_total_weight'    => '1',
            'ac_docs_send'          => '1',
            'ac_docs_return'        => '1',
            'payment_city'          => '110011001100',
            'payment_type'          => PaymentType::getRandomValue(),
            'packages[0][uid]'      => '1100',
            'packages[0][count]'    => '1',
        ]);
        $client = Mockery::mock(DellinClient::class);
        $expectedData = [
            'errors' => [
                0 => [
                    'detail' => 'text'
                ]
            ]
        ];
        $client->shouldReceive('getPrice')->andReturn($expectedData);
        $this->app->instance(DellinClient::class, $client);
        $class = $this->app->make(DellinController::class);
        $this->expectException(ValidationException::class);
        $method = $class->calculateDeliveryPrice($request);
    }
}
