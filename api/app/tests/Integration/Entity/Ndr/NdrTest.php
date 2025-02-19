<?php

namespace App\Tests\Integration\Entity\Ndr;

use App\Entity\Client;
use App\Entity\Ndr\Ndr;
use MockeryStub as m;
use PHPUnit\Framework\TestCase;

class NdrTest extends TestCase
{
    /**
     * @var Ndr
     */
    private $ndr;

    public function setUp(): void
    {
        $client = m::mock(Client::class);
        $this->ndr = new Ndr($client);
    }

    public function testInvalidAgreedBehalfOption()
    {
        $this->expectException(\InvalidArgumentException::class);
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
