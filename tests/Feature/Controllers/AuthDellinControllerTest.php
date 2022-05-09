<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Tests\Feature\Controllers;

use Mockery;
use Exception;
use Illuminate\Http\JsonResponse;
use SergeevPasha\Dellin\Tests\TestCase;
use Illuminate\Validation\ValidationException;
use SergeevPasha\Dellin\Libraries\DellinClient;
use SergeevPasha\Dellin\Http\Controllers\AuthDellinController;

class AuthDellinControllerTest extends TestCase
{
    public function testInvokeAuthorization()
    {
        $client = Mockery::mock(DellinClient::class);
        $expectedData = [
            'sessionID' => '123',
            'session' => [
                'expire_time' => '2020-10-10'
            ]
        ];
        $client->shouldReceive('authorize')->andReturn($expectedData);
        $this->app->instance(DellinClient::class, $client);
        $class = $this->app->make(AuthDellinController::class);
        $method = $class->__invoke();
        $this->assertInstanceOf(JsonResponse::class, $method);
    }

    public function testInvokeAuthorizationFailsIfGotServerErrors()
    {
        $client = Mockery::mock(DellinClient::class);
        $expectedData = [
            'errors' => 'text'
        ];
        $client->shouldReceive('authorize')->andReturn($expectedData);
        $this->app->instance(DellinClient::class, $client);
        $class = $this->app->make(AuthDellinController::class);
        $this->expectException(ValidationException::class);
        $class->__invoke();
    }

    public function testInvokeAuthorizationFailsIfGotErrorMEssages()
    {
        $client = Mockery::mock(DellinClient::class);
        $expectedData = [
            'message' => 'text'
        ];
        $client->shouldReceive('authorize')->andReturn($expectedData);
        $this->app->instance(DellinClient::class, $client);
        $class = $this->app->make(AuthDellinController::class);
        $this->expectException(ValidationException::class);
        $class->__invoke();
    }

    public function testInvokeAuthorizationFailsIfGotNoSession()
    {
        $client = Mockery::mock(DellinClient::class);
        $expectedData = [];
        $client->shouldReceive('authorize')->andReturn($expectedData);
        $this->app->instance(DellinClient::class, $client);
        $class = $this->app->make(AuthDellinController::class);
        $this->expectException(Exception::class);
        $class->__invoke();
    }
}
