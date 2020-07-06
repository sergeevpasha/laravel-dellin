<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Tests\Unit;

use SergeevPasha\Dellin\Providers\DellinServiceProvider;
use SergeevPasha\Dellin\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function testServiceProvider(): void
    {
        $service = new DellinServiceProvider($this->app);
        $this->assertNull($service->register());
        $this->assertNull($service->boot());
    }
}
