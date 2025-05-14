<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingSelectedCandidate;
use App\v2\Registration\Enum\DeputyshipBuilderResultOutcome;
use App\v2\Registration\Enum\DeputyshipCandidateAction;
use App\v2\Service\DeputyshipCandidateConverter;
use PHPUnit\Framework\TestCase;

class DeputyshipBuilderTest extends TestCase
{
    private DeputyshipCandidateConverter $mockConverter;
    private DeputyshipBuilder $sut;

    public function setUp(): void
    {
        $this->mockConverter = $this->createMock(DeputyshipCandidateConverter::class);

        $this->sut = new DeputyshipBuilder($this->mockConverter);
    }

    public function testBuild(): void
    {
        // candidates for two separate orders
        $candidateOrder1_1 = new StagingSelectedCandidate(DeputyshipCandidateAction::InsertOrder, '11112222');
        $candidateOrder1_2 = new StagingSelectedCandidate(DeputyshipCandidateAction::InsertOrderDeputy, '11112222');
        $candidateOrder1_3 = new StagingSelectedCandidate(DeputyshipCandidateAction::InsertOrderReport, '11112222');
        $candidatesOrder1 = [$candidateOrder1_1, $candidateOrder1_2, $candidateOrder1_3];

        $candidateOrder2_1 = new StagingSelectedCandidate(DeputyshipCandidateAction::InsertOrder, '22223333');
        $candidateOrder2_2 = new StagingSelectedCandidate(DeputyshipCandidateAction::InsertOrderNdr, '22223333');
        $candidatesOrder2 = [$candidateOrder2_1, $candidateOrder2_2];

        // candidates are pre-sorted by court order UID
        $candidates = new \ArrayIterator(array_merge($candidatesOrder1, $candidatesOrder2));

        // two groups should be passed to the converter, and the outputs from those calls yielded as the return value
        $matcher = $this->exactly(2);
        $this->mockConverter->expects($matcher)
            ->method('createEntitiesFromCandidates')
            ->willReturnCallback(function ($group) use ($matcher, $candidatesOrder1, $candidatesOrder2) {
                match ($matcher->getInvocationCount()) {
                    1 => self::assertEquals($candidatesOrder1, $group),
                    2 => self::assertEquals($candidatesOrder2, $group),
                };

                return new DeputyshipBuilderResult(DeputyshipBuilderResultOutcome::EntitiesBuiltSuccessfully);
            });

        $results = $this->sut->build($candidates);

        self::assertEquals(2, count(iterator_to_array($results)));
    }
}
