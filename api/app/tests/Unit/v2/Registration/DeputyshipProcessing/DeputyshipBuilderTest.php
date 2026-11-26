<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\v2\Registration\DeputyshipProcessing;

use OPG\Digideps\Backend\Domain\Deputyship\DeputyshipCandidatesConverter;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\DeputyshipBuilder;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\DeputyshipBuilderResult;
use OPG\Digideps\Backend\v2\Registration\Enum\DeputyshipBuilderResultOutcome;
use OPG\Digideps\Backend\v2\Registration\Enum\DeputyshipCandidateAction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DeputyshipBuilderTest extends TestCase
{
    /** @param array<string, mixed> $deputyCandidate */
    #[DataProvider('invalidDeputyCandidates')]
    public function testBuildRejectsDeputyCandidateWithoutId(array $deputyCandidate): void
    {
        $mockConverter = $this->createMock(DeputyshipCandidatesConverter::class);
        $mockConverter->expects($this->never())->method('convert');
        $sut = new DeputyshipBuilder($mockConverter);

        $candidates = new \ArrayIterator([
            ['action' => DeputyshipCandidateAction::InsertOrder, 'orderUid' => '11112222'],
            ['action' => DeputyshipCandidateAction::InsertOrderDeputy, 'orderUid' => '11112222', 'deputyId' => 123456],
            $deputyCandidate,
        ]);

        $results = iterator_to_array($sut->build($candidates));

        self::assertCount(1, $results);
        self::assertSame(DeputyshipBuilderResultOutcome::CandidateListError, $results[0]->getOutcome());
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function invalidDeputyCandidates(): array
    {
        return [
            'missing deputy ID' => [
                ['action' => DeputyshipCandidateAction::InsertOrderDeputy, 'orderUid' => '11112222'],
            ],
            'null deputy ID' => [
                ['action' => DeputyshipCandidateAction::InsertOrderDeputy, 'orderUid' => '11112222', 'deputyId' => null],
            ],
        ];
    }

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
        $candidateOrder1_1 = ['action' => DeputyshipCandidateAction::InsertOrderDeputy, 'orderUid' => $orderUid1, 'deputyId' => 123456];
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
