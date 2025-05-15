<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

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
        $candidateOrder1_1 = ['action' => DeputyshipCandidateAction::InsertOrderDeputy, 'orderUid' => '11112222'];
        $candidateOrder1_2 = ['action' => DeputyshipCandidateAction::InsertOrderReport, 'orderUid' => '11112222'];
        $candidateOrder1_3 = ['action' => DeputyshipCandidateAction::InsertOrder, 'orderUid' => '11112222'];

        $candidateOrder2_1 = ['action' => DeputyshipCandidateAction::InsertOrder, 'orderUid' => '22223333'];
        $candidateOrder2_2 = ['action' => DeputyshipCandidateAction::InsertOrderNdr, 'orderUid' => '22223333'];

        // expected candidate grouping before being sent for conversion
        $candidatesOrder1 = ['INSERT' => $candidateOrder1_3, 'OTHER' => [$candidateOrder1_1, $candidateOrder1_2]];
        $candidatesOrder2 = ['INSERT' => $candidateOrder2_1, 'OTHER' => [$candidateOrder2_2]];

        // candidates are pre-sorted by court order UID
        $candidates = new \ArrayIterator([
            $candidateOrder1_1,
            $candidateOrder1_2,
            $candidateOrder1_3,
            $candidateOrder2_1,
            $candidateOrder2_2,
        ]);

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

        self::assertCount(2, iterator_to_array($results));
    }
}
