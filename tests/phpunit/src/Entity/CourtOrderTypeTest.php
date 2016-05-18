<?php

namespace AppBundle\Entity;

use Mockery as m;

class CourtOrderTypeTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->cot = new CourtOrderType();
    }

    public function testgetReportIds()
    {
        $r1 = m::mock('AppBundle\Entity\Report')->shouldReceive('getId')->andReturn(1)->getMock();
        $r2 = m::mock('AppBundle\Entity\Report')->shouldReceive('getId')->andReturn(2)->getMock();

        $this->cot
            ->addReport($r1)
            ->addReport($r2);

        $this->assertEquals([1, 2],  $this->cot->getReportIds());
    }
}
