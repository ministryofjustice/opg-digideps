<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Repository;

use OPG\Digideps\Backend\Entity\Satisfaction;
use OPG\Digideps\Backend\Repository\SatisfactionRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class SatisfactionRepositoryTest extends TestCase
{
    private SatisfactionRepository $sut;
    private EntityManagerInterface $mockEm;

    protected function setUp(): void
    {
        $this->mockEm = $this->createMock(EntityManagerInterface::class);

        $mockRegistry = $this->createMock(ManagerRegistry::class);

        $mockMetadata = $this->createMock(ClassMetadata::class);
        $mockMetadata->name = Satisfaction::class;

        $mockRegistry->method('getManagerForClass')->willReturn($this->mockEm);
        $this->mockEm->method('getClassMetadata')->willReturn($mockMetadata);

        $this->sut = new SatisfactionRepository($mockRegistry);
    }

    public function testFindAllSatisfactionSubmissionsReturnsQueryResults(): void
    {
        $fromDate = new \DateTime('2026-03-01');
        $toDate = new \DateTime('2026-03-31 23:59:59');

        $expectedRows = [
            ['id' => 1, 'score' => 5, 'comments' => 'Great', 'deputyrole' => 'LAY', 'reporttype' => '103', 'created' => '2026-03-15'],
            ['id' => 2, 'score' => 4, 'comments' => 'Good',  'deputyrole' => 'LAY', 'reporttype' => '103', 'created' => '2026-03-20'],
        ];

        $mockQuery = $this->createMock(AbstractQuery::class);
        $mockQuery->method('setParameters')->willReturnSelf();
        $mockQuery->method('getResult')->willReturn($expectedRows);

        $this->mockEm->method('createQuery')
            ->willReturn($mockQuery);

        $result = $this->sut->findAllSatisfactionSubmissions($fromDate, $toDate);

        $this->assertSame($expectedRows, $result);
    }

    public function testGetSatisfactionDataForPeriodReturnsEmptyWhenNoDbRows(): void
    {
        $this->setUpDbalMocks([]);

        $result = $this->sut->getSatisfactionDataForPeriod(
            new \DateTime('2026-03-01'),
            new \DateTime('2026-03-31 23:59:59')
        );

        $this->assertSame([], $result);
    }

    public function testGetSatisfactionDataForPeriodReturnsEmptyWhenAllScoresAreZero(): void
    {
        $this->setUpDbalMocks([[
            'very_dissatisfied' => 0,
            'dissatisfied'      => 0,
            'neither'           => 0,
            'satisfied'         => 0,
            'very_satisfied'    => 0,
        ]]);

        $result = $this->sut->getSatisfactionDataForPeriod(
            new \DateTime('2026-03-01'),
            new \DateTime('2026-03-31 23:59:59')
        );

        $this->assertSame([], $result);
    }

    public function testGetSatisfactionDataForPeriodReturnsFormattedScoresWithPercentage(): void
    {
        $statsStartDate = new \DateTime('2026-03-01');
        $statsEndDate   = new \DateTime('2026-03-31 23:59:59');

        $this->setUpDbalMocks([[
            'very_dissatisfied' => 1,
            'dissatisfied'      => 1,
            'neither'           => 2,
            'satisfied'         => 3,
            'very_satisfied'    => 3,
        ]]);

        $result = $this->sut->getSatisfactionDataForPeriod($statsStartDate, $statsEndDate);

        // 5 score keys + 1 user_satisfaction_percent key = 6 entries
        $this->assertCount(6, $result);

        // Every entry should have the common fields
        foreach ($result as $entry) {
            $this->assertSame('2026-03-01T00:00:00+00:00', $entry['_timestamp']);
            $this->assertSame('deputy-reporting', $entry['service']);
            $this->assertSame('digital', $entry['channel']);
            $this->assertSame('month', $entry['period']);
            $this->assertArrayHasKey('count', $entry);
            $this->assertArrayHasKey('dataType', $entry);
        }

        // Collect by dataType for targeted assertions
        $byType = array_column($result, null, 'dataType');

        $this->assertSame(1, $byType['very-dissatisfied']['count']);
        $this->assertSame(1, $byType['dissatisfied']['count']);
        $this->assertSame(2, $byType['neither']['count']);
        $this->assertSame(3, $byType['satisfied']['count']);
        $this->assertSame(3, $byType['very-satisfied']['count']);

        // satisfied(3) + very_satisfied(3) = 6 out of 10 total → 60%
        $this->assertSame(60, $byType['user-satisfaction-percent']['count']);
    }

    public function testGetSatisfactionDataForPeriodCalculates100PercentSatisfaction(): void
    {
        $this->setUpDbalMocks([[
            'very_dissatisfied' => 0,
            'dissatisfied'      => 0,
            'neither'           => 0,
            'satisfied'         => 5,
            'very_satisfied'    => 5,
        ]]);

        $result = $this->sut->getSatisfactionDataForPeriod(
            new \DateTime('2026-03-01'),
            new \DateTime('2026-03-31 23:59:59')
        );

        $byType = array_column($result, null, 'dataType');
        $this->assertSame(100, $byType['user-satisfaction-percent']['count']);
    }

    public function testGetSatisfactionDataForPeriodCalculates0PercentSatisfaction(): void
    {
        $this->setUpDbalMocks([[
            'very_dissatisfied' => 5,
            'dissatisfied'      => 5,
            'neither'           => 0,
            'satisfied'         => 0,
            'very_satisfied'    => 0,
        ]]);

        $result = $this->sut->getSatisfactionDataForPeriod(
            new \DateTime('2026-03-01'),
            new \DateTime('2026-03-31 23:59:59')
        );

        $byType = array_column($result, null, 'dataType');
        $this->assertSame(0, $byType['user-satisfaction-percent']['count']);
    }

    private function setUpDbalMocks(array $rows): void
    {
        $mockResult = $this->createMock(Result::class);
        $mockResult->method('fetchAllAssociative')->willReturn($rows);

        $mockStmt = $this->createMock(Statement::class);
        $mockStmt->method('executeQuery')->willReturn($mockResult);

        $mockConn = $this->createMock(Connection::class);
        $mockConn->method('prepare')->willReturn($mockStmt);

        $this->mockEm->method('getConnection')->willReturn($mockConn);
    }
}
