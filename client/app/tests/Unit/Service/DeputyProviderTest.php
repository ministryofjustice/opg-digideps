<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service;

use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use OPG\Digideps\Frontend\Service\DeputyProvider;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class DeputyProviderTest extends TestCase
{
    private DeputyProvider $object;
    private MockObject&RestClient $restClient;
    private MockObject&LoggerInterface $logger;

    public function setUp(): void
    {
        $this->restClient = $this->createMock(RestClient::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->object = new DeputyProvider($this->restClient, $this->logger);
    }

    public function testLogin(): void
    {
        $credentials = ['email' => 'Peter', 'password' => 'p'];

        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $authToken = 'abc123';

        $this->restClient->expects($this->once())->method('login')->with($credentials)->willReturn([$user, $authToken]);
        $this->restClient->expects($this->once())->method('setLoggedUserId')->with(1);

        $this->logger->method('info')->willReturnCallback(function ($e) {
            throw new \Exception($e);
        });

        $this->object->login($credentials);
    }

    public function testLoginFail(): void
    {
        $this->expectException(UserNotFoundException::class);

        $credentials = ['email' => 'Peter', 'password' => 'p'];

        $this->restClient->expects($this->once())->method('login')->with($credentials)->willThrowException(new \Exception('e'));
        $this->logger->expects($this->once())->method('info');

        $this->object->login($credentials);
    }

    public function testLoadUserByIdentifier(): void
    {
        $mockUser = $this->createMock(User::class);

        $this->restClient->method('setLoggedUserId')->with(1)->willReturn($this->restClient);
        $this->restClient->method('get')->with('user/1', 'User', new IsType(IsType::TYPE_ARRAY))->willReturn($mockUser);

        $this->assertEquals($mockUser, $this->object->loadUserByIdentifier('1'));
    }

    public function testSupportsClass(): void
    {
        $this->assertTrue($this->object->supportsClass(User::class));
        $this->assertFalse($this->object->supportsClass(Report::class));
    }
}
