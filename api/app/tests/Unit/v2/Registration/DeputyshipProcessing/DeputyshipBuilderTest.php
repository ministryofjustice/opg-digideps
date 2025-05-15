<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\v2\Registration\Enum\DeputyshipBuilderResultOutcome;
use App\v2\Registration\Enum\DeputyshipCandidateAction;
use App\v2\Service\DeputyshipCandidatesConverter;
use PHPUnit\Framework\TestCase;

class DeputyshipBuilderTest extends TestCase
{
    private DeputyshipCandidatesConverter $mockConverter;
    private int $testBuildCounter = 0;
    private DeputyshipBuilder $sut;

    public function setUp(): void
    {
        $this->mockConverter = $this->createMock(DeputyshipCandidatesConverter::class);

        $this->sut = new DeputyshipBuilder($this->mockConverter);
    }

    public function testBuild(): void
    {
        // candidates for two separate orders
        $orderUid1 = '11112222';
        $candidateOrder1_1 = ['action' => DeputyshipCandidateAction::InsertOrderDeputy, 'orderUid' => $orderUid1];
        $candidateOrder1_2 = ['action' => DeputyshipCandidateAction::InsertOrderReport, 'orderUid' => $orderUid1];
        $candidateOrder1_3 = ['action' => DeputyshipCandidateAction::InsertOrder, 'orderUid' => $orderUid1];

        $orderUid2 = '22223333';
        $candidateOrder2_1 = ['action' => DeputyshipCandidateAction::InsertOrder, 'orderUid' => $orderUid2];
        $candidateOrder2_2 = ['action' => DeputyshipCandidateAction::InsertOrderNdr, 'orderUid' => $orderUid2];

        // candidates are pre-sorted by court order UID
        $candidates = new \ArrayIterator([
            $candidateOrder1_1,
            $candidateOrder1_2,
            $candidateOrder1_3,
            $candidateOrder2_1,
            $candidateOrder2_2,
        ]);

        // two groups should be passed to the converter
        $uidsExpected = [$orderUid1, $orderUid2];
        $this->mockConverter->expects($this->exactly(2))
            ->method('createEntitiesFromCandidates')
            ->willReturnCallback(function ($calledWith) use ($uidsExpected) {
                $this->assertEquals($uidsExpected[$this->testBuildCounter], $calledWith->orderUid);
                ++$this->testBuildCounter;

                return new DeputyshipBuilderResult(DeputyshipBuilderResultOutcome::EntitiesBuiltSuccessfully);
            });

        $results = $this->sut->build($candidates);

        self::assertCount(2, iterator_to_array($results));
    }
}
