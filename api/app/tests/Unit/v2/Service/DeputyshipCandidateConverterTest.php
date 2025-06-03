<?php

declare(strict_types=1);

namespace App\v2\Service;

use App\Model\DeputyshipProcessingRawDbAccess;
use App\Model\DeputyshipProcessingRawDbAccessResult;
use App\v2\Registration\DeputyshipProcessing\DeputyshipCandidatesGroup;
use App\v2\Registration\Enum\DeputyshipBuilderResultOutcome;
use App\v2\Registration\Enum\DeputyshipCandidateAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeputyshipCandidateConverterTest extends TestCase
{
    private DeputyshipProcessingRawDbAccess&MockObject $mockDbAccess;
    private DeputyshipCandidatesConverter $sut;

    public function setUp(): void
    {
        $this->mockDbAccess = $this->createMock(DeputyshipProcessingRawDbAccess::class);

        // this always happens, regardless
        $this->mockDbAccess->expects($this->once())->method('beginTransaction');

        $this->sut = new DeputyshipCandidatesConverter($this->mockDbAccess);
    }

    public function testConvertOrderCouldNotBeCreatedFail(): void
    {
        $orderUid = '1122334455';
        $insertOrder = [];
        $expectedError = 'error inserting court order';

        $candidateGroup = new DeputyshipCandidatesGroup();
        $candidateGroup->orderUid = $orderUid;
        $candidateGroup->insertOrder = $insertOrder;

        $mockResult = $this->createMock(DeputyshipProcessingRawDbAccessResult::class);
        $mockResult->success = false;
        $mockResult->error = $expectedError;

        $this->mockDbAccess->expects($this->once())->method('insertOrder')->with($insertOrder)->willReturn($mockResult);
        $this->mockDbAccess->expects($this->once())->method('rollback');

        // call
        $builderResult = $this->sut->convert($candidateGroup, false);

        // assert
        self::assertEquals(DeputyshipBuilderResultOutcome::InsertOrderFailed, $builderResult->getOutcome());

        $error = $builderResult->getErrors()[0];
        self::assertStringContainsString($expectedError, $error);
    }

    public function testConvertOrderCouldNotBeFoundFail(): void
    {
        $orderUid = '1122334466';
        $expectedError = 'could not find court order';

        $candidateGroup = new DeputyshipCandidatesGroup();
        $candidateGroup->orderUid = $orderUid;

        $mockResult = $this->createMock(DeputyshipProcessingRawDbAccessResult::class);
        $mockResult->success = false;
        $mockResult->error = $expectedError;

        $this->mockDbAccess->expects($this->once())->method('findOrderId')->with($orderUid)->willReturn($mockResult);

        // call
        $builderResult = $this->sut->convert($candidateGroup, false);

        // assert
        self::assertEquals(DeputyshipBuilderResultOutcome::NoExistingOrder, $builderResult->getOutcome());

        $error = $builderResult->getErrors()[0];
        self::assertStringContainsString($expectedError, $error);
    }

    // so long as the court order was inserted/found, the conversion is considered a success, even if
    // one or more candidates failed
    public function testConvertCandidateNotAppliedSuccess(): void
    {
        $orderUid = '1122334477';
        $orderId = 1;
        $expectedError = 'insert order deputy not applied';

        $insertOrderDeputyCandidate = ['action' => DeputyshipCandidateAction::InsertOrderDeputy];

        $candidateGroup = new DeputyshipCandidatesGroup();
        $candidateGroup->orderUid = $orderUid;
        $candidateGroup->insertOthers[] = $insertOrderDeputyCandidate;

        $mockResult1 = $this->createMock(DeputyshipProcessingRawDbAccessResult::class);
        $mockResult1->success = true;
        $mockResult1->data = $orderId;

        $this->mockDbAccess->expects($this->once())->method('findOrderId')->with($orderUid)->willReturn($mockResult1);
        $this->mockDbAccess->expects($this->once())->method('endTransaction');

        // inserting the court_order_deputy entry fails
        $mockResult2 = $this->createMock(DeputyshipProcessingRawDbAccessResult::class);
        $mockResult2->success = false;
        $mockResult2->error = $expectedError;

        $this->mockDbAccess->expects($this->once())
            ->method('insertOrderDeputy')
            ->with($orderId, $insertOrderDeputyCandidate)
            ->willReturn($mockResult2);

        // call
        $builderResult = $this->sut->convert($candidateGroup, false);

        // assert
        self::assertEquals(DeputyshipBuilderResultOutcome::CandidatesApplied, $builderResult->getOutcome());

        $error = $builderResult->getErrors()[0];
        self::assertStringContainsString($expectedError, $error);
    }

    // a dry run looks just like a normal run, except none of the converted candidates are saved to the database
    // (the transaction is rolled back)
    public function testConvertDryRunSuccess(): void
    {
        $orderUid = '1122334455';
        $insertOrder = [];

        $candidateGroup = new DeputyshipCandidatesGroup();
        $candidateGroup->orderUid = $orderUid;
        $candidateGroup->insertOrder = $insertOrder;

        $mockResult1 = $this->createMock(DeputyshipProcessingRawDbAccessResult::class);
        $mockResult1->action = DeputyshipCandidateAction::InsertOrder;
        $mockResult1->success = true;

        $mockResult2 = $this->createMock(DeputyshipProcessingRawDbAccessResult::class);
        $mockResult2->success = true;
        $mockResult2->data = 1;

        $this->mockDbAccess->expects($this->once())->method('insertOrder')->with($insertOrder)->willReturn($mockResult1);
        $this->mockDbAccess->expects($this->once())->method('findOrderId')->with($orderUid)->willReturn($mockResult2);

        // expect rollback as this is a dry run
        $this->mockDbAccess->expects($this->once())->method('rollback');

        // call
        $builderResult = $this->sut->convert($candidateGroup, true);

        // assert
        self::assertEquals(DeputyshipBuilderResultOutcome::CandidatesApplied, $builderResult->getOutcome());

        // even though this is a dry run, we're still recording the insert order candidate as "applied"
        self::assertEquals(1, $builderResult->getNumCandidatesApplied());
    }

    // use a full set of candidates to test all the branches
    public function testConvertSuccess(): void
    {
        $orderUid = '1122334455';
        $orderId = 2;

        $insertOrderDeputy = ['action' => DeputyshipCandidateAction::InsertOrderDeputy];
        $insertOrderReport = ['action' => DeputyshipCandidateAction::InsertOrderReport];
        $insertOrderNdr = ['action' => DeputyshipCandidateAction::InsertOrderNdr];
        $updateOrderStatus = ['action' => DeputyshipCandidateAction::UpdateOrderStatus];
        $updateDeputyStatus = ['action' => DeputyshipCandidateAction::UpdateDeputyStatus];

        $findOrderResult = $this->createMockResult(DeputyshipCandidateAction::FindOrder, data: $orderId);
        $insertOrderDeputyResult = $this->createMockResult(DeputyshipCandidateAction::InsertOrderDeputy);
        $insertOrderReportResult = $this->createMockResult(DeputyshipCandidateAction::InsertOrderReport);
        $insertOrderNdrResult = $this->createMockResult(DeputyshipCandidateAction::InsertOrderNdr);
        $updateOrderStatusResult = $this->createMockResult(DeputyshipCandidateAction::UpdateOrderStatus);
        $updateDeputyStatusResult = $this->createMockResult(DeputyshipCandidateAction::UpdateDeputyStatus);

        $candidateGroup = new DeputyshipCandidatesGroup();
        $candidateGroup->orderUid = $orderUid;
        $candidateGroup->insertOthers = [$insertOrderDeputy, $insertOrderReport, $insertOrderNdr];
        $candidateGroup->updates = [$updateOrderStatus, $updateDeputyStatus];

        $this->mockDbAccess->expects($this->once())->method('findOrderId')->with($orderUid)->willReturn($findOrderResult);
        $this->mockDbAccess->expects($this->once())->method('insertOrderDeputy')->with($orderId, $insertOrderDeputy)->willReturn($insertOrderDeputyResult);
        $this->mockDbAccess->expects($this->once())->method('insertOrderReport')->with($orderId, $insertOrderReport)->willReturn($insertOrderReportResult);
        $this->mockDbAccess->expects($this->once())->method('insertOrderNdr')->with($orderId, $insertOrderNdr)->willReturn($insertOrderNdrResult);
        $this->mockDbAccess->expects($this->once())->method('updateOrderStatus')->with($orderId, $updateOrderStatus)->willReturn($updateOrderStatusResult);
        $this->mockDbAccess->expects($this->once())->method('updateDeputyStatus')->with($orderId, $updateDeputyStatus)->willReturn($updateDeputyStatusResult);
        $this->mockDbAccess->expects($this->once())->method('endTransaction');

        // call
        $builderResult = $this->sut->convert($candidateGroup, false);

        // assert
        self::assertEquals(DeputyshipBuilderResultOutcome::CandidatesApplied, $builderResult->getOutcome());

        self::assertEquals(5, $builderResult->getNumCandidatesApplied());
    }

    private function createMockResult(
        DeputyshipCandidateAction $action,
        bool $success = true,
        mixed $data = null,
    ): DeputyshipProcessingRawDbAccessResult&MockObject {
        $result = $this->createMock(DeputyshipProcessingRawDbAccessResult::class);
        $result->action = $action;
        $result->success = $success;
        $result->data = $data;

        return $result;
    }
}
