<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Tests\Unit\DTO;

use SergeevPasha\Dellin\DTO\DellinTrack;
use SergeevPasha\Dellin\Tests\TestCase;

class DellinTrackTest extends TestCase
{
    public function testReadsSettlementCodesBesideTerminalCityIds(): void
    {
        // 64055093: Donetsk is served by the Mariupol terminal, cityId 287 is Mariupol's terminal city
        $track = DellinTrack::fromArray(['orders' => [[
            'orderId'   => '64055093',
            'state'     => 'finished',
            'stateName' => 'Заказ завершен',
            'derival'   => [
                'city'       => 'г. Москва',
                'cityId'     => 3,
                'cityCode'   => '7700000000000000000000000',
                'terminalId' => 125,
            ],
            'arrival'   => [
                'city'         => 'Донецк г (Респ. Донецкая Народная)',
                'cityId'       => 287,
                'cityCode'     => '9300100000000000000000000',
                'terminalId'   => 719,
                'terminalName' => 'Мариуполь',
            ],
        ]]]);

        $this->assertSame('7700000000000000000000000', $track->derivalCityCode);
        $this->assertSame(3, $track->derivalCityId);
        $this->assertSame('9300100000000000000000000', $track->arrivalCityCode);
        $this->assertSame(287, $track->arrivalCityId);
        $this->assertSame('finished', $track->state);
        $this->assertFalse($track->isDeclined());
        $this->assertNull($track->declineReason);
    }

    public function testReadsDeclinedState(): void
    {
        $track = DellinTrack::fromArray(['orders' => [[
            'orderId'       => '59885423',
            'state'         => 'declined',
            'stateName'     => 'Отклонен',
            'declineReason' => 'Отказ отправителя',
        ]]]);

        $this->assertTrue($track->isDeclined());
        $this->assertSame('Отклонен', $track->status);
        $this->assertSame('Отказ отправителя', $track->declineReason);
        $this->assertNull($track->arrivalCityCode);
    }

    public function testUnknownOrderHasNoStateOrCodes(): void
    {
        $track = DellinTrack::fromArray(['errors' => [['code' => 717001, 'title' => 'Заказ не найден']]]);

        $this->assertTrue($track->notFound);
        $this->assertNull($track->state);
        $this->assertFalse($track->isDeclined());
        $this->assertNull($track->derivalCityCode);
        $this->assertNull($track->arrivalCityCode);
    }
}
