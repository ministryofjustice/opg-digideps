<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Factory\ChainedDataFactory;
use App\Factory\DataFactoryInterface;
use App\Factory\DataFactoryResult;
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
        $mockDataFactory1->expects(self::once())->method('run')->willReturn($success);

        $failure = new DataFactoryResult();
        $failure->addErrorMessages('UpdateErrors', ['Update error in factory 2']);

        $mockDataFactory2 = $this->createMock(DataFactoryInterface::class);
        $mockDataFactory2->expects(self::once())->method('getName')->willReturn('DataFactory2');
        $mockDataFactory2->expects(self::once())->method('run')->willReturn($failure);

        // sut
        $sut = new ChainedDataFactory([$mockDataFactory1, $mockDataFactory2]);
        $result = $sut->run();

        // assertions
        self::assertFalse($result->getSuccess());

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
