<?php

namespace Tests\AppBundle\v2\Transformer;

use AppBundle\v2\DTO\DeputyDto;
use AppBundle\v2\DTO\NamedDeputyDto;
use AppBundle\v2\DTO\OrganisationDto;
use AppBundle\v2\Transformer\DeputyTransformer;
use AppBundle\v2\Transformer\NamedDeputyTransformer;
use AppBundle\v2\Transformer\OrganisationTransformer;
use PHPUnit\Framework\TestCase;

class NamedDeputyTransformerTest extends TestCase
{
    /**
     * @test
     */
    public function transformsANamedDeputyDto()
    {
        $dto = (new NamedDeputyDto())
            ->setId(4)
            ->setDeputyNo('01234567')
            ->setFirstName('foo')
            ->setLastName('bar')
            ->setEmail1('foo1@org')
            ->setEmail2('foo@2org')
            ->setEmail3('foo@org')
            ->setDepAddrNo('54321')
            ->setPhoneMain('123')
            ->setPhoneAlterrnative('456')
            ->setAddress1('a1')
            ->setAddress2('a2')
            ->setAddress3('a3')
            ->setAddress4('a4')
            ->setAddress5('a5')
            ->setAddressPostcode('apc')
            ->setAddressCountry('bar');

        $transformed = (new NamedDeputyTransformer())->transform($dto);

        $this->assertEquals(4, $transformed['id']);
        $this->assertEquals('01234567', $transformed['deputy_no']);
        $this->assertEquals('foo', $transformed['firstname']);
        $this->assertEquals('bar', $transformed['lastname']);
        $this->assertEquals('foo1@org', $transformed['email1']);
        $this->assertEquals('foo2@org', $transformed['email2']);
        $this->assertEquals('foo3@org', $transformed['email3']);
        $this->assertEquals('54321', $transformed['deputy_addr_no']);
        $this->assertEquals('123', $transformed['phone_main']);
        $this->assertEquals('456', $transformed['phone_alternative']);
        $this->assertEquals('a1', $transformed['address1']);
        $this->assertEquals('a2', $transformed['address2']);
        $this->assertEquals('a3', $transformed['address3']);
        $this->assertEquals('a4', $transformed['address4']);
        $this->assertEquals('a5', $transformed['address5']);
        $this->assertEquals('apc', $transformed['address_postcode']);
        $this->assertEquals('bar', $transformed['address_country']);

    }
}
