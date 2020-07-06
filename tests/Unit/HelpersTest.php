<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Tests\Unit;

use SergeevPasha\Dellin\Tests\TestCase;
use SergeevPasha\Dellin\Helpers\DellinHelper;

class HelpersTest extends TestCase
{
    public function testRemoveNullValues(): void
    {
        $haystack = [
            'arrayOne' => [
                'one' => 'text',
                'two' => null
            ],
            'arrayTwo' => 'text',
            'arrayThree' => [
                'one' => ''
            ]
        ];
        $result = [
            'arrayOne' => [
                'one' => 'text',
            ],
            'arrayTwo' => 'text',
        ];
        $clean = DellinHelper::removeNullValues($haystack);
        $this->assertEqualsCanonicalizing($result, $clean);
    }
}
