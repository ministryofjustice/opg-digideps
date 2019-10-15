<?php

namespace Tests\AppBundle\v2\Transformer;

use AppBundle\v2\DTO\DeputyDto;
use AppBundle\v2\DTO\OrganisationDto;
use AppBundle\v2\Transformer\DeputyTransformer;
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

        $deputyTransformer = $this
            ->getMockBuilder(DeputyTransformer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $transformed = (new OrganisationTransformer($deputyTransformer))->transform($dto, ['users']);

        $this->assertEquals(4, $transformed['id']);
        $this->assertEquals('foo', $transformed['name']);
        $this->assertEquals('bar', $transformed['email_identifier']);
        $this->assertTrue($transformed['is_activated']);
        $this->assertArrayNotHasKey('users', $transformed);
    }

    /**
     * @test
     */
    public function transformsAnOrganisationDtoWithUsersIfNotExcluded()
    {
        $dto = (new OrganisationDto())
            ->setId(4)
            ->setName('foo')
            ->setEmailIdentifier('bar')
            ->setIsActivated(true)
            ->setUsers([new DeputyDto(), new DeputyDto()]);

        $deputyTransformer = $this
            ->getMockBuilder(DeputyTransformer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $deputyTransformer
            ->expects($this->exactly(2))
            ->method('transform')
            ->withConsecutive(
                [$this->isInstanceOf(DeputyDto::class), ['clients']],
                [$this->isInstanceOf(DeputyDto::class), ['clients']]
            )
            ->willReturnOnConsecutiveCalls(
                ['user_one' => 'transformed'],
                ['user_two' => 'transformed']
            );

        $transformed = (new OrganisationTransformer($deputyTransformer))->transform($dto);

        $this->assertEquals(4, $transformed['id']);
        $this->assertEquals('foo', $transformed['name']);
        $this->assertEquals('bar', $transformed['email_identifier']);
        $this->assertTrue($transformed['is_activated']);
        $this->assertCount(2, $transformed['users']);
        $this->assertEquals(['user_one' => 'transformed'], $transformed['users'][0]);
        $this->assertEquals(['user_two' => 'transformed'], $transformed['users'][1]);
    }
}
