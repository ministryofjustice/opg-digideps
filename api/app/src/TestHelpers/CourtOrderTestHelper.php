<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\TestHelpers;

use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Deputy;
use Doctrine\ORM\EntityManagerInterface;
use OPG\Digideps\Common\Report\ReportType;

class CourtOrderTestHelper
{
    public static function generateCourtOrder(
        EntityManagerInterface $em,
        Client $client,
        string $courtOrderUid,
        ReportType $reportType,
        string $status = 'ACTIVE',
        ?Deputy $deputy = null,
        bool $deputyIsActive = true,
        \DateTime $orderDate = (new \DateTime()),
    ): CourtOrder {
        $courtOrder = new CourtOrder(
            $courtOrderUid,
            $reportType->courtOrderType,
            $reportType->courtOrderReportType,
            $reportType->courtOrderKind,
            $orderDate,
            $client,
            $status
        );

        if (!is_null($deputy)) {
            $deputy->associateWithCourtOrder($courtOrder, $deputyIsActive);
            $em->persist($deputy);
        }

        $em->persist($courtOrder);

        $em->flush();

        return $courtOrder;
    }
}
