<?php

declare(strict_types=1);

namespace App\Tests\Integration\v2\Registration\DeputyshipProcessing\CourtOrder;

use App\Domain\CourtOrder\CourtOrderKind;
use App\Domain\CourtOrder\CourtOrderType;
use App\Entity\CourtOrder;
use App\Entity\StagingDeputyship;
use App\Tests\Integration\ApiIntegrationTestCase;
use App\v2\Registration\DeputyshipProcessing\CourtOrder\CourtOrderRelationshipIngester;
use App\v2\Registration\DeputyshipProcessing\CourtOrder\CourtOrderRelationshipReader;
use App\v2\Registration\DeputyshipProcessing\CourtOrder\CourtOrderRelationshipResult;
use App\v2\Registration\DeputyshipProcessing\Report\ReportReassembler;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class CourtOrderRelationshipIngesterTest extends ApiIntegrationTestCase
{
    private int $oldGeneratorType;
    private AbstractIdGenerator $oldGenerator;

    private function persistCourtOrder(int $id, CourtOrderKind $kind, ?int $siblingId = null, bool $active = true, ?bool $activeSibling = null, ?CourtOrderType $orderType = null): void
    {
        $courtOrder = new CourtOrder();
        $courtOrder->setId($id);
        $courtOrder->setCourtOrderUid("UID-{$id}");
        $courtOrder->setOrderKind($kind);
        $courtOrder->setOrderType($orderType ?? CourtOrderType::PFA);
        $courtOrder->setStatus($active ? 'ACTIVE' : 'CLOSED');
        $courtOrder->setOrderMadeDate(new \DateTime());
        if ($siblingId === null && $kind === CourtOrderKind::Single) {
            $courtOrder->setSibling(null);
        } elseif ($siblingId !== null && $kind !== CourtOrderKind::Single) {
            $sibling = new CourtOrder();
            $sibling->setId($siblingId);
            $sibling->setCourtOrderUid("UID-{$siblingId}");
            $sibling->setOrderKind($kind);
            $sibling->setOrderType($courtOrder->getOrderType() === CourtOrderType::HW ? CourtOrderType::PFA : CourtOrderType::HW);
            $sibling->setSibling($courtOrder);
            $sibling->setStatus($activeSibling === null && $active || $activeSibling ? 'ACTIVE' : 'CLOSED');
            $sibling->setOrderMadeDate(new \DateTime());
            $courtOrder->setSibling($sibling);
            self::$entityManager->persist($sibling);
        } else {
            throw new \LogicException();
        }
        self::$entityManager->persist($courtOrder);
    }

    /**
     * @param array<string> $deputyUids
     * @param array<string> $siblingDeputyUids
     */
    private function persistDeputyship(string $caseNumber, int $orderId, array $deputyUids, ?int $siblingId = null, ?array $siblingDeputyUids = null, bool $active = true, ?CourtOrderType $orderType = null): void
    {
        if (empty($deputyUids)) {
            throw new \LogicException();
        }
        $orderType ??= CourtOrderType::PFA;
        foreach ($deputyUids as $deputyUid) {
            $deputyship = new StagingDeputyship();
            $deputyship->orderUid = "UID-{$orderId}";
            $deputyship->clientUid = "UID-{$caseNumber}";
            $deputyship->caseNumber = $caseNumber;
            $deputyship->orderType = $orderType->value;
            $deputyship->orderStatus = $active ? 'ACTIVE' : 'CLOSED';
            $deputyship->deputyStatusOnOrder = $deputyship->orderStatus;
            $deputyship->deputyUid = $deputyUid;
            self::$entityManager->persist($deputyship);
        }
        if ($siblingId !== null && !empty($siblingDeputyUids)) {
            foreach ($siblingDeputyUids as $deputyUid) {
                $sibling = new StagingDeputyship();
                $sibling->orderUid = "UID-{$siblingId}";
                $sibling->clientUid = "UID-{$caseNumber}";
                $sibling->caseNumber = $caseNumber;
                $sibling->orderType = ($orderType === CourtOrderType::PFA ? CourtOrderType::HW : CourtOrderType::PFA)->value;
                $sibling->orderStatus = $active ? 'ACTIVE' : 'CLOSED';
                $sibling->deputyStatusOnOrder = $sibling->orderStatus;
                $sibling->deputyUid = $deputyUid;
                self::$entityManager->persist($sibling);
            }
        }
    }

    public function setUp(): void
    {
        $metadata = self::$entityManager->getClassMetaData(CourtOrder::class);
        $this->oldGeneratorType = $metadata->generatorType;
        $this->oldGenerator = $metadata->idGenerator;

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());
    }

    public function tearDown(): void
    {
        $metadata = self::$entityManager->getClassMetaData(CourtOrder::class);
        $metadata->setIdGeneratorType($this->oldGeneratorType);
        $metadata->setIdGenerator($this->oldGenerator);
    }

    public function testExecute()
    {
        //Unchanged Single
        $this->persistCourtOrder(1, CourtOrderKind::Single);
        $this->persistDeputyship("#1", 1, ["D1"]);

        //Unchanged Hybrid
        $this->persistCourtOrder(2, CourtOrderKind::Hybrid, 102);
        $this->persistDeputyship("#2", 2, ["D2", "D3"], 102, ["D2", "D3"]);

        //Unchanged Dual
        $this->persistCourtOrder(3, CourtOrderKind::Dual, 103);
        $this->persistDeputyship("#3", 3, ["D4", "D5", "D6"], 103, ["D4", "D5"]);

        //Deactivated Single
        $this->persistCourtOrder(4, CourtOrderKind::Single, active: false);
        $this->persistDeputyship("#4", 4, ["D1"], active: false);

        //Deactivated Hybrid
        $this->persistCourtOrder(5, CourtOrderKind::Hybrid, 105, active: false);
        $this->persistDeputyship("#5", 5, ["D2", "D3"], 105, ["D2", "D3"], active: false);

        //Deactivated Dual
        $this->persistCourtOrder(6, CourtOrderKind::Hybrid, 106, active: false);
        $this->persistDeputyship("#6", 6, ["D4", "D5", "D6"], 106, ["D4", "D5"], active: false);

        //Single to Hybrid
        $this->persistCourtOrder(7, CourtOrderKind::Single);
        $this->persistCourtOrder(207, CourtOrderKind::Single, orderType: CourtOrderType::HW);
        $this->persistDeputyship("#7", 7, ["D4", "D5"], 207, ["D4", "D5"]);

        //Single to Dual
        $this->persistCourtOrder(8, CourtOrderKind::Single);
        $this->persistCourtOrder(208, CourtOrderKind::Single, orderType: CourtOrderType::HW);
        $this->persistDeputyship("#8", 8, ["D4", "D5", "D6"], 208, ["D4", "D5"]);

        //Hybrid to Single
        $this->persistCourtOrder(9, CourtOrderKind::Hybrid, 109, activeSibling: false);
        $this->persistDeputyship("#9", 9, ["D4", "D5"]);
        $this->persistDeputyship("#9", 109, ["D4", "D5"], active: false, orderType: CourtOrderType::HW);

        //Hybrid to Dual
        $this->persistCourtOrder(10, CourtOrderKind::Hybrid, 110, activeSibling: false);
        $this->persistCourtOrder(210, CourtOrderKind::Single, orderType: CourtOrderType::HW);
        $this->persistDeputyship("#10", 110, ["D4", "D5"], active: false);
        $this->persistDeputyship("#10", 10, ["D4", "D5", "D6"], 210, ["D4", "D5"]);

        //Hybrid partial change
        $this->persistCourtOrder(11, CourtOrderKind::Hybrid, 111, activeSibling: false);
        $this->persistCourtOrder(211, CourtOrderKind::Single, orderType: CourtOrderType::HW);
        $this->persistDeputyship("#11", 111, ["D4", "D5"], active: false, orderType: CourtOrderType::HW);
        $this->persistDeputyship("#11", 11, ["D4", "D5"], 211, ["D4", "D5"]);

        //Dual to Single
        $this->persistCourtOrder(12, CourtOrderKind::Dual, 112, activeSibling: false);
        $this->persistDeputyship("#12", 12, ["D4", "D5", "D6"]);
        $this->persistDeputyship("#12", 112, ["D4", "D5"], active: false, orderType: CourtOrderType::HW);

        //Dual to Hybrid
        $this->persistCourtOrder(13, CourtOrderKind::Dual, 113, activeSibling: false);
        $this->persistCourtOrder(213, CourtOrderKind::Single, orderType: CourtOrderType::HW);
        $this->persistDeputyship("#13", 113, ["D4", "D5"], active: false);
        $this->persistDeputyship("#13", 13, ["D4", "D5"], 213, ["D4", "D5"]);

        //Dual partial change
        $this->persistCourtOrder(14, CourtOrderKind::Dual, 114, activeSibling: false);
        $this->persistCourtOrder(214, CourtOrderKind::Single, orderType: CourtOrderType::HW);
        $this->persistDeputyship("#14", 114, ["D4", "D5", "D7"], active: false, orderType: CourtOrderType::HW);
        $this->persistDeputyship("#14", 14, ["D4", "D5"], 214, ["D4", "D5","D6"]);

        self::$entityManager->flush();

        $ingester = new CourtOrderRelationshipIngester(
            new CourtOrderRelationshipReader(self::$entityManager->getConnection()),
            new ReportReassembler(),
            self::$entityManager
        );
        $results = $ingester->execute();
        usort($results, fn(CourtOrderRelationshipResult $left, CourtOrderRelationshipResult $right) => $left->getMessage() <=> $right->getMessage());

        $this->assertCount(14, $results);
        $this->assertSame("Changes in CourtOrder 10: SiblingId changed from '110' -> '210'. Kind changed from 'hybrid' -> 'dual'.", $results[0]->getMessage());
        $this->assertSame("Changes in CourtOrder 11: SiblingId changed from '111' -> '211'.", $results[1]->getMessage());
        $this->assertSame("Changes in CourtOrder 12: SiblingId changed from '112' -> ''. Kind changed from 'dual' -> 'single'.", $results[2]->getMessage());
        $this->assertSame("Changes in CourtOrder 13: SiblingId changed from '113' -> '213'. Kind changed from 'dual' -> 'hybrid'.", $results[3]->getMessage());
        $this->assertSame("Changes in CourtOrder 14: SiblingId changed from '114' -> '214'.", $results[4]->getMessage());
        $this->assertSame("Changes in CourtOrder 207: SiblingId changed from '' -> '7'. Kind changed from 'single' -> 'hybrid'.", $results[5]->getMessage());
        $this->assertSame("Changes in CourtOrder 208: SiblingId changed from '' -> '8'. Kind changed from 'single' -> 'dual'.", $results[6]->getMessage());
        $this->assertSame("Changes in CourtOrder 210: SiblingId changed from '' -> '10'. Kind changed from 'single' -> 'dual'.", $results[7]->getMessage());
        $this->assertSame("Changes in CourtOrder 211: SiblingId changed from '' -> '11'. Kind changed from 'single' -> 'hybrid'.", $results[8]->getMessage());
        $this->assertSame("Changes in CourtOrder 213: SiblingId changed from '' -> '13'. Kind changed from 'single' -> 'hybrid'.", $results[9]->getMessage());
        $this->assertSame("Changes in CourtOrder 214: SiblingId changed from '' -> '14'. Kind changed from 'single' -> 'dual'.", $results[10]->getMessage());
        $this->assertSame("Changes in CourtOrder 7: SiblingId changed from '' -> '207'. Kind changed from 'single' -> 'hybrid'.", $results[11]->getMessage());
        $this->assertSame("Changes in CourtOrder 8: SiblingId changed from '' -> '208'. Kind changed from 'single' -> 'dual'.", $results[12]->getMessage());
        $this->assertSame("Changes in CourtOrder 9: SiblingId changed from '109' -> ''. Kind changed from 'hybrid' -> 'single'.", $results[13]->getMessage());

        $closed = self::$entityManager->getRepository(CourtOrder::class)->findBy(['status' => 'CLOSED']);
        $this->assertTrue(array_all($closed, fn(CourtOrder $order) => $order->getSibling() === null));
    }
}
