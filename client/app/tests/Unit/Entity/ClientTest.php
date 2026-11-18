<?php

declare(strict_types=1);

use OPG\Digideps\Frontend\Entity\Client;
use OPG\Digideps\Frontend\Entity\CourtOrder;
use OPG\Digideps\Frontend\Entity\Deputy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testHasProDeputy(): void
    {
        $deputy = new Deputy();
        $deputy->deputyType = 'PRO';

        $activeCourtOrder = new CourtOrder();
        $activeCourtOrder->setStatus('ACTIVE');

        $inactiveCourtOrder = new CourtOrder();
        $inactiveCourtOrder->setStatus('CLOSED');

        $client = new Client();
        $client->courtOrders = [$activeCourtOrder, $inactiveCourtOrder];

        // deputy only active on the inactive court order
        $activeCourtOrder->setActiveDeputies([]);
        $inactiveCourtOrder->setActiveDeputies([$deputy]);
        self::assertFalse($client->hasProDeputy());

        // deputy only active on the active court order
        $activeCourtOrder->setActiveDeputies([$deputy]);
        $inactiveCourtOrder->setActiveDeputies([]);
        self::assertTrue($client->hasProDeputy());

        // deputy active on both court orders
        $activeCourtOrder->setActiveDeputies([$deputy]);
        $inactiveCourtOrder->setActiveDeputies([]);
        self::assertTrue($client->hasProDeputy());
    }

    public function testHasPaDeputy(): void
    {
        $deputy = new Deputy();
        $deputy->deputyType = 'PA';

        $activeCourtOrder = new CourtOrder();
        $activeCourtOrder->setStatus('ACTIVE');

        $inactiveCourtOrder = new CourtOrder();
        $inactiveCourtOrder->setStatus('CLOSED');

        $client = new Client();
        $client->courtOrders = [$activeCourtOrder, $inactiveCourtOrder];

        // deputy only active on the inactive court order
        $activeCourtOrder->setActiveDeputies([]);
        $inactiveCourtOrder->setActiveDeputies([$deputy]);
        self::assertFalse($client->hasPaDeputy());

        // deputy only active on the active court order
        $activeCourtOrder->setActiveDeputies([$deputy]);
        $inactiveCourtOrder->setActiveDeputies([]);
        self::assertTrue($client->hasPaDeputy());

        // deputy active on both court orders
        $activeCourtOrder->setActiveDeputies([$deputy]);
        $inactiveCourtOrder->setActiveDeputies([]);
        self::assertTrue($client->hasPaDeputy());
    }

    public static function courtOrderCombos(): array
    {
        return [
            ['hwPresent' => false, 'hwActive' => false, 'pfaPresent' => false, 'pfaActive' => false],
            ['hwPresent' => true,  'hwActive' => false, 'pfaPresent' => false, 'pfaActive' => false],
            ['hwPresent' => false, 'hwActive' => true,  'pfaPresent' => false, 'pfaActive' => false],
            ['hwPresent' => true,  'hwActive' => true,  'pfaPresent' => false, 'pfaActive' => false],
            ['hwPresent' => false, 'hwActive' => false, 'pfaPresent' => true,  'pfaActive' => false],
            ['hwPresent' => true,  'hwActive' => false, 'pfaPresent' => true,  'pfaActive' => false],
            ['hwPresent' => false, 'hwActive' => true,  'pfaPresent' => true,  'pfaActive' => false],
            ['hwPresent' => true,  'hwActive' => true,  'pfaPresent' => true,  'pfaActive' => false],
            ['hwPresent' => false, 'hwActive' => false, 'pfaPresent' => false, 'pfaActive' => true],
            ['hwPresent' => true,  'hwActive' => false, 'pfaPresent' => false, 'pfaActive' => true],
            ['hwPresent' => false, 'hwActive' => true,  'pfaPresent' => false, 'pfaActive' => true],
            ['hwPresent' => true,  'hwActive' => true,  'pfaPresent' => false, 'pfaActive' => true],
            ['hwPresent' => false, 'hwActive' => false, 'pfaPresent' => true,  'pfaActive' => true],
            ['hwPresent' => true,  'hwActive' => false, 'pfaPresent' => true,  'pfaActive' => true],
            ['hwPresent' => false, 'hwActive' => true,  'pfaPresent' => true,  'pfaActive' => true],
            ['hwPresent' => true,  'hwActive' => true,  'pfaPresent' => true,  'pfaActive' => true]
        ];
    }

    #[DataProvider('courtOrderCombos')]
    public function testHasHwCourtOrder(bool $hwPresent, bool $hwActive, bool $pfaPresent, bool $pfaActive): void
    {
        $hwCourtOrder = new CourtOrder();
        $hwCourtOrder->setOrderType('hw');
        $hwCourtOrder->setStatus($hwActive ? 'ACTIVE' : 'CLOSED');

        $pfaCourtOrder = new CourtOrder();
        $pfaCourtOrder->setOrderType('pfa');
        $pfaCourtOrder->setStatus($pfaActive ? 'ACTIVE' : 'CLOSED');

        $client = new Client();

        $courtOrders = [];

        if ($hwPresent) {
            $courtOrders[] = $hwCourtOrder;
        }
        if ($pfaPresent) {
            $courtOrders[] = $pfaCourtOrder;
        }

        $client->courtOrders = $courtOrders;

        self::assertEquals($hwPresent && $hwActive, $client->hasHwCourtOrder());
    }
}
