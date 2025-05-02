<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\CourtOrder;
use App\Entity\CourtOrderDeputy;
use Faker\Factory;

class CourtOrderTestHelper
{
    public function generateCourtOrder($orderType, $client, $reportStartDate, $status = 'ACTIVE'): CourtOrder
    {
        $faker = Factory::create('en_GB');

        return (new CourtOrder())
            ->setCourtOrderUid('70000000'.$faker->randomNumber(4))
            ->setOrderType($orderType)
            ->setStatus($status)
            ->setCreatedAt(new \DateTime('now'))
            ->setUpdatedAt(new \DateTime('now'))
            ->setClient($client)
            ->setOrderMadeDate($reportStartDate);
    }

    public function linkCourtOrderToDeputy($courtOrder, $deputy, $isActive = true): CourtOrderDeputy
    {
        return (new CourtOrderDeputy())
            ->setCourtOrder($courtOrder)
            ->setDeputy($deputy)
            ->setIsActive($isActive);
    }
}
