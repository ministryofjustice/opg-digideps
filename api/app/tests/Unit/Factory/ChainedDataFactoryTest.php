<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Factory\ChainedDataFactory;
use App\Factory\DataFactoryInterface;
use App\Factory\DataFactoryResult;
use App\v2\Registration\DeputyshipProcessing\BuilderResult;
use App\v2\Registration\DeputyshipProcessing\PostProcessing\ReportType\ReportTypeBuilderResult;
use App\v2\Registration\Enum\ReportTypeBuilderResultOutcome;
use PHPUnit\Framework\TestCase;

class ChainedDataFactoryTest extends TestCase
{
    public function testRun(): void
    {
        // mocks and stubs
        $success = new DataFactoryResult();
        $success->addMessages('Updates', ['Factory 1 updates successful']);
        $success->addMessages('Deletes', ['Deleted record with ID 1', 'Deleted record with ID 2']);

        $builderResult = new ReportTypeBuilderResult(ReportTypeBuilderResultOutcome::UpdateSuccess);

        $mockDataFactory1 = $this->createMock(DataFactoryInterface::class);
        $mockDataFactory1->expects(self::once())->method('getName')->willReturn('DataFactory1');
        $mockDataFactory1->expects(self::once())->method('run')->willReturn([$success, $builderResult]);

        $failure = new DataFactoryResult();
        $failure->addErrorMessages('UpdateErrors', ['Update error in factory 2']);

        $mockDataFactory2 = $this->createMock(DataFactoryInterface::class);
        $mockDataFactory2->expects(self::once())->method('getName')->willReturn('DataFactory2');
        $mockDataFactory2->expects(self::once())->method('run')->willReturn([$failure, null]);

        // sut
        $sut = new ChainedDataFactory(dataFactories: [$mockDataFactory1, $mockDataFactory2]);
        [$result, $builderResults] = $sut->run();

        // assertions
        self::assertFalse($result->isSuccessful());
        self::assertCount(1, $builderResults);
        self::assertEquals(ReportTypeBuilderResultOutcome::UpdateSuccess, $builderResults[0]->getOutcome());

        self::assertEquals(
            [
                'DataFactory1:Updates' => ['Factory 1 updates successful'],
                'DataFactory1:Deletes' => ['Deleted record with ID 1', 'Deleted record with ID 2'],
            ],
            $result->getMessages()
        );

        self::assertEquals(['DataFactory2:UpdateErrors' => ['Update error in factory 2']], $result->getErrorMessages());
    }
}
