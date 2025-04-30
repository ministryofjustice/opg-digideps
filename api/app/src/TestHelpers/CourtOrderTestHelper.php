<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\Client;
use App\Entity\CourtOrder;
use App\Entity\CourtOrderDeputy;
use App\Entity\Deputy;
use Doctrine\ORM\EntityManager;

class CourtOrderTestHelper
{
    public static function generateCourtOrder(
        EntityManager $em, 
        Client $client,
        string $status,
        string $courtOrderUid,
        string $type = 'SINGLE',
        ?Deputy $deputy = null,
        bool $deputyDischarged = false,
    ): CourtOrder {
        /** @var CourtOrder $courtOrder */
        $courtOrder = (new CourtOrder())
            ->setCourtOrderUid($courtOrderUid)
            ->setClient($client)
            ->setOrderType($type)
            ->setStatus($status);

        if (!is_null($deputy)) {
            self::associateDeputyToCourtOrder($em, $courtOrder, $deputy, $deputyDischarged);
        }
        
        return $courtOrder;
    }
    
    public static function associateDeputyToCourtOrder(EntityManager $em, CourtOrder $courtOrder, Deputy $deputy, bool $deputyDischarged = false): void
    {   /** @var CourtOrderDeputy $courtOrderDeputy */
        $courtOrderDeputy = (new CourtOrderDeputy())
            ->setDeputy($deputy)
            ->setCourtOrder($courtOrder)
            ->setDischarged($deputyDischarged);

        $em->persist($courtOrderDeputy);
    }
}
