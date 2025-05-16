<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\v2\Registration\Enum\DeputyshipCandidateAction;
use PHPUnit\Framework\TestCase;

class DeputyshipCandidatesGroupTest extends TestCase
{
    public function testDeputyshipCandidatesGroup(): void
    {
        $expectedInsertOthers = [
            ['action' => DeputyshipCandidateAction::InsertOrderNdr, 'orderUid' => '555445566'],
            ['action' => DeputyshipCandidateAction::InsertOrderNdr, 'orderUid' => '555445566'],
            ['action' => DeputyshipCandidateAction::InsertOrderDeputy, 'orderUid' => '555445566'],
            ['action' => DeputyshipCandidateAction::InsertOrderDeputy, 'orderUid' => '555445566'],
        ];

        $expectedUpdates = [
            ['action' => DeputyshipCandidateAction::UpdateOrderStatus, 'orderUid' => '555445566'],
            ['action' => DeputyshipCandidateAction::UpdateOrderStatus, 'orderUid' => '555445566'],
            ['action' => DeputyshipCandidateAction::UpdateDeputyStatus, 'orderUid' => '555445566'],
            ['action' => DeputyshipCandidateAction::UpdateDeputyStatus, 'orderUid' => '555445566'],
        ];

        $expectedInsertOrder = [
            'action' => DeputyshipCandidateAction::InsertOrder, 'orderUid' => '555445566', 'orderStatus' => 'CLOSED',
        ];

        $expectedIteratorOrder = array_merge(
            [$expectedInsertOrder],
            $expectedInsertOthers,
            $expectedUpdates
        );

        $candidates = array_merge(
            [
                ['action' => DeputyshipCandidateAction::InsertOrder, 'orderUid' => '555445566', 'orderStatus' => 'ACTIVE'],
                $expectedInsertOrder,
            ],
            $expectedUpdates,
            $expectedInsertOthers
        );

        $candidateGroup = DeputyshipCandidatesGroup::create('555445566', $candidates);

        self::assertEquals(9, $candidateGroup->totalCandidates());
        self::assertEquals('555445566', $candidateGroup->orderUid);
        self::assertEquals('CLOSED', $candidateGroup->insertOrder['orderStatus']);
        self::assertEquals($expectedInsertOthers, $candidateGroup->insertOthers);
        self::assertEquals($expectedUpdates, $candidateGroup->updates);
        self::assertEquals($expectedIteratorOrder, iterator_to_array($candidateGroup->getIterator()));
    }
}
