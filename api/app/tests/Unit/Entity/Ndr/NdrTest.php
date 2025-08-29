<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\Ndr;

use InvalidArgumentException;
use App\Entity\Client;
use App\Entity\Ndr\Ndr;
use MockeryStub as m;
use PHPUnit\Framework\TestCase;

final class NdrTest extends TestCase
{
    private Ndr $ndr;

    public function setUp(): void
    {
        $client = m::mock(Client::class);
        $this->ndr = new Ndr($client);
    }

    public function testInvalidAgreedBehalfOption(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->ndr->setAgreedBehalfDeputy('BAD_VALUE');
    }

    public function testValidAgreedBehalfOptions(): void
    {
        $values = ['only_deputy', 'more_deputies_behalf', 'more_deputies_not_behalf'];
        foreach ($values as $value) {
            $this->ndr->setAgreedBehalfDeputy($value);

            $this->assertEquals($this->ndr->getAgreedBehalfDeputy(), $value);
        }
    }
}
