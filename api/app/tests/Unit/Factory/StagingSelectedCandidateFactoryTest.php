<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Entity\Client;
use App\Entity\StagingDeputyship;
use App\Factory\StagingSelectedCandidateFactory;
use PHPUnit\Framework\TestCase;

final class StagingSelectedCandidateFactoryTest extends TestCase
{
    private StagingSelectedCandidateFactory $factory;
    private StagingDeputyship $csvDeputyShipRow;

    protected function setUp(): void
    {
        $this->factory = new StagingSelectedCandidateFactory();

        $this->csvDeputyShipRow = new StagingDeputyship();
        $this->csvDeputyShipRow->orderUid = '700000001102';
        $this->csvDeputyShipRow->orderType = 'hw';
        $this->csvDeputyShipRow->orderStatus = 'ACTIVE';
        $this->csvDeputyShipRow->orderMadeDate = '2018-01-21';
        $this->csvDeputyShipRow->deputyUid = '700761111002';
        $this->csvDeputyShipRow->deputyStatusOnOrder = 'ACTIVE';
    }

    public function testCreateInsertOrderCandidate(): void
    {
        $candidateRecord = $this->factory->createInsertOrderCandidate($this->csvDeputyShipRow, 1);

        self::assertEquals($this->csvDeputyShipRow->orderUid, $candidateRecord->orderUid);
        self::assertEquals($this->csvDeputyShipRow->orderType, $candidateRecord->orderType);
        self::assertEquals($this->csvDeputyShipRow->orderStatus, $candidateRecord->status);
        self::assertEquals(1, $candidateRecord->clientId);
        self::assertEquals($this->csvDeputyShipRow->orderMadeDate, $candidateRecord->orderMadeDate);
    }

    public function testCreateUpdateDeputyStatusCandidate(): void
    {
        $candidateRecord = $this->factory->createUpdateDeputyStatusCandidate($this->csvDeputyShipRow, deputyId: 1, courtOrderId: 2);

        self::assertEquals($this->csvDeputyShipRow->deputyUid, $candidateRecord->deputyUid);
        self::assertEquals(true, $candidateRecord->deputyStatusOnOrder);
        self::assertEquals(1, $candidateRecord->deputyId);
        self::assertEquals(2, $candidateRecord->orderId);
    }
}
