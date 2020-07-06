<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Tests\Unit\Libraries;

use Illuminate\Support\Str;
use SergeevPasha\Dellin\DTO\AcDoc;
use SergeevPasha\Dellin\DTO\ArrivalData;
use SergeevPasha\Dellin\DTO\Cargo;
use SergeevPasha\Dellin\DTO\Collection\AcDocCollection;
use SergeevPasha\Dellin\DTO\Collection\PackageCollection;
use SergeevPasha\Dellin\DTO\DerivalData;
use SergeevPasha\Dellin\DTO\Payment;
use SergeevPasha\Dellin\DTO\Requester;
use SergeevPasha\Dellin\Enum\AcDocType;
use SergeevPasha\Dellin\Enum\DeliveryType;
use SergeevPasha\Dellin\Enum\PaymentType;
use SergeevPasha\Dellin\Enum\RequesterRole;
use SergeevPasha\Dellin\Enum\ShippingType;
use SergeevPasha\Dellin\Libraries\RequestBuilder;
use SergeevPasha\Dellin\Tests\TestCase;

class RequestBuilderTest extends TestCase
{
    /**
     * Controller.
     *
     * @var \SergeevPasha\Dellin\Libraries\RequestBuilder
     */
    protected RequestBuilder $builder;

    /**
     * Set Up requirements.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = $this->app->make(RequestBuilder::class);
    }

    public function testBuildDeliveryType()
    {
        $deliveryType = DeliveryType::getRandomInstance();
        $method = $this->builder->buildDeliveryType($deliveryType);
        $this->assertArrayHasKey('type', $method);
    }

    public function testBuildArrival()
    {
        $data = [
            'arrival_shipping_type'  => '0',
            'arrival_terminal_id'    => '1',
            'arrival_address_id'     => '1',
            'arrival_street_code'    => '1',
            'arrival_city_code'      => '1',
            'arrival_worktime_start' => '10:00',
            'arrival_worktime_end'   => '10:30',
            'arrival_break_start'    => '11:00',
            'arrival_break_end'      => '11:30',
            'arrival_exact_time'     => '1',
            'arrival_freight_lift'   => '1',
            'arrival_to_floor'       => '10',
            'arrival_carry'          => '1',
            'arrival_requirements'   => [
                '123',
                '456',
                '789',
            ],
        ];
        $response = [
            'variant'    => Str::lower(ShippingType::getKey(0)),
            'terminalID' => '1',
            'time'       => [
                'worktimeStart' => '10:00',
                'worktimeEnd'   => '10:30',
                'breakStart'    => '11:00',
                'breakEnd'      => '11:30',
                'exactTime'     => '1',
            ],
            'handling' => [
                'freightLift' => '1',
                'toFloor'     => '10',
                'carry'       => '1',
            ],
            'requirements' => [
                '123',
                '456',
                '789',
            ],
        ];
        $arrivalData = ArrivalData::fromArray($data);
        $method = $this->builder->buildArrival($arrivalData);
        $this->assertEqualsCanonicalizing($response, $method);
        $arrivalData->terminalID = null;
        $method = $this->builder->buildArrival($arrivalData);
        $this->assertArrayHasKey('variant', $method);
        $arrivalData->addressID = null;
        $method = $this->builder->buildArrival($arrivalData);
        $this->assertArrayHasKey('variant', $method);
        $arrivalData->address['street'] = null;
        $method = $this->builder->buildArrival($arrivalData);
        $this->assertArrayHasKey('variant', $method);
    }

    public function testBuildDerival()
    {
        $data = [
            'derival_produce_date'   => '2100-10-10',
            'derival_shipping_type'  => '0',
            'derival_terminal_id'    => '1',
            'derival_address_id'     => '1',
            'derival_street_code'    => '1',
            'derival_city_code'      => '1',
            'derival_worktime_start' => '10:00',
            'derival_worktime_end'   => '10:30',
            'derival_break_start'    => '11:00',
            'derival_break_end'      => '11:30',
            'derival_exact_time'     => '1',
            'derival_freight_lift'   => '1',
            'derival_to_floor'       => '10',
            'derival_carry'          => '1',
            'derival_requirements'   => [
                '123',
                '456',
                '789',
            ],
        ];
        $response = [
            'produceDate' => '2100-10-10',
            'variant'     => Str::lower(ShippingType::getKey(0)),
            'terminalID'  => '1',
            'time'        => [
                'worktimeStart' => '10:00',
                'worktimeEnd'   => '10:30',
                'breakStart'    => '11:00',
                'breakEnd'      => '11:30',
                'exactTime'     => '1',
            ],
            'handling' => [
                'freightLift' => '1',
                'toFloor'     => '10',
                'carry'       => '1',
            ],
            'requirements' => [
                '123',
                '456',
                '789',
            ],
        ];
        $arrivalData = DerivalData::fromArray($data);
        $method = $this->builder->buildDerival($arrivalData);
        $this->assertEqualsCanonicalizing($response, $method);
        $arrivalData->terminalID = null;
        $method = $this->builder->buildDerival($arrivalData);
        $this->assertArrayHasKey('variant', $method);
        $arrivalData->addressID = null;
        $method = $this->builder->buildDerival($arrivalData);
        $this->assertArrayHasKey('variant', $method);
        $arrivalData->address['street'] = null;
        $method = $this->builder->buildDerival($arrivalData);
        $this->assertArrayHasKey('variant', $method);
    }

    public function testBuildPackages()
    {
        $packageCollection = PackageCollection::fromArray([
            'packages' => [
                [
                    'uid'   => '1100',
                    'count' => '1',
                ],
            ],
        ]);
        $response = [
            [
                'uid'   => '1100',
                'count' => '1',
            ],
        ];
        $method = $this->builder->buildPackages($packageCollection);
        $this->assertEqualsCanonicalizing($response, $method);
    }

    public function testBuildAcDocs()
    {
        $acDocsCollection = AcDocCollection::create([
            AcDoc::fromValue(0),
            AcDoc::fromValue(1),
        ]);
        $response = [
            [
                'action' => Str::lower(AcDocType::getKey(0)),
            ],
            [
                'action' => Str::lower(AcDocType::getKey(1)),
            ],
        ];
        $method = $this->builder->buildAcDocs($acDocsCollection);
        $this->assertEqualsCanonicalizing($response, $method);
    }

    public function testBuildRequester()
    {
        $requester = Requester::fromArray(0, '123');
        $response = [
            'role' => Str::lower(RequesterRole::getKey(0)),
            'uid'  => '123',
        ];
        $method = $this->builder->buildRequester($requester);
        $this->assertEqualsCanonicalizing($response, $method);
    }

    public function testBuildCargo()
    {
        $cargo = Cargo::fromArray([
            'cargo_quantity'         => '10',
            'cargo_length'           => '1',
            'cargo_width'            => '1',
            'cargo_height'           => '1',
            'cargo_weight'           => '1',
            'cargo_total_volume'     => '1',
            'cargo_total_weight'     => '10',
            'cargo_oversized_weight' => '1',
            'cargo_oversized_volume' => '1',
            'cargo_freight_uid'      => '123',
            'cargo_freight_name'     => 'string',
            'insurance_value'        => '100',
            'insurance_term'         => '1',
        ]);

        $response = [
            'quantity'        => 10,
            'length'          => 1.0,
            'width'           => 1.0,
            'height'          => 1.0,
            'weight'          => 1.0,
            'totalVolume'     => 1.0,
            'totalWeight'     => 10.0,
            'oversizedWeight' => 1.0,
            'oversizedVolume' => 1.0,
            'freightUID'      => '123',
            'hazardClass'     => 0.0,
            'insurance'       => [
                'statedValue' => 100.0,
                'term'        => true,
            ],
        ];
        $method = $this->builder->buildCargo($cargo);
        $this->assertEqualsCanonicalizing($response, $method);
        $cargo->freightUID = null;
        $method = $this->builder->buildCargo($cargo);
        $this->assertArrayHasKey('quantity', $method);
    }

    public function testBuildPayment()
    {
        $payment = Payment::fromArray([
            'payment_city' => '110011',
            'payment_type' => 0,
        ]);
        $response = [
            'paymentCity' => '110011',
            'type'        => Str::lower(PaymentType::getKey(0)),
        ];
        $method = $this->builder->buildPayment($payment);
        $this->assertEqualsCanonicalizing($response, $method);
    }
}
