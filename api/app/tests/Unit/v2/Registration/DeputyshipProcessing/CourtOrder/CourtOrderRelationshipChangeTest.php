<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\DeputyshipProcessing\CourtOrder;

use App\Domain\CourtOrder\CourtOrderKind;
use App\Entity\CourtOrder;
use App\v2\Registration\DeputyshipProcessing\CourtOrder\CourtOrderRelationshipChange;
use PHPUnit\Framework\TestCase;

class CourtOrderRelationshipChangeTest extends TestCase
{
    public function testHasSiblingIdChange()
    {
        $sibling = $this->createStub(CourtOrder::class);
        $sibling->method('getId')->willReturn(66);
        $courtOrder = $this->createStub(CourtOrder::class);
        $courtOrder->method('getSibling')->willReturn($sibling);
        $change = new CourtOrderRelationshipChange($courtOrder, CourtOrderKind::Dual, 99);
        $this->assertTrue($change->hasSiblingIdChange());
        $change = new CourtOrderRelationshipChange($courtOrder, CourtOrderKind::Hybrid, 99);
        $this->assertTrue($change->hasSiblingIdChange());
        $change = new CourtOrderRelationshipChange($courtOrder, CourtOrderKind::Dual, 66);
        $this->assertFalse($change->hasSiblingIdChange());
        $change = new CourtOrderRelationshipChange($courtOrder, CourtOrderKind::Single, null);
        $this->assertTrue($change->hasSiblingIdChange());
        $change = new CourtOrderRelationshipChange($courtOrder, CourtOrderKind::Hybrid, 66);
        $this->assertFalse($change->hasSiblingIdChange());
    }

    public function testHasKindChange()
    {
        $courtOrder = $this->createStub(CourtOrder::class);
        $courtOrder->method('getOrderKind')->willReturn(CourtOrderKind::Hybrid);
        $change = new CourtOrderRelationshipChange($courtOrder, CourtOrderKind::Dual, 99);
        $this->assertTrue($change->hasKindChange());
        $change = new CourtOrderRelationshipChange($courtOrder, CourtOrderKind::Hybrid, 99);
        $this->assertFalse($change->hasKindChange());
        $change = new CourtOrderRelationshipChange($courtOrder, CourtOrderKind::Dual, 66);
        $this->assertTrue($change->hasKindChange());
        $change = new CourtOrderRelationshipChange($courtOrder, CourtOrderKind::Single, null);
        $this->assertTrue($change->hasKindChange());
        $change = new CourtOrderRelationshipChange($courtOrder, CourtOrderKind::Hybrid, 66);
        $this->assertFalse($change->hasKindChange());
    }
}
