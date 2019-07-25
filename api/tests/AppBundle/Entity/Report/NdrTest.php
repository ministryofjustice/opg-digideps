<?php

namespace Tests\AppBundle\Entity\Report;

use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Ndr;
use MockeryStub as m;

class NdrTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Ndr
     */
    private $ndr;

    public function setUp()
    {
        $client = m::mock(Client::class);
        $this->ndr = new Ndr($client);
    }

    public function testInvalidAgreedBehalfOption()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $this->ndr->setAgreedBehalfDeputy('BAD_VALUE');
    }

    public function testValidAgreedBehalfOptions()
    {
        $values = ['only_deputy', 'more_deputies_behalf', 'more_deputies_not_behalf'];
        foreach ($values as $value) {
            $this->ndr->setAgreedBehalfDeputy($value);

            $this->assertEquals($this->ndr->getAgreedBehalfDeputy(), $value);
        }
    }
}
