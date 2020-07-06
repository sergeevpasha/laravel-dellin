<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use SergeevPasha\Dellin\Libraries\DellinClient;

final class AuthDellinController
{
    /**
     * Dellin Client Instance.
     *
     * @var \SergeevPasha\Dellin\Libraries\DellinClient
     */
    private $client;

    public function __construct(DellinClient $client)
    {
        $this->client = $client;
    }

    /**
     * Authorize Dellin User to get modified prices.
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $data = $this->client->authorize();
        if (isset($data['errors'])) {
            throw ValidationException::withMessages([
                'key' => trans('dellin::messages.invalid', ['attribute' => 'key']),
            ]);
        }
        if (isset($data['message'])) {
            throw ValidationException::withMessages([
                'login' => trans('dellin::messages.invalid', ['attribute' => 'login']),
                'password'   => trans('dellin::messages.invalid', ['attribute' => 'password'])
            ]);
        }
        if (!isset($data['sessionID'])) {
            throw new Exception();
        }
        $response = [
            'message' => trans('dellin::messages.success_login'),
            'session' => [
                'id' => $data['sessionID'],
                'expires' => $data['session']['expire_time']
            ]
        ];
        return response()->json($response);
    }
}
