<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\v2\Service;

use OPG\Digideps\Backend\Domain\Deputyship\DeputyshipCandidatesConverter;
use OPG\Digideps\Backend\Domain\Deputyship\DeputyshipProcessingRawDbAccess;
use OPG\Digideps\Backend\Domain\Deputyship\DeputyshipProcessingRawDbAccessResult;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\DeputyshipCandidatesGroup;
use OPG\Digideps\Backend\v2\Registration\Enum\DeputyshipBuilderResultOutcome;
use OPG\Digideps\Backend\v2\Registration\Enum\DeputyshipCandidateAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class DeputyshipCandidateConverterTest extends TestCase
{
    private DeputyshipProcessingRawDbAccess&MockObject $mockDbAccess;
    private DeputyshipCandidatesConverter $sut;

    public function setUp(): void
    {
        $this->mockDbAccess = $this->createMock(DeputyshipProcessingRawDbAccess::class);

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

        $stubResult = $this->createStub(DeputyshipProcessingRawDbAccessResult::class);
        $stubResult->success = false;
        $stubResult->error = $expectedError;

        $this->mockDbAccess->expects($this->once())->method('beginTransaction');
        $this->mockDbAccess->expects($this->once())->method('insertOrder')->with($insertOrder)->willReturn($stubResult);
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

        $stubResult = $this->createStub(DeputyshipProcessingRawDbAccessResult::class);
        $stubResult->success = false;
        $stubResult->error = $expectedError;

        $this->mockDbAccess->expects($this->once())->method('beginTransaction');
        $this->mockDbAccess->expects($this->once())->method('findOrderId')->with($orderUid)->willReturn($stubResult);

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

        $stubResult1 = $this->createStub(DeputyshipProcessingRawDbAccessResult::class);
        $stubResult1->success = true;
        $stubResult1->data = $orderId;

        $this->mockDbAccess->expects($this->once())->method('beginTransaction');
        $this->mockDbAccess->expects($this->once())->method('findOrderId')->with($orderUid)->willReturn($stubResult1);
        $this->mockDbAccess->expects($this->once())->method('rollback');

        // inserting the court_order_deputy entry fails
        $stubResult2 = $this->createStub(DeputyshipProcessingRawDbAccessResult::class);
        $stubResult2->success = false;
        $stubResult2->error = $expectedError;

        $this->mockDbAccess->expects($this->once())
            ->method('insertOrderDeputy')
            ->with($orderId, $insertOrderDeputyCandidate)
            ->willReturn($stubResult2);

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

        $stubResult1 = $this->createStub(DeputyshipProcessingRawDbAccessResult::class);
        $stubResult1->action = DeputyshipCandidateAction::InsertOrder;
        $stubResult1->success = true;

        $stubResult2 = $this->createStub(DeputyshipProcessingRawDbAccessResult::class);
        $stubResult2->success = true;
        $stubResult2->data = 1;

        $this->mockDbAccess->expects($this->once())->method('insertOrder')->with($insertOrder)->willReturn($stubResult1);
        $this->mockDbAccess->expects($this->once())->method('findOrderId')->with($orderUid)->willReturn($stubResult2);

        // expect rollbacks as this is a dry run: once for the insert order, once for the transaction around the
        // candidates
        $this->mockDbAccess->expects($this->once())->method('rollback');

        // call
        $builderResult = $this->sut->convert($candidateGroup, true);

        // assert
        self::assertEquals(DeputyshipBuilderResultOutcome::CandidatesApplied, $builderResult->getOutcome());
    }

    // use a full set of candidates to test all the branches
    public function testConvertSuccess(): void
    {
        $orderUid = '1122334455';
        $orderId = 2;

        $insertOrderDeputy = ['action' => DeputyshipCandidateAction::InsertOrderDeputy];
        $insertOrderReport = ['action' => DeputyshipCandidateAction::InsertOrderReport];
        $updateOrderStatus = ['action' => DeputyshipCandidateAction::UpdateOrderStatus];
        $updateDeputyStatus = ['action' => DeputyshipCandidateAction::UpdateDeputyStatus];

        $findOrderResult = $this->createStubResult(DeputyshipCandidateAction::FindOrder, data: $orderId);
        $insertOrderDeputyResult = $this->createStubResult(DeputyshipCandidateAction::InsertOrderDeputy);
        $updateOrderStatusResult = $this->createStubResult(DeputyshipCandidateAction::UpdateOrderStatus);
        $updateDeputyStatusResult = $this->createStubResult(DeputyshipCandidateAction::UpdateDeputyStatus);

        $candidateGroup = new DeputyshipCandidatesGroup();
        $candidateGroup->orderUid = $orderUid;
        $candidateGroup->insertOthers = [$insertOrderDeputy, $insertOrderReport];
        $candidateGroup->updates = [$updateOrderStatus, $updateDeputyStatus];

        $this->mockDbAccess->expects($this->once())->method('findOrderId')->with($orderUid)->willReturn($findOrderResult);
        $this->mockDbAccess->expects($this->once())->method('insertOrderDeputy')->with($orderId, $insertOrderDeputy)->willReturn($insertOrderDeputyResult);
        $this->mockDbAccess->expects($this->once())->method('updateOrderStatus')->with($orderId, $updateOrderStatus)->willReturn($updateOrderStatusResult);
        $this->mockDbAccess->expects($this->once())->method('updateDeputyStatus')->with($orderId, $updateDeputyStatus)->willReturn($updateDeputyStatusResult);
        $this->mockDbAccess->expects($this->once())->method('endTransaction');

        // call
        $builderResult = $this->sut->convert($candidateGroup, false);

        // assert
        self::assertEquals(DeputyshipBuilderResultOutcome::CandidatesApplied, $builderResult->getOutcome());
    }

    private function createStubResult(
        DeputyshipCandidateAction $action,
        bool $success = true,
        mixed $data = null,
    ): DeputyshipProcessingRawDbAccessResult&Stub {
        $result = $this->createStub(DeputyshipProcessingRawDbAccessResult::class);
        $result->action = $action;
        $result->success = $success;
        $result->data = $data;

        return $result;
    }
}
