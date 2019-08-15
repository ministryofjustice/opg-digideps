<?php

namespace Tests\AppBundle\v2\Transformer;

use AppBundle\v2\DTO\OrganisationDto;
use AppBundle\v2\Transformer\OrganisationTransformer;
use PHPUnit\Framework\TestCase;

class OrganisationTransformerTest extends TestCase
{
    /**
     * @test
     */
    public function transformsAnOrganisationDto()
    {
        $dto = (new OrganisationDto())
            ->setId(4)
            ->setName('foo')
            ->setEmailIdentifier('bar')
            ->setIsActivated(true);

        $transformed = (new OrganisationTransformer())->transform($dto);

        $this->assertEquals(4, $transformed['id']);
        $this->assertEquals('foo', $transformed['name']);
        $this->assertEquals('bar', $transformed['email_identifier']);
        $this->assertTrue($transformed['is_activated']);
    }
}
