<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use SergeevPasha\Dellin\DTO\Delivery;
use SergeevPasha\Dellin\Http\Requests\DellinCalculatePriceRequest;
use SergeevPasha\Dellin\Http\Requests\DellinCounterpartiesRequest;
use SergeevPasha\Dellin\Http\Requests\DellinOrderHistoryRequest;
use SergeevPasha\Dellin\Http\Requests\DellinQueryCityRequest;
use SergeevPasha\Dellin\Http\Requests\DellinQueryStreetRequest;
use SergeevPasha\Dellin\Http\Requests\DellinTerminalRequest;
use SergeevPasha\Dellin\Libraries\DellinClient;

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
     * Query City.
     *
     * @param \SergeevPasha\Dellin\Http\Requests\DellinQueryCityRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function queryCity(DellinQueryCityRequest $request): JsonResponse
    {
        $data = $this->client->findCity($request->query('query'));
        $response = $this->responseOrFail($data, 'cities');
        return response()->json($response);
    }

    /**
     * Check if required key is isset and fail if not
     *
     * @param array $data
     * @param string|null $key
     *
     * @return array
     * @throws \Exception
     */
    public function responseOrFail(array $data, string $key = null): array
    {
        $response = [];
        if ($key) {
            if (!isset($data[$key])) {
                // Деловые отвечают 200 с errors, например на город без терминала или с устаревшим id
                if (isset($data['errors'])) {
                    throw ValidationException::withMessages($this->errorMessages($data['errors']));
                }
                throw new Exception("Dellin API response has no \"$key\"");
            }
            $response['data'] = $data[$key];
        } else {
            $response['data'] = $data;
        }
        return $response;
    }

    /**
     * Get terminals for the city.
     *
     * @param int $city
     * @param \SergeevPasha\Dellin\Http\Requests\DellinTerminalRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getTerminals(int $city, DellinTerminalRequest $request): JsonResponse
    {
        $arrival = (bool) $request->query('arrival');
        $express = $request->query('express') !== null ? (bool) $request->query('express') : null;
        $data = $this->client->getCityTerminals($city, $arrival, $express);
        $response = $this->responseOrFail($data, 'terminals');
        return response()->json($response);
    }

    /**
     * Query Street.
     *
     * @param int $city
     * @param \SergeevPasha\Dellin\Http\Requests\DellinQueryStreetRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function queryStreet(int $city, DellinQueryStreetRequest $request): JsonResponse
    {
        $data = $this->client->findCityStreet($city, $request->query('query'));
        $response = $this->responseOrFail($data, 'streets');
        return response()->json($response);
    }

    /**
     * Get available package types.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getAvailablePackages(): JsonResponse
    {
        $data = $this->client->getAvailablePackages();
        $response = $this->responseOrFail($data, 'data');
        return response()->json($response);
    }

    /**
     * Get special requirements for your cargo handling.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getSpecialRequirements(): JsonResponse
    {
        $data = $this->client->getSpecialRequirements();
        $response = $this->responseOrFail($data, 'data');
        return response()->json($response);
    }

    /**
     * Get oversized cargo thresholds. They rarely change, so they are cached for a day.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getOversizedThresholds(): JsonResponse
    {
        $response = Cache::remember('dellin_oversized_thresholds', now()->addDay(), function () {
            return $this->responseOrFail($this->client->getOversizedThresholds(), 'data');
        });
        return response()->json($response);
    }

    /**
     * Get Counterparties data
     *
     * @param \SergeevPasha\Dellin\Http\Requests\DellinCounterpartiesRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getCounterparties(DellinCounterpartiesRequest $request): JsonResponse
    {
        $data = $this->client->getCounterparties($request->query('session_id', $request->query('expand')));
        $data = $this->responseOrFail($data, 'data');
        $response['data'] = $data['data']['counteragents'];
        return response()->json($response);
    }

    /**
     * Calculate delivery.
     *
     * @param \SergeevPasha\Dellin\Http\Requests\DellinCalculatePriceRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     * @throws \Illuminate\Validation\ValidationException
     */
    public function calculateDeliveryPrice(DellinCalculatePriceRequest $request): JsonResponse
    {
        $data = $this->client->getPrice(Delivery::fromArray($request->all()));
        if (array_key_exists('errors', $data)) {
            throw ValidationException::withMessages($this->errorMessages($data['errors']));
        }
        $response = $this->responseOrFail($data, 'data');
        return response()->json($response);
    }

    /**
     * Get Order History.
     *
     * @param \SergeevPasha\Dellin\Http\Requests\DellinOrderHistoryRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     * @throws \Illuminate\Validation\ValidationException
     */
    public function orderHistory(DellinOrderHistoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $orderHistory = $this->client->getOrderHistory($data['order_id']);

        $response = $this->responseOrFail($orderHistory, 'data');
        return response()->json($response['data']['statusHistory'][$data['order_id']]);
    }

    /**
     * Ошибки Деловых сообщениями валидации. Формат неоднородный: строка, в v1 поле => текст
     * (`{"cityid": "Объект не найден"}`), в v2 список с code, title, detail и fields, где
     * любого из них может не быть.
     *
     * @param mixed $errors
     *
     * @return array<string|int, string>
     */
    private function errorMessages($errors): array
    {
        if (!is_array($errors)) {
            $errors = ['dellin' => (string) $errors];
        }
        $messages = [];
        foreach ($errors as $index => $error) {
            if (is_array($error)) {
                $fields = implode(',', array_filter((array) ($error['fields'] ?? []), 'is_string'));
                $detail = trim((string) ($error['detail'] ?? $error['title'] ?? ''), '.');
                $text = trim($fields . ($fields !== '' && $detail !== '' ? ' - ' : '') . $detail);
                $index = $error['code'] ?? $index;
            } else {
                $text = trim((string) $error, '.');
            }
            $messages[$index] = 'Деловые Линии: ' . ($text !== '' ? $text : 'неизвестная ошибка') . '.';
        }
        return $messages;
    }
}
