<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\v2\Registration\DeputyshipProcessing;

use OPG\Digideps\Backend\Domain\Deputyship\DeputyshipCandidatesConverter;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\DeputyshipBuilder;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\DeputyshipBuilderResult;
use OPG\Digideps\Backend\v2\Registration\Enum\DeputyshipBuilderResultOutcome;
use OPG\Digideps\Backend\v2\Registration\Enum\DeputyshipCandidateAction;
use PHPUnit\Framework\TestCase;

final class DeputyshipBuilderTest extends TestCase
{
    public function testBuildNoCandidatesFail(): void
    {
        $stubConverter = $this->createStub(DeputyshipCandidatesConverter::class);
        $sut = new DeputyshipBuilder($stubConverter);

        $candidates = new \ArrayIterator([]);

        $results = iterator_to_array($sut->build($candidates));

        /** @var DeputyshipBuilderResult $result */
        $result = $results[0];

        self::assertEquals(DeputyshipBuilderResultOutcome::Skipped, $result->getOutcome());
    }

    public function testBuildSuccess(): void
    {
        $mockConverter = $this->createMock(DeputyshipCandidatesConverter::class);
        $sut = new DeputyshipBuilder($mockConverter);

        // candidates for two separate orders
        $orderUid1 = '11112222';
        $candidateOrder1_1 = ['action' => DeputyshipCandidateAction::InsertOrderDeputy, 'orderUid' => $orderUid1];
        $candidateOrder1_2 = ['action' => DeputyshipCandidateAction::InsertOrderReport, 'orderUid' => $orderUid1];
        $candidateOrder1_3 = ['action' => DeputyshipCandidateAction::InsertOrder, 'orderUid' => $orderUid1];

        $orderUid2 = '22223333';
        $candidateOrder2_1 = ['action' => DeputyshipCandidateAction::InsertOrder, 'orderUid' => $orderUid2];

        // candidates are pre-sorted by court order UID
        $candidates = new \ArrayIterator([
            $candidateOrder1_1,
            $candidateOrder1_2,
            $candidateOrder1_3,
            $candidateOrder2_1,
        ]);

        $caller = new \stdClass();
        $caller->counter = 0;

        // two groups should be passed to the converter
        $uidsExpected = [$orderUid1, $orderUid2];
        $mockConverter->expects($this->exactly(2))
            ->method('convert')
            ->willReturnCallback(function ($calledWith) use ($uidsExpected, $caller): DeputyshipBuilderResult {
                $this->assertEquals($uidsExpected[$caller->counter], $calledWith->orderUid);
                ++$caller->counter;

                return new DeputyshipBuilderResult(DeputyshipBuilderResultOutcome::CandidatesApplied);
            });

        $results = $sut->build($candidates);

        self::assertCount(2, iterator_to_array($results));
    }
}
