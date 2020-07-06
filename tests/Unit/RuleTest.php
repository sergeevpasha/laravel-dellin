<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Tests\Unit;

use SergeevPasha\Dellin\Tests\TestCase;
use SergeevPasha\Dellin\Rules\ValidatePackages;

class RuleTest extends TestCase
{
    public function testValidatePackagesRule(): void
    {
        $validate = new ValidatePackages();
        $pass = $validate->passes('any', [
            [
                'uid' => '123',
                'count' => '10'
            ]
        ]);
        $this->assertTrue($pass);
        $errorPass = $validate->passes('any', [
            [
               'count' => '10'
            ]
        ]);
        $this->assertFalse($errorPass);
        $errorPass = $validate->passes('any', [
            [
               'uid' => '10',
               'count' => 'string'
            ]
        ]);
        $this->assertFalse($errorPass);
        $errorPass = $validate->passes('any', 'string');
        $this->assertFalse($errorPass);
    }

    public function testValidatePackagesRuleMessages(): void
    {
        $validate = new ValidatePackages();
        $messages = $validate->message();
        $this->assertIsString($messages);
    }
}
