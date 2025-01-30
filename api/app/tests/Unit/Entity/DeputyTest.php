<?php

namespace App\Tests\Unit\Entity;

use App\Entity\CourtOrder;
use App\TestHelpers\DeputyTestHelper;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class DeputyTest extends TestCase
{
    public function testGetCourtOrdersWithStatus()
    {
        $faker = Factory::create();
        $fakeUid = $faker->unique()->randomNumber(8);

        $deputyHelper = new DeputyTestHelper();
        $deputy = $deputyHelper->generateDeputy();
        $deputy->setLastname('MONK');

        $courtOrder = new CourtOrder();
        $courtOrder
            ->setCourtOrderUid($fakeUid)
            ->setType('hybrid')
            ->setActive(true);

        $deputy->associateWithCourtOrder($courtOrder);

        $actual = $deputy->getCourtOrdersWithStatus();
        $actualDischarged = $actual[0]['discharged'];
        $actualCourtOrder = $actual[0]['courtOrder'];

        $this->assertEquals(1, count($actual));
        $this->assertEquals(false, $actualDischarged);
        $this->assertEquals($fakeUid, $actualCourtOrder->getCourtOrderUid());
        $this->assertEquals('hybrid', $actualCourtOrder->getType());
    }
}
