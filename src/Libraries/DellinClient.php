<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Libraries;

use GuzzleHttp\Client as GuzzleClient;
use SergeevPasha\Dellin\DTO\Delivery;
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
     * @param  string $key
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
     * @return array<mixed>
     */
    public function authorize(?string $login = null, ?string $password = null): array
    {
        $data = $this->request('v2/customers/login', [
            'login' => $login ?? config('dellin.login'),
            'password' => $password ?? config('dellin.password'),
        ]);
        if (isset($data['sessionID'])) {
            $session = $this->request('v1/customers/session_info', [
                'sessionId'    => $data['sessionID'],
            ]);
            $data = array_merge($data, $session);
        }
        return $data;
    }
    
    /**
     * Send request to Dellin API.
     *
     * @param string $path
     * @param array<mixed> $params
     * @param string $method
     *
     * @return array<mixed>
     */
    public function request(string $path, array $params = [], string $method = 'POST'): array
    {
        $url = "https://api.dellin.ru/$path.json";
        $params['appkey'] = $this->key;
        $options = [
            'headers' => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'json'        => $params,
            'http_errors' => false,
        ];
        $client = new GuzzleClient();
        $response = $client->$method($url, $options);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get Dellin cities CSV File path.
     *
     * @return string|null
     */
    public function getCitiesCSV(): ?string
    {
        $data = $this->request('v1/public/cities');
        return isset($data['url']) ? $data['url'] : null;
    }

    /**
     * Get Dellin places CSV File path.
     *
     * @return string|null
     */
    public function getPlacesCSV(): ?string
    {
        $data = $this->request('v1/public/places');
        return isset($data['url']) ? $data['url'] : null;
    }

    /**
     * Get Streets CSV File path.
     *
     * @return string|null
     */
    public function getStreetsCSV(): ?string
    {
        $data = $this->request('v1/public/streets');
        return isset($data['url']) ? $data['url'] : null;
    }

    /**
     * Find a city by query string.
     *
     * @param string $query
     * @return array<mixed>
     */
    public function findCity(string $query): array
    {
        return $this->request('v2/public/kladr', [
            'q' => $query,
        ]);
    }

    /**
     * Find a street by query string and City ID.
     *
     * @param int    $city
     * @param string $query
     * @return array<mixed>
     */
    public function findCityStreet(int $city, string $query): array
    {
        return $this->request('v1/public/kladr_street', [
            'cityID' => $city,
            'street' => $query,
        ]);
    }

    /**
     * Get every terminal in the city.
     *
     * @param int  $city
     * @param bool $arrival
     * @return array<mixed>
     */
    public function getCityTerminals(int $city, bool $arrival = true): array
    {
        $direction = $arrival ? 'arrival' : 'derival';
        return $this->request('v1/public/request_terminals', [
            'cityID'    => $city,
            'direction' => $direction,
        ]);
    }

    /**
     * Get available package types.
     *
     * @return array<mixed>
     */
    public function getAvailablePackages(): array
    {
        return $this->request('v1/references/packages');
    }

    /**
     * Get special requirements for your cargo handling.
     *
     * @return array<mixed>
     */
    public function getSpecialRequirements(): array
    {
        return $this->request('v1/references/services');
    }

    /**
     * Get Counterpaties data
     *
     * @param  string $session
     * @param  bool $expanded
     * @return array<mixed>
     */
    public function getCounterparties(string $session, bool $expanded = false): array
    {
        return $this->request('v2/counteragents', [
            'sessionID' => $session,
            'fullInfo' => $expanded
        ]);
    }

    /**
     * Get calculated price.
     *
     * @param \SergeevPasha\Dellin\DTO\Delivery $delivery
     * @return array<mixed>
     */
    public function getPrice(Delivery $delivery): array
    {
        $builder = new RequestBuilder();
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
        $members = [
            'requester' => $builder->buildRequester($delivery->requester),
        ];
        $cargo = $builder->buildCargo($delivery->cargo);
        $payment = $builder->buildPayment($delivery->payment);
        $request = [
            'sessionID' => $delivery->session,
            'delivery'  => $deliveryRequest,
            'members'   => $members,
            'cargo'     => $cargo,
            'payment'   => $payment,
        ];
        $data = DellinHelper::removeNullValues($request);
        return $this->request('v2/calculator', $data);
    }
}
