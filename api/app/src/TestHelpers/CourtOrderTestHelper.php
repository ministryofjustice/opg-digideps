<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\Client;
use App\Entity\CourtOrder;
use App\Entity\CourtOrderDeputy;
use App\Entity\Deputy;
use App\Entity\Report\Report;
use Doctrine\ORM\EntityManager;

class CourtOrderTestHelper
{
    public static function generateCourtOrder(
        EntityManager $em,
        Client $client,
        string $courtOrderUid,
        string $status = 'ACTIVE',
        string $type = 'SINGLE',
        ?Report $report = null,
        ?Deputy $deputy = null,
        bool $isActive = true,
        \DateTime $orderDate = (new \DateTime()),
    ): CourtOrder {
        /** @var CourtOrder $courtOrder */
        $courtOrder = (new CourtOrder())
            ->setCourtOrderUid($courtOrderUid)
            ->setClient($client)
            ->setOrderType($type)
            ->setStatus($status)
            ->setOrderMadeDate($orderDate);

        if (!is_null($report)) {
            $courtOrder->addReport($report);
        }

        $em->persist($courtOrder);
        $em->flush();

        if (!is_null($deputy)) {
            self::associateDeputyToCourtOrder($em, $courtOrder, $deputy, $isActive);
        }

        return $courtOrder;
    }

    public static function associateDeputyToCourtOrder(
        EntityManager $em,
        CourtOrder $courtOrder,
        Deputy $deputy,
        bool $isActive = true,
    ): CourtOrderDeputy {
        /** @var CourtOrderDeputy $courtOrderDeputy */
        $courtOrderDeputy = (new CourtOrderDeputy())
            ->setDeputy($deputy)
            ->setCourtOrder($courtOrder)
            ->setIsActive($isActive);

        $em->persist($courtOrderDeputy);
        $em->flush();

        return $courtOrderDeputy;
    }
}
