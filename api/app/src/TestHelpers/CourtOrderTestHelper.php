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
        bool $active,
        string $courtOrderUid,
        string $type = 'SINGLE',
        ?Deputy $deputy = null,
        bool $deputyDischarged = false,
    ): CourtOrder {
        $courtOrder = (new CourtOrder())
            ->setCourtOrderUid((int) $courtOrderUid)
            ->setClient($client)
            ->setType($type)
            ->setActive($active);

        if (!is_null($deputy)) {
            self::associateDeputyToCourtOrder($em, $courtOrder, $deputy, $deputyDischarged);
        }
        
        return $courtOrder;
    }
    
    public static function associateDeputyToCourtOrder(EntityManager $em, CourtOrder $courtOrder, Deputy $deputy, bool $deputyDischarged = false): void
    {
        $courtOrderDeputy = (new CourtOrderDeputy())
            ->setDeputy($deputy)
            ->setCourtOrder($courtOrder)
            ->setDischarged($deputyDischarged);

        $em->persist($courtOrderDeputy);
    }
}
