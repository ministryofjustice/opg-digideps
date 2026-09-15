<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\TestHelpers;

use OPG\Digideps\Common\CourtOrder\CourtOrderKind;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Entity\Report\Report;
use Doctrine\ORM\EntityManagerInterface;

class CourtOrderTestHelper
{
    public static function generateCourtOrder(
        EntityManagerInterface $em,
        Client $client,
        string $courtOrderUid,
        string $status = 'ACTIVE',
        CourtOrderType $type = CourtOrderType::PFA,
        ?Report $report = null,
        ?Deputy $deputy = null,
        bool $deputyIsActive = true,
        \DateTime $orderDate = (new \DateTime()),
        CourtOrderKind $courtOrderKind = CourtOrderKind::Single,
    ): CourtOrder {
        $courtOrder = new CourtOrder(
            $courtOrderUid,
            $type,
            $type === CourtOrderType::PFA || $courtOrderKind === CourtOrderKind::Hybrid ? CourtOrderReportType::OPG102 : CourtOrderReportType::OPG104,
            $courtOrderKind,
            $orderDate,
            $client,
            $status
        );

        if (!is_null($report)) {
            $courtOrder->addReport($report);
        }

        if (!is_null($deputy)) {
            $deputy->associateWithCourtOrder($courtOrder, $deputyIsActive);
            $em->persist($deputy);
        }

        $em->persist($courtOrder);

        $em->flush();

        return $courtOrder;
    }
}
