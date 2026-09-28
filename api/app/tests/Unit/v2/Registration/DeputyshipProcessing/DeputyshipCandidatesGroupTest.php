<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\v2\Registration\DeputyshipProcessing;

use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\DeputyshipCandidatesGroup;
use OPG\Digideps\Backend\v2\Registration\Enum\DeputyshipCandidateAction;
use PHPUnit\Framework\TestCase;

final class DeputyshipCandidatesGroupTest extends TestCase
{
    public function testDeputyshipCandidatesGroup(): void
    {
        $expectedInsertOthers = [
            ['action' => DeputyshipCandidateAction::InsertOrderDeputy, 'orderUid' => '555445566', 'deputyId' => '123456', 'deputyStatusOnOrder' => true],
            ['action' => DeputyshipCandidateAction::InsertOrderDeputy, 'orderUid' => '555445566', 'deputyId' => '789654'],
        ];

        $expectedUpdates = [
            ['action' => DeputyshipCandidateAction::UpdateOrderStatus, 'orderUid' => '555445566'],
            ['action' => DeputyshipCandidateAction::UpdateOrderStatus, 'orderUid' => '555445566'],
            ['action' => DeputyshipCandidateAction::UpdateDeputyStatus, 'orderUid' => '555445566', 'deputyId' => '456611'],
            ['action' => DeputyshipCandidateAction::UpdateDeputyStatus, 'orderUid' => '555445566', 'deputyId' => '116578'],
        ];

        $expectedInsertOrder = [
            'action' => DeputyshipCandidateAction::InsertOrder, 'orderUid' => '555445566', 'orderStatus' => 'CLOSED',
        ];

        $expectedIteratorOrder = array_merge(
            $expectedInsertOthers,
            $expectedUpdates
        );

        $candidates = array_merge(
            [
                ['action' => DeputyshipCandidateAction::InsertOrder, 'orderUid' => '555445566', 'orderStatus' => 'ACTIVE'],
                $expectedInsertOrder,
            ],
            $expectedUpdates,
            [['action' => DeputyshipCandidateAction::InsertOrderDeputy, 'orderUid' => '555445566', 'deputyId' => '123456', 'deputyStatusOnOrder' => false]],
            $expectedInsertOthers,
        );

        $candidateGroup = DeputyshipCandidatesGroup::create('555445566', $candidates);

        self::assertEquals(7, $candidateGroup->totalCandidates());
        self::assertEquals('555445566', $candidateGroup->orderUid);
        self::assertEquals('CLOSED', $candidateGroup->insertOrder['orderStatus']);
        self::assertEquals($expectedInsertOthers, array_values($candidateGroup->insertOthers));
        self::assertEquals($expectedUpdates, array_values($candidateGroup->updates));
        self::assertEquals($expectedIteratorOrder, iterator_to_array($candidateGroup->getIterator()));
    }
}
