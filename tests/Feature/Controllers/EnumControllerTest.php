<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Tests\Feature\Controllers;

use Illuminate\Http\JsonResponse;
use SergeevPasha\Dellin\Tests\TestCase;
use SergeevPasha\Dellin\Enum\PaymentType;
use SergeevPasha\Dellin\Enum\DeliveryType;
use SergeevPasha\Dellin\Enum\ShippingType;
use SergeevPasha\Dellin\Enum\RequesterRole;
use SergeevPasha\Dellin\Http\Controllers\EnumController;

class EnumControllerTest extends TestCase
{
    /**
     * Controller.
     *
     * @var \SergeevPasha\Dellin\Http\Controllers\EnumController
     */
    protected EnumController $controller;

    /**
     * Set Up requirements.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = $this->app->make(EnumController::class);
    }

    public function testDeliveryTypes()
    {
        $method = $this->controller->deliveryTypes();
        $this->assertInstanceOf(JsonResponse::class, $method);
        $this->assertEqualsCanonicalizing(
            json_encode(DeliveryType::asArray()),
            $method->content(),
            'Expected and actual data are not canonically equals'
        );
    }

    public function testPaymentTypes()
    {
        $method = $this->controller->paymentTypes();
        $this->assertInstanceOf(JsonResponse::class, $method);
        $this->assertEqualsCanonicalizing(
            json_encode(PaymentType::asArray()),
            $method->content(),
            'Expected and actual data are not canonically equals'
        );
    }

    public function testRequesterRoles()
    {
        $method = $this->controller->requesterRoles();
        $this->assertInstanceOf(JsonResponse::class, $method);
        $this->assertEqualsCanonicalizing(
            json_encode(RequesterRole::asArray()),
            $method->content(),
            'Expected and actual data are not canonically equals'
        );
    }

    public function testShippingTypes()
    {
        $method = $this->controller->shippingTypes();
        $this->assertInstanceOf(JsonResponse::class, $method);
        $this->assertEqualsCanonicalizing(
            json_encode(ShippingType::asArray()),
            $method->content(),
            'Expected and actual data are not canonically equals'
        );
    }
}
