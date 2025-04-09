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
            ->setStatus('ACTIVE')
            ->setOrderMadeDate(new \DateTime('2020-06-14'));

        $deputy->associateWithCourtOrder($courtOrder);

        $actual = $deputy->getCourtOrdersWithStatus();
        $actualIsActive = $actual[0]['isActive'];
        $actualCourtOrder = $actual[0]['courtOrder'];

        $this->assertEquals(1, count($actual));
        $this->assertEquals(true, $actualIsActive);
        $this->assertEquals($fakeUid, $actualCourtOrder->getCourtOrderUid());
        $this->assertEquals('hybrid', $actualCourtOrder->getType());
    }
}
