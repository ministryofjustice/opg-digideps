<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Domain\CourtOrder\CourtOrderType;
use App\Entity\Client;
use App\Entity\CourtOrder;
use App\Entity\Deputy;
use App\Entity\Report\Report;
use Doctrine\ORM\EntityManagerInterface;

class CourtOrderTestHelper
{
    public static function generateCourtOrder(
        EntityManagerInterface $em,
        Client $client,
        string $courtOrderUid,
        string $status = 'ACTIVE',
        string $type = 'pfa',
        ?Report $report = null,
        ?Deputy $deputy = null,
        bool $deputyIsActive = true,
        \DateTime $orderDate = (new \DateTime()),
    ): CourtOrder {
        $courtOrder = (new CourtOrder())
            ->setCourtOrderUid($courtOrderUid)
            ->setClient($client)
            ->setOrderType(CourtOrderType::from($type))
            ->setStatus($status)
            ->setOrderMadeDate($orderDate);

        if (!is_null($report)) {
            $courtOrder->addReport($report);
        }

        if (!is_null($deputy)) {
            $deputy->associateWithCourtOrder($courtOrder, $deputyIsActive);
        }

        $em->persist($courtOrder);
        $em->flush();

        return $courtOrder;
    }
}
