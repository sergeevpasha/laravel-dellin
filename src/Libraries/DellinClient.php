<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Libraries;

use GuzzleHttp\Client as GuzzleClient;
use SergeevPasha\Dellin\DTO\Delivery;
use SergeevPasha\Dellin\DTO\DellinTrack;
use SergeevPasha\Dellin\Helpers\DellinHelper;

class DellinClient
{
    /**
     * Dellin key.
     *
     * @var string
     */
    private string $key;

    /**
     * Constructor
     *
     * @param string $key
     *
     * @return void
     */
    public function __construct(string $key)
    {
        $this->key = $key;
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
     * @param string $path
     * @param array  $params
     * @param string $method
     *
     * @return array
     */
    public function request(string $path, array $params = [], string $method = 'POST'): array
    {
        $url              = "https://api.dellin.ru/$path.json";
        $params['appkey'] = $this->key;
        $options          = [
            'headers'     => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'json'        => $params,
            'http_errors' => false,
            'decode_content' => false,
        ];
        $client           = new GuzzleClient();
        $response         = $client->$method($url, $options);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Find track by number
     *
     * @param string $trackNumber
     *
     * @return \SergeevPasha\Dellin\DTO\DellinTrack
     */
    public function findByTrackNumber(string $trackNumber): DellinTrack
    {
        $data = $this->request(
            'v3/orders',
            [
                'docIds' => [$trackNumber],
            ]
        );

        return DellinTrack::fromArray($data);
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
     * @param \SergeevPasha\Dellin\DTO\Delivery $delivery
     *
     * @return array
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
                'sessionID' => $delivery->session,
                'delivery'  => $deliveryRequest,
                'cargo'     => $cargo,
                'payment'   => $payment,
            ],
            $members
        );
        $data    = DellinHelper::removeNullValues($request);
        return $this->request('v2/calculator', $data);
    }
}
