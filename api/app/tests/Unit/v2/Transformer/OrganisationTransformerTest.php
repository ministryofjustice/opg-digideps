<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Transformer;

use PHPUnit\Framework\Attributes\Test;
use App\v2\DTO\OrganisationDto;
use App\v2\DTO\UserDto;
use App\v2\Transformer\ClientTransformer;
use App\v2\Transformer\OrganisationTransformer;
use App\v2\Transformer\UserTransformer;
use PHPUnit\Framework\TestCase;

final class OrganisationTransformerTest extends TestCase
{
    #[Test]
    public function transformsAnOrganisationDto(): void
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

        $clientTransformer = $this->createMock(ClientTransformer::class);

        $transformed = (new OrganisationTransformer($userTransformer, $clientTransformer))
            ->transform($dto, ['users', 'total_user_count', 'total_client_count']);

        $this->assertEquals(4, $transformed['id']);
        $this->assertEquals('foo', $transformed['name']);
        $this->assertEquals('bar', $transformed['email_identifier']);
        $this->assertTrue($transformed['is_activated']);
        $this->assertArrayNotHasKey('users', $transformed);
    }

    #[Test]
    public function transformsAnOrganisationDtoWithUsersIfNotExcluded(): void
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
            ->with($this->isInstanceOf(UserDto::class), ['clients'])
            ->willReturnOnConsecutiveCalls(
                ['user_one' => 'transformed'],
                ['user_two' => 'transformed']
            );

        $clientTransformer = $this->createMock(ClientTransformer::class);

        $transformed = (new OrganisationTransformer($userTransformer, $clientTransformer))
            ->transform($dto, ['total_client_count', 'clients']);

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
