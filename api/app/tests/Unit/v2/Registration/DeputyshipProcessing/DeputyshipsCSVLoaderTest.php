<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingDeputyship;
use App\v2\CSV\CSVChunker;
use App\v2\CSV\CSVChunkerFactory;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVLoader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use League\Csv\Exception as CSVException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DeputyshipsCSVLoaderTest extends TestCase
{
    private EntityManager|MockObject $mockEm;
    private CSVChunkerFactory|MockObject $mockCSVChunkerFactory;
    private LoggerInterface|MockObject $mockLogger;
    private DeputyshipsCSVLoader $sut;

    public function setUp(): void
    {
        $this->mockEm = $this->createMock(EntityManager::class);
        $this->mockCSVChunkerFactory = $this->createMock(CSVChunkerFactory::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);
        $this->sut = new DeputyshipsCSVLoader($this->mockEm, $this->mockCSVChunkerFactory, $this->mockLogger);
    }

    public function testLoadThrowsCSVException(): void
    {
        $fileLocation = '/tmp/test.csv';
        $chunkSize = 10000;
        $exception = new CSVException('error with csv file');

        $this->mockCSVChunkerFactory
            ->expects($this->once())
            ->method('create')
            ->with($fileLocation, StagingDeputyship::class, $chunkSize)
            ->willThrowException($exception);

        $result = $this->sut->load($fileLocation);

        $this->assertFalse($result);
    }

    public function testLoadSuccess(): void
    {
        $fileLocation = '/tmp/test.csv';
        $chunkSize = 10000;

        $sd1 = new StagingDeputyship();
        $sd2 = new StagingDeputyship();
        $sd3 = new StagingDeputyship();
        $records = new \ArrayIterator([$sd1, $sd2, $sd3]);

        $mockChunker = $this->createMock(CSVChunker::class);

        // work-around for the removal of willReturnConsecutive from phpunit...
        $caller = new \stdClass();
        $caller->callNumber = 1;

        $mockChunker->method('getChunk')->willReturnCallback(function () use ($records, $caller) {
            if ($caller->callNumber > count($records)) {
                return null;
            }

            $record = $records[$caller->callNumber - 1];
            ++$caller->callNumber;

            return [$record];
        });

        $this->mockCSVChunkerFactory
            ->expects($this->once())
            ->method('create')
            ->with($fileLocation, StagingDeputyship::class, $chunkSize)
            ->willReturn($mockChunker);

        $this->mockEm->expects($this->exactly(2))->method('beginTransaction');
        $this->mockEm->expects($this->exactly(2))->method('commit');
        $this->mockEm->expects($this->exactly(4))->method('flush');
        $this->mockEm->expects($this->exactly(3))->method('clear');

        // chunker returns three chunks, then null
        $this->mockEm->expects($this->exactly(3))
            ->method('persist')
            ->willReturnCallback(function ($record) use ($records) {
                $validValues = array_merge(iterator_to_array($records), [null]);
                $this->assertContains($record, $validValues);
            });

        $mockQuery = $this->createMock(Query::class);
        $mockQuery->expects($this->once())->method('execute');

        $this->mockEm->expects($this->once())
            ->method('createQuery')
            ->with('DELETE FROM App\Entity\StagingDeputyship sd')
            ->willReturn($mockQuery);

        $result = $this->sut->load($fileLocation);

        $this->assertTrue($result);
    }
}
