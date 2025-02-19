<?php

namespace App\Tests\Integration\v2\Transformer;

use App\v2\DTO\OrganisationDto;
use App\v2\DTO\UserDto;
use App\v2\Transformer\OrganisationTransformer;
use App\v2\Transformer\UserTransformer;
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

        $userTransformer = $this
            ->getMockBuilder(UserTransformer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $transformed = (new OrganisationTransformer($userTransformer))->transform($dto, ['users', 'total_user_count', 'total_client_count']);

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
            ->setUsers([new UserDto(), new UserDto()])
            ->setTotalUserCount(2);

        $userTransformer = $this
            ->getMockBuilder(UserTransformer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userTransformer
            ->expects($this->exactly(2))
            ->method('transform')
            ->withConsecutive(
                [$this->isInstanceOf(UserDto::class), ['clients']],
                [$this->isInstanceOf(UserDto::class), ['clients']]
            )
            ->willReturnOnConsecutiveCalls(
                ['user_one' => 'transformed'],
                ['user_two' => 'transformed']
            );

        $transformed = (new OrganisationTransformer($userTransformer))->transform($dto, ['total_client_count', 'clients']);

        $this->assertEquals(4, $transformed['id']);
        $this->assertEquals('foo', $transformed['name']);
        $this->assertEquals('bar', $transformed['email_identifier']);
        $this->assertTrue($transformed['is_activated']);
        $this->assertCount(2, $transformed['users']);
        $this->assertEquals(2, $transformed['total_user_count']);
        $this->assertEquals(['user_one' => 'transformed'], $transformed['users'][0]);
        $this->assertEquals(['user_two' => 'transformed'], $transformed['users'][1]);
    }
}
