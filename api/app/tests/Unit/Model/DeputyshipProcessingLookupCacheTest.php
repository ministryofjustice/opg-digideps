<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model;

use App\Model\DeputyshipProcessingLookupCache;
use App\Repository\ClientRepository;
use App\Repository\CourtOrderRepository;
use App\Repository\DeputyRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class DeputyshipProcessingLookupCacheTest extends TestCase
{
    private CourtOrderRepository $courtOrderRepository;
    private DeputyRepository $deputyRepository;
    private ClientRepository $clientRepository;

    private DeputyshipProcessingLookupCache $sut;

    public function setUp(): void
    {
        $this->courtOrderRepository = $this->createMock(CourtOrderRepository::class);
        $this->deputyRepository = $this->createMock(DeputyRepository::class);
        $this->clientRepository = $this->createMock(ClientRepository::class);

        $this->sut = new DeputyshipProcessingLookupCache(
            $this->courtOrderRepository,
            $this->deputyRepository,
            $this->clientRepository
        );
    }

    public function testGettersBeforeInitFail(): void
    {
        static::expectException(\RuntimeException::class);
        $this->sut->getCourtOrderStatusForUid('111111111');

        static::expectException(\RuntimeException::class);
        $this->sut->getClientIdForCasenumber('41443433434');

        static::expectException(\RuntimeException::class);
        $this->sut->getDeputyIdForUid('112214555');

        static::expectException(\RuntimeException::class);
        $this->sut->getCourtOrderStatusForUid('4324234234');
    }

    public function testInitAndGetters(): void
    {
        $mockQueryBuilder = $this->createMock(QueryBuilder::class);
        $mockQuery = $this->createMock(Query::class);

        $mockCourtOrders = [
            ['id' => 11, 'status' => 'ACTIVE', 'courtOrderUid' => '1442223333'],
            ['id' => 12, 'status' => 'CLOSED', 'courtOrderUid' => '1112221122'],
        ];

        $deputyUidToIdMap = ['999999999' => 1];

        $clientCasenumberToIdMap = ['88888888' => 2];

        $this->courtOrderRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($mockQueryBuilder);

        $mockQueryBuilder->expects($this->once())
            ->method('select')
            ->willReturn($mockQueryBuilder);

        $mockQueryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($mockQuery);

        $mockQuery->expects($this->once())
            ->method('getArrayResult')
            ->willReturn($mockCourtOrders);

        $this->deputyRepository->expects($this->once())
            ->method('getUidToIdMapping')
            ->willReturn($deputyUidToIdMap);

        $this->clientRepository->expects($this->once())
            ->method('getActiveCasenumberToIdMapping')
            ->willReturn($clientCasenumberToIdMap);

        $expected = $this->sut->init();
        static::assertTrue($expected);

        // second call to init should return true without rebuilding the cache
        $expected = $this->sut->init();
        static::assertTrue($expected);

        // test getters
        static::assertEquals(2, $this->sut->getClientIdForCasenumber('88888888'));
        static::assertNull($this->sut->getClientIdForCasenumber('345234341'));

        static::assertEquals(1, $this->sut->getDeputyIdForUid('999999999'));
        static::assertNull($this->sut->getDeputyIdForUid('1145443545'));

        static::assertEquals(11, $this->sut->getCourtOrderIdForUid('1442223333'));
        static::assertEquals(12, $this->sut->getCourtOrderIdForUid('1112221122'));
        static::assertNull($this->sut->getCourtOrderIdForUid('14446573'));

        static::assertEquals('ACTIVE', $this->sut->getCourtOrderStatusForUid('1442223333'));
        static::assertEquals('CLOSED', $this->sut->getCourtOrderStatusForUid('1112221122'));
        static::assertNull($this->sut->getCourtOrderStatusForUid('14446573'));
    }
}
