<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\v2\Registration\DeputyshipProcessing;

use Doctrine\DBAL\Exception;
use OPG\Digideps\Backend\Factory\DataFactoryInterface;
use OPG\Digideps\Backend\Factory\DataFactoryResult;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CourtOrder\CourtOrderRelationshipIngester;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\DeputyshipBuilder;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\DeputyshipBuilderResult;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\DeputyshipCandidatesSelectorResult;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\DeputyshipsCandidatesSelector;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\DeputyshipsCSVIngester;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\DeputyshipsCSVIngestResult;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\DeputyshipsCSVLoader;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\DeputyshipsCSVLoaderResult;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\DeputyshipsIngestResultRecorder;
use OPG\Digideps\Backend\v2\Registration\Enum\DeputyshipBuilderResultOutcome;
use OPG\Digideps\Backend\v2\Registration\Enum\DeputyshipCandidateAction;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\isInstanceOf;

#[AllowMockObjectsWithoutExpectations]
final class DeputyshipsCSVIngesterTest extends TestCase
{
    private DeputyshipsCSVLoader&MockObject $mockDeputyshipsCSVLoader;
    private DeputyshipsCandidatesSelector&MockObject $mockDeputyshipsCandidatesSelector;
    private DeputyshipBuilder&MockObject $mockDeputyshipBuilder;
    private DeputyshipsIngestResultRecorder&MockObject $mockDeputyshipsIngestResultRecorder;
    private DataFactoryInterface&MockObject $mockPreCsvDataFactory;
    private DataFactoryInterface&MockObject $mockPostCsvDataFactory;
    private DeputyshipsCSVIngester $sut;

    public function setUp(): void
    {
        $this->mockDeputyshipsCSVLoader = $this->createMock(DeputyshipsCSVLoader::class);
        $this->mockDeputyshipsCandidatesSelector = $this->createMock(DeputyshipsCandidatesSelector::class);
        $this->mockDeputyshipBuilder = $this->createMock(DeputyshipBuilder::class);
        $this->mockPreCsvDataFactory = $this->createMock(DataFactoryInterface::class);
        $this->mockPostCsvDataFactory = $this->createMock(DataFactoryInterface::class);
        $this->mockDeputyshipsIngestResultRecorder = $this->createMock(DeputyshipsIngestResultRecorder::class);
        $mockCourtOrderRelationshipIngester = $this->createMock(CourtOrderRelationshipIngester::class);

        $this->sut = new DeputyshipsCSVIngester(
            $this->mockDeputyshipsCSVLoader,
            $this->mockDeputyshipsCandidatesSelector,
            $this->mockDeputyshipBuilder,
            $this->mockPreCsvDataFactory,
            $this->mockPostCsvDataFactory,
            $this->mockDeputyshipsIngestResultRecorder,
            $mockCourtOrderRelationshipIngester
        );
    }

    public function testPreCSVDataFactoryFails(): void
    {
        $preCSVDataFactoryResult = new DataFactoryResult();
        $preCSVDataFactoryResult->addErrorMessages('preCSVDataFactory', ['Failed to apply data fixes']);

        $this->mockPreCsvDataFactory->expects(self::once())
            ->method('run')->with(false)
            ->willReturn($preCSVDataFactoryResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordPreCSVDataFactoryResult')
            ->with($preCSVDataFactoryResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('result')
            ->willReturn(new DeputyshipsCSVIngestResult(false, 'pre CSV data fixes failed to apply'));

        $result = $this->sut->processCsv('/tmp/deputyshipsReport.csv');

        self::assertFalse($result->success);
    }

    public function testCsvLoadFailed(): void
    {
        $preCSVDataFactoryResult = new DataFactoryResult();

        $this->mockPreCsvDataFactory->expects(self::once())
            ->method('run')->with(false)
            ->willReturn($preCSVDataFactoryResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordPreCSVDataFactoryResult')
            ->with($preCSVDataFactoryResult);

        $stubCsvLoaderResult = self::createStub(DeputyshipsCSVLoaderResult::class);
        $stubCsvLoaderResult->loadedOk = false;

        $this->mockDeputyshipsCSVLoader->expects(self::once())
            ->method('load')
            ->with('/tmp/deputyshipsReport.csv')
            ->willReturn($stubCsvLoaderResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordCsvLoadResult')
            ->with($stubCsvLoaderResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('result')
            ->willReturn(new DeputyshipsCSVIngestResult(false, 'failed to load CSV'));

        $result = $this->sut->processCsv('/tmp/deputyshipsReport.csv');

        self::assertFalse($result->success);
    }

    public function testCandidateSelectionFailed(): void
    {
        // pre CSV data fixes applied OK
        $preCSVDataFactoryResult = new DataFactoryResult();

        $this->mockPreCsvDataFactory->expects(self::once())
            ->method('run')->with(false)
            ->willReturn($preCSVDataFactoryResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordPreCSVDataFactoryResult')
            ->with($preCSVDataFactoryResult);

        // CSV load goes OK
        $stubCSVLoaderResult = $this->createStub(DeputyshipsCSVLoaderResult::class);
        $stubCSVLoaderResult->loadedOk = true;
        $this->mockDeputyshipsCSVLoader->method('load')->willReturn($stubCSVLoaderResult);
        $this->mockDeputyshipsIngestResultRecorder->method('recordCsvLoadResult');

        // candidate selection fails
        $candidatesSelectorResult = new DeputyshipCandidatesSelectorResult(
            new \ArrayIterator([]),
            0,
            new Exception('unexpected database exception')
        );

        $this->mockDeputyshipsCandidatesSelector->expects(self::once())
            ->method('select')
            ->willReturn($candidatesSelectorResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordDeputyshipCandidatesResult')
            ->with($candidatesSelectorResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('result')
            ->willReturn(new DeputyshipsCSVIngestResult(false, 'unexpected database exception'));

        $result = $this->sut->processCsv('/tmp/deputyshipsReport.csv');

        self::assertFalse($result->success);
    }

    public function testPostCSVDataFactoryFails(): void
    {
        $stubCSVLoaderResult = $this->createStub(DeputyshipsCSVLoaderResult::class);
        $stubCSVLoaderResult->loadedOk = true;

        $candidates = [['orderUid' => '11112222', 'action' => DeputyshipCandidateAction::InsertOrder]];
        $candidatesSelectorResult = new DeputyshipCandidatesSelectorResult(new \ArrayIterator($candidates), 1);

        $builderResult = new DeputyshipBuilderResult(DeputyshipBuilderResultOutcome::CandidatesApplied);

        $postCSVDataFactoryResult = new DataFactoryResult();
        $postCSVDataFactoryResult->addErrorMessages('postCSVDataFactory', ['Failed to apply data fix']);

        $this->mockPreCsvDataFactory->expects(self::once())
            ->method('run')->with(false)
            ->willReturn(new DataFactoryResult(messages: ['Success' => []]));

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordPreCSVDataFactoryResult')
            ->with(self::isInstanceOf(DataFactoryResult::class));

        $this->mockDeputyshipsCSVLoader->expects(self::once())
            ->method('load')
            ->with('/tmp/deputyshipsReport.csv')
            ->willReturn($stubCSVLoaderResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordCsvLoadResult')
            ->with($stubCSVLoaderResult);

        $this->mockDeputyshipsCandidatesSelector->expects(self::once())
            ->method('select')
            ->willReturn($candidatesSelectorResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordDeputyshipCandidatesResult')
            ->with($candidatesSelectorResult);

        $this->mockDeputyshipBuilder->expects(self::once())
            ->method('build')
            ->with(new \ArrayIterator($candidates))
            ->willReturn(new \ArrayIterator([$builderResult]));

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordBuilderResult')
            ->with($builderResult);

        $this->mockPostCsvDataFactory->expects(self::once())
            ->method('run')->with(false)
            ->willReturn($postCSVDataFactoryResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordPostCSVDataFactoryResult')
            ->with($postCSVDataFactoryResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('result')
            ->willReturn(new DeputyshipsCSVIngestResult(false, ''));

        $result = $this->sut->processCsv('/tmp/deputyshipsReport.csv');

        self::assertFalse($result->success);
    }

    public function testProcessCsvRows(): void
    {
        $stubCSVLoaderResult = $this->createStub(DeputyshipsCSVLoaderResult::class);
        $stubCSVLoaderResult->loadedOk = true;

        $candidates = [['orderUid' => '11112222', 'action' => DeputyshipCandidateAction::InsertOrder]];
        $candidatesSelectorResult = new DeputyshipCandidatesSelectorResult(new \ArrayIterator($candidates), 1);

        $builderResult = new DeputyshipBuilderResult(DeputyshipBuilderResultOutcome::CandidatesApplied);

        $this->mockPreCsvDataFactory->expects(self::once())
            ->method('run')->with(false)
            ->willReturn(new DataFactoryResult(messages: ['Success' => []]));

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordPreCSVDataFactoryResult')
            ->with(isInstanceOf(DataFactoryResult::class));

        $this->mockDeputyshipsCSVLoader->expects(self::once())
            ->method('load')
            ->with('/tmp/deputyshipsReport.csv')
            ->willReturn($stubCSVLoaderResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordCsvLoadResult')
            ->with($stubCSVLoaderResult);

        $this->mockDeputyshipsCandidatesSelector->expects(self::once())
            ->method('select')
            ->willReturn($candidatesSelectorResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordDeputyshipCandidatesResult')
            ->with($candidatesSelectorResult);

        $this->mockDeputyshipBuilder->expects(self::once())
            ->method('build')
            ->with(new \ArrayIterator($candidates))
            ->willReturn(new \ArrayIterator([$builderResult]));

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordBuilderResult')
            ->with($builderResult);

        $this->mockPostCsvDataFactory->expects(self::once())
            ->method('run')->with(false)
            ->willReturn(new DataFactoryResult(messages: ['Success' => []]));

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordPostCSVDataFactoryResult')
            ->with(isInstanceOf(DataFactoryResult::class));

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('result')
            ->willReturn(new DeputyshipsCSVIngestResult(true, ''));

        $result = $this->sut->processCsv('/tmp/deputyshipsReport.csv');

        self::assertTrue($result->success);
    }
}
