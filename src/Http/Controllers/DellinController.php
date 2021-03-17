<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use SergeevPasha\Dellin\DTO\Delivery;
use Illuminate\Validation\ValidationException;
use SergeevPasha\Dellin\Libraries\DellinClient;
use SergeevPasha\Dellin\Http\Requests\DellinTerminalRequest;
use SergeevPasha\Dellin\Http\Requests\DellinQueryCityRequest;
use SergeevPasha\Dellin\Http\Requests\DellinQueryStreetRequest;
use SergeevPasha\Dellin\Http\Requests\DellinCalculatePriceRequest;
use SergeevPasha\Dellin\Http\Requests\DellinCounterpartiesRequest;

class DellinController
{
    /**
     * Dellin Client Instance.
     *
     * @var \SergeevPasha\Dellin\Libraries\DellinClient
     */
    private DellinClient $client;

    public function __construct(DellinClient $client)
    {
        $this->client = $client;
    }

    /**
     * Check if required key is isset and fail if not
     *
     * @param array<mixed> $data
     * @param string|null  $key
     *
     * @throws \Exception
     * @return array<mixed>
     */
    public function responseOrFail(array $data, string $key = null): array
    {
        $response = [];
        if ($key) {
            if (!isset($data[$key])) {
                throw new Exception('Missing required parameters or session ID is expired');
            }
            $response['data'] = $data[$key];
        } else {
            $response['data'] = $data;
        }
        return $response;
    }

    /**
     * Query City.
     *
     * @param \SergeevPasha\Dellin\Http\Requests\DellinQueryCityRequest $request
     *
     * @throws \Exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function queryCity(DellinQueryCityRequest $request): JsonResponse
    {
        $data     = $this->client->findCity($request->query('query'));
        $response = $this->responseOrFail($data, 'cities');
        return response()->json($response);
    }

    /**
     * Get terminals for the city.
     *
     * @param int                                                      $city
     * @param \SergeevPasha\Dellin\Http\Requests\DellinTerminalRequest $request
     *
     * @throws \Exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTerminals(int $city, DellinTerminalRequest $request): JsonResponse
    {
        $data     = $this->client->getCityTerminals($city, (bool) $request->query('arrival'));
        $response = $this->responseOrFail($data, 'terminals');
        return response()->json($response);
    }

    /**
     * Query Street.
     *
     * @param int                                                         $city
     * @param \SergeevPasha\Dellin\Http\Requests\DellinQueryStreetRequest $request
     *
     * @throws \Exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function queryStreet(int $city, DellinQueryStreetRequest $request): JsonResponse
    {
        $data     = $this->client->findCityStreet($city, $request->query('query'));
        $response = $this->responseOrFail($data, 'streets');
        return response()->json($response);
    }

    /**
     * Get available package types.
     *
     * @throws \Exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailablePackages(): JsonResponse
    {
        $data     = $this->client->getAvailablePackages();
        $response = $this->responseOrFail($data, 'data');
        return response()->json($response);
    }

    /**
     * Get special requirements for your cargo handling.
     *
     * @throws \Exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSpecialRequirements(): JsonResponse
    {
        $data     = $this->client->getSpecialRequirements();
        $response = $this->responseOrFail($data, 'data');
        return response()->json($response);
    }

    /**
     * Get Counterparties data
     *
     * @param \SergeevPasha\Dellin\Http\Requests\DellinCounterpartiesRequest $request
     *
     * @throws \Exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCounterparties(DellinCounterpartiesRequest $request): JsonResponse
    {
        $data             = $this->client->getCounterparties($request->query('session_id', $request->query('expand')));
        $data             = $this->responseOrFail($data, 'data');
        $response['data'] = $data['data']['counteragents'];
        return response()->json($response);
    }

    /**
     * Calculate delivery.
     *
     * @param \SergeevPasha\Dellin\Http\Requests\DellinCalculatePriceRequest $request
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateDeliveryPrice(DellinCalculatePriceRequest $request): JsonResponse
    {
        $data = $this->client->getPrice(Delivery::fromArray($request->all()));
        if (array_key_exists('errors', $data)) {
            $messages = [];
            foreach ($data['errors'] as $error) {
                $messages[$error['code']] = implode(',', $error['fields']) . ' - ' . trim($error['detail'], '.') . '.';
            }
            throw ValidationException::withMessages($messages);
        }
        $response = $this->responseOrFail($data, 'data');
        return response()->json($response);
    }
}
