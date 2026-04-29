<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Factory;

use OPG\Digideps\Backend\Factory\ChainedDataFactory;
use OPG\Digideps\Backend\Factory\DataFactoryInterface;
use OPG\Digideps\Backend\Factory\DataFactoryResult;
use OPG\Digideps\Backend\Factory\FactoryExecutionFlag;
use PHPUnit\Framework\TestCase;

class ChainedDataFactoryTest extends TestCase
{
    public function testRun(): void
    {
        // mocks and stubs
        $success = new DataFactoryResult();
        $success->addMessages('Updates', ['Factory 1 updates successful']);
        $success->addMessages('Deletes', ['Deleted record with ID 1', 'Deleted record with ID 2']);

        $mockDataFactory1 = $this->createMock(DataFactoryInterface::class);
        $mockDataFactory1->expects(self::once())->method('getName')->willReturn('DataFactory1');
        $mockDataFactory1->expects(self::once())->method('run')->with(false)->willReturn($success);

        $failure = new DataFactoryResult();
        $failure->addErrorMessages('UpdateErrors', ['Update error in factory 2']);

        $mockDataFactory2 = $this->createMock(DataFactoryInterface::class);
        $mockDataFactory2->expects(self::once())->method('getName')->willReturn('DataFactory2');
        $mockDataFactory2->expects(self::once())->method('run')->with(false)->willReturn($failure);

        // sut
        $sut = new ChainedDataFactory(dataFactories: [
            [$mockDataFactory1, FactoryExecutionFlag::Active->value],
            [$mockDataFactory2, FactoryExecutionFlag::Active->value],
        ]);
        $result = $sut->run(false);

        // assertions
        self::assertFalse($result->isSuccessful());

        self::assertEquals(
            [
                'DataFactory1:Updates' => ['Factory 1 updates successful'],
                'DataFactory1:Deletes' => ['Deleted record with ID 1', 'Deleted record with ID 2'],
            ],
            $result->getMessages()
        );

        self::assertEquals(['DataFactory2:UpdateErrors' => ['Update error in factory 2']], $result->getErrorMessages());
    }

    public function testRunDry(): void
    {
        // mocks and stubs
        $success = new DataFactoryResult();
        $success->addMessages('Updates', ['Factory 1 updates successful']);
        $success->addMessages('Deletes', ['Deleted record with ID 1', 'Deleted record with ID 2']);

        $mockDataFactory1 = $this->createMock(DataFactoryInterface::class);
        $mockDataFactory1->expects(self::once())->method('getName')->willReturn('DataFactory1');
        $mockDataFactory1->expects(self::once())->method('run')->with(true)->willReturn($success);

        $failure = new DataFactoryResult();
        $failure->addErrorMessages('UpdateErrors', ['Update error in factory 2']);

        $mockDataFactory2 = $this->createMock(DataFactoryInterface::class);
        $mockDataFactory2->expects(self::once())->method('getName')->willReturn('DataFactory2');
        $mockDataFactory2->expects(self::once())->method('run')->with(true)->willReturn($failure);

        // sut
        $sut = new ChainedDataFactory(dataFactories: [
            [$mockDataFactory1, FactoryExecutionFlag::Active->value],
            [$mockDataFactory2, FactoryExecutionFlag::Active->value],
        ]);
        $result = $sut->run(true);

        // assertions
        self::assertFalse($result->isSuccessful());

        self::assertEquals(
            [
                'DataFactory1:Updates' => ['Factory 1 updates successful'],
                'DataFactory1:Deletes' => ['Deleted record with ID 1', 'Deleted record with ID 2'],
            ],
            $result->getMessages()
        );

        self::assertEquals(['DataFactory2:UpdateErrors' => ['Update error in factory 2']], $result->getErrorMessages());
    }

    public function testRunConfig(): void
    {
        // mocks and stubs
        $success = new DataFactoryResult();
        $success->addMessages('Updates', ['Factory 1 updates successful']);
        $success->addMessages('Deletes', ['Deleted record with ID 1', 'Deleted record with ID 2']);

        $mockDataFactory1 = $this->createMock(DataFactoryInterface::class);
        $mockDataFactory1->expects(self::once())->method('getName')->willReturn('DataFactory1');
        $mockDataFactory1->expects(self::once())->method('run')->with(true)->willReturn($success);

        $failure = new DataFactoryResult();
        $failure->addErrorMessages('UpdateErrors', ['Update error in factory 2']);

        $mockDataFactory2 = $this->createMock(DataFactoryInterface::class);
        $mockDataFactory2->expects(self::once())->method('getName')->willReturn('DataFactory2');
        $mockDataFactory2->expects(self::once())->method('run')->with(false)->willReturn($failure);

        $mockDataFactory3 = $this->createMock(DataFactoryInterface::class);
        $mockDataFactory3->expects(self::never())->method('getName');
        $mockDataFactory3->expects(self::never())->method('run');

        // sut
        $sut = new ChainedDataFactory(dataFactories: [
            [$mockDataFactory1, FactoryExecutionFlag::DryRunOnly->value],
            [$mockDataFactory2, FactoryExecutionFlag::Active->value],
            [$mockDataFactory3, FactoryExecutionFlag::Inactive->value],
        ]);
        $result = $sut->run(false);

        // assertions
        self::assertFalse($result->isSuccessful());

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
