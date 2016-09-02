<?php

namespace AppBundle\Entity\Odr;

use Mockery as m;

class OdrTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Odr $report */
    private $odr;

    protected function setUp()
    {
        $this->odr = new Odr;
        $this->incomeTicked = new IncomeBenefit('t1', true);
        $this->incomeUnticked = new IncomeBenefit('t2', false);
    }

    public function tearDown()
    {
        m::close();
    }

    public function testCountRecordsPresent()
    {
        $this->assertEquals(0, $this->odr->countRecordsPresent(null));
        $this->assertEquals(0, $this->odr->countRecordsPresent([]));
        $this->assertEquals(0, $this->odr->countRecordsPresent([$this->incomeUnticked, $this->incomeUnticked]));
        $this->assertEquals(1, $this->odr->countRecordsPresent([$this->incomeTicked, $this->incomeUnticked]));
        $this->assertEquals(1, $this->odr->countRecordsPresent([$this->incomeTicked]));
    }


    public function testIncomeBenefitsStatus()
    {
        $this->assertEquals('not-started', $this->odr->incomeBenefitsStatus());

        $this->odr->setStateBenefits([$this->incomeTicked]);
        $this->assertEquals('incomplete', $this->odr->incomeBenefitsStatus(), 'state benefits and one-off should be ignored');

        $this->odr->setReceiveStatePension('yes');
        $this->odr->setReceiveOtherIncome('yes');
        $this->odr->setReceiveOtherIncomeDetails('..');
        $this->odr->setExpectCompensationDamages('yes');
        $this->odr->setExpectCompensationDamagesDetails('..');

        // state benefits and one-off should be ignored
        $this->odr->setStateBenefits([$this->incomeUnticked]);
        $this->odr->setOneOff([$this->incomeUnticked]);

        $this->assertEquals('done', $this->odr->incomeBenefitsStatus());
        
    }
}
