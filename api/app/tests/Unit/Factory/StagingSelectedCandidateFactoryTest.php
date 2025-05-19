<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Entity\Client;
use App\Entity\StagingDeputyship;
use App\Factory\StagingSelectedCandidateFactory;
use PHPUnit\Framework\TestCase;

class StagingSelectedCandidateFactoryTest extends TestCase
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
    }

    public function testCreateInsertOrderCandidate()
    {
        $mockClient = $this->createMock(Client::class);
        $mockClient
            ->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $candidateRecord = $this->factory->createInsertOrderCandidate($this->csvDeputyShipRow, $mockClient->getId());

        self::assertEquals($this->csvDeputyShipRow->orderUid, $candidateRecord->orderUid);
        self::assertEquals($this->csvDeputyShipRow->orderType, $candidateRecord->orderType);
        self::assertEquals($this->csvDeputyShipRow->orderStatus, $candidateRecord->status);
        self::assertEquals($mockClient->getId(), $candidateRecord->clientId);
        self::assertEquals($this->csvDeputyShipRow->orderMadeDate, $candidateRecord->orderMadeDate);
    }
}
