<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Repository;

use OPG\Digideps\Backend\Entity\Satisfaction;
use OPG\Digideps\Backend\Repository\SatisfactionRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class SatisfactionRepositoryTest extends TestCase
{
    private SatisfactionRepository $sut;
    private EntityManagerInterface&Stub $stubEm;

    protected function setUp(): void
    {
        $this->stubEm = $this->createStub(EntityManagerInterface::class);

        $stubRegistry = $this->createStub(ManagerRegistry::class);

        $stubMetadata = $this->createStub(ClassMetadata::class);
        $stubMetadata->name = Satisfaction::class;

        $stubRegistry->method('getManagerForClass')->willReturn($this->stubEm);
        $this->stubEm->method('getClassMetadata')->willReturn($stubMetadata);

        $this->sut = new SatisfactionRepository($stubRegistry);
    }

    public function testFindAllSatisfactionSubmissionsReturnsQueryResults(): void
    {
        $fromDate = new \DateTime('2026-03-01');
        $toDate = new \DateTime('2026-03-31 23:59:59');

        $expectedRows = [
            ['id' => 1, 'score' => 5, 'comments' => 'Great', 'deputyRole' => 'LAY', 'reportType' => '103', 'created' => '2026-03-15'],
            ['id' => 2, 'score' => 4, 'comments' => 'Good',  'deputyRole' => 'LAY', 'reportType' => '103', 'created' => '2026-03-20'],
        ];

        $stubQuery = $this->createStub(Query::class);
        $stubQuery->method('setParameters')->willReturnSelf();
        $stubQuery->method('getResult')->willReturn($expectedRows);

        $this->stubEm->method('createQuery')
            ->willReturn($stubQuery);

        $result = $this->sut->findAllSatisfactionSubmissions($fromDate, $toDate);

        $this->assertSame($expectedRows, $result);
    }

    public function testGetSatisfactionDataForPeriodReturnsEmptyWhenNoDbRows(): void
    {
        $this->setUpDbalStubs([]);

        $result = $this->sut->getSatisfactionDataForPeriod(
            new \DateTime('2026-03-01'),
            new \DateTime('2026-03-31 23:59:59')
        );

        $this->assertSame([], $result);
    }

    public function testGetSatisfactionDataForPeriodReturnsEmptyWhenAllScoresAreZero(): void
    {
        $this->setUpDbalStubs([[
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

        $this->setUpDbalStubs([[
            'very_dissatisfied' => 1,
            'dissatisfied'      => 1,
            'neither'           => 2,
            'satisfied'         => 3,
            'very_satisfied'    => 3,
        ]]);

        /** @var array<int, array<string, mixed>> $result */
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
        $this->setUpDbalStubs([[
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
        $this->setUpDbalStubs([[
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

    private function setUpDbalStubs(array $rows): void
    {
        $stubResult = $this->createStub(Result::class);
        $stubResult->method('fetchAllAssociative')->willReturn($rows);

        $stubStmt = $this->createStub(Statement::class);
        $stubStmt->method('executeQuery')->willReturn($stubResult);

        $stubConn = $this->createStub(Connection::class);
        $stubConn->method('prepare')->willReturn($stubStmt);

        $this->stubEm->method('getConnection')->willReturn($stubConn);
    }
}
