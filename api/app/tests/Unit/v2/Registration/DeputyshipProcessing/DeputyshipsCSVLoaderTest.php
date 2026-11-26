<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\v2\Registration\DeputyshipProcessing;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use League\Csv\Exception as CSVException;
use OPG\Digideps\Backend\Entity\Staging\StagingDeputyship;
use OPG\Digideps\Backend\v2\CSV\CSVChunker;
use OPG\Digideps\Backend\v2\CSV\CSVChunkerFactory;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\DeputyshipsCSVLoader;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class DeputyshipsCSVLoaderTest extends TestCase
{
    public function testLoadThrowsCSVException(): void
    {
        $stubEm = $this->createStub(EntityManager::class);
        $mockCSVChunkerFactory = $this->createMock(CSVChunkerFactory::class);
        $stubLogger = $this->createStub(LoggerInterface::class);
        $sut = new DeputyshipsCSVLoader($stubEm, $mockCSVChunkerFactory, $stubLogger);

        $fileLocation = '/tmp/test.csv';
        $chunkSize = 10000;
        $exception = new CSVException('error with csv file');

        $mockCSVChunkerFactory
            ->expects($this->once())
            ->method('create')
            ->with($fileLocation, StagingDeputyship::class, $chunkSize)
            ->willThrowException($exception);

        $result = $sut->load($fileLocation);

        $this->assertFalse($result->loadedOk);
    }

    public function testLoadSuccess(): void
    {
        $mockEm = $this->createMock(EntityManager::class);
        $mockCSVChunkerFactory = $this->createMock(CSVChunkerFactory::class);
        $stubLogger = $this->createStub(LoggerInterface::class);
        $sut = new DeputyshipsCSVLoader($mockEm, $mockCSVChunkerFactory, $stubLogger);

        $fileLocation = '/tmp/test.csv';
        $chunkSize = 10000;

        $sd1 = new StagingDeputyship();
        $sd2 = new StagingDeputyship();
        $sd3 = new StagingDeputyship();
        $records = new \ArrayIterator([$sd1, $sd2, $sd3]);

        $stubChunker = $this->createStub(CSVChunker::class);

        // work-around for the removal of willReturnConsecutive from phpunit...
        $caller = new \stdClass();
        $caller->callNumber = 1;

        $stubChunker->method('getChunk')->willReturnCallback(function () use ($records, $caller): ?array {
            if ($caller->callNumber > count($records)) {
                return null;
            }

            $record = $records[$caller->callNumber - 1];
            ++$caller->callNumber;

            return [$record];
        });

        $mockCSVChunkerFactory
            ->expects($this->once())
            ->method('create')
            ->with($fileLocation, StagingDeputyship::class, $chunkSize)
            ->willReturn($stubChunker);

        $mockEm->expects($this->exactly(2))->method('beginTransaction');
        $mockEm->expects($this->exactly(2))->method('commit');
        $mockEm->expects($this->exactly(4))->method('flush');
        $mockEm->expects($this->exactly(3))->method('clear');

        // chunker returns three chunks, then null
        $mockEm->expects($this->exactly(3))
            ->method('persist')
            ->willReturnCallback(function ($record) use ($records): void {
                $validValues = array_merge(iterator_to_array($records), [null]);
                $this->assertContains($record, $validValues);
            });

        $mockQuery = $this->createMock(Query::class);
        $mockQuery->expects($this->once())->method('execute');

        $qualified = StagingDeputyship::class;
        $mockEm->expects($this->once())
            ->method('createQuery')
            ->with("DELETE FROM {$qualified} sd")
            ->willReturn($mockQuery);

        $result = $sut->load($fileLocation);

        $this->assertTrue($result->loadedOk);
    }
}
