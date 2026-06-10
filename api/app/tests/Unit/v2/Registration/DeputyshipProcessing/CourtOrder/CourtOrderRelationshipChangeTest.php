<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\v2\Registration\DeputyshipProcessing\CourtOrder;

use OPG\Digideps\Common\CourtOrder\CourtOrderKind;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CourtOrder\CourtOrderRelationshipChange;
use PHPUnit\Framework\TestCase;

class CourtOrderRelationshipChangeTest extends TestCase
{
    public function testHasSiblingIdChange()
    {
        $sibling = $this->createStub(CourtOrder::class);
        $sibling->method('getId')->willReturn(66);
        $courtOrder = $this->createStub(CourtOrder::class);
        $courtOrder->method('getId')->willReturn(1);
        $courtOrder->method('getOrderKind')->willReturn(CourtOrderKind::Dual);
        $courtOrder->method('getSibling')->willReturn($sibling);

        $change = new CourtOrderRelationshipChange($courtOrder->getId(), $courtOrder->getOrderKind(), $courtOrder->getSibling()?->getId(), CourtOrderKind::Dual, 99);
        $this->assertTrue($change->hasSiblingIdChange());

        $change = new CourtOrderRelationshipChange($courtOrder->getId(), $courtOrder->getOrderKind(), $courtOrder->getSibling()?->getId(), CourtOrderKind::Hybrid, 99);
        $this->assertTrue($change->hasSiblingIdChange());

        $change = new CourtOrderRelationshipChange($courtOrder->getId(), $courtOrder->getOrderKind(), $courtOrder->getSibling()?->getId(), CourtOrderKind::Dual, 66);
        $this->assertFalse($change->hasSiblingIdChange());

        $change = new CourtOrderRelationshipChange($courtOrder->getId(), $courtOrder->getOrderKind(), $courtOrder->getSibling()?->getId(), CourtOrderKind::Single, null);
        $this->assertTrue($change->hasSiblingIdChange());

        $change = new CourtOrderRelationshipChange($courtOrder->getId(), $courtOrder->getOrderKind(), $courtOrder->getSibling()?->getId(), CourtOrderKind::Hybrid, 66);
        $this->assertFalse($change->hasSiblingIdChange());
    }

    public function testHasKindChange()
    {
        $courtOrder = $this->createStub(CourtOrder::class);
        $courtOrder->method('getId')->willReturn(1);
        $courtOrder->method('getOrderKind')->willReturn(CourtOrderKind::Hybrid);
        $courtOrder->method('getSibling')->willReturn(null);

        $change = new CourtOrderRelationshipChange($courtOrder->getId(), $courtOrder->getOrderKind(), $courtOrder->getSibling()?->getId(), CourtOrderKind::Dual, 99);
        $this->assertTrue($change->hasKindChange());

        $change = new CourtOrderRelationshipChange($courtOrder->getId(), $courtOrder->getOrderKind(), $courtOrder->getSibling()?->getId(), CourtOrderKind::Hybrid, 99);
        $this->assertFalse($change->hasKindChange());

        $change = new CourtOrderRelationshipChange($courtOrder->getId(), $courtOrder->getOrderKind(), $courtOrder->getSibling()?->getId(), CourtOrderKind::Dual, 66);
        $this->assertTrue($change->hasKindChange());

        $change = new CourtOrderRelationshipChange($courtOrder->getId(), $courtOrder->getOrderKind(), $courtOrder->getSibling()?->getId(), CourtOrderKind::Single, null);
        $this->assertTrue($change->hasKindChange());

        $change = new CourtOrderRelationshipChange($courtOrder->getId(), $courtOrder->getOrderKind(), $courtOrder->getSibling()?->getId(), CourtOrderKind::Hybrid, 66);
        $this->assertFalse($change->hasKindChange());
    }
}
