<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\Client;
use App\Entity\CourtOrder;
use App\Entity\CourtOrderDeputy;
use App\Entity\Deputy;
use App\Entity\Ndr\Ndr;
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
        ?Ndr $ndr = null,
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

        if (!is_null($ndr)) {
            $courtOrder->setNdr($ndr);
        }

        if (!is_null($deputy)) {
            $deputy->associateWithCourtOrder($courtOrder, $isActive);
        }

        $em->persist($courtOrder);
        $em->flush();

        return $courtOrder;
    }
}
