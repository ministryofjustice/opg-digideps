<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\Client;
use App\Entity\CourtOrder;
use App\Entity\CourtOrderDeputy;
use App\Entity\Deputy;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class CourtOrderTestHelper
{
    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public static function generateCourtOrder(
        EntityManager $em,
        Client $client,
        string $status,
        string $courtOrderUid,
        string $type = 'SINGLE',
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

        if (!is_null($deputy)) {
            self::associateDeputyToCourtOrder($em, $courtOrder, $deputy, $isActive);
        }

        return $courtOrder;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
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
