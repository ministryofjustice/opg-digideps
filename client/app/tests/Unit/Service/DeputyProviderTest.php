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
    private RestClient&MockObject $restClient;
    private LoggerInterface&MockObject $logger;
    private DeputyProvider $sut;

    public function setUp(): void
    {
        $this->restClient = self::createMock(RestClient::class);
        $this->logger = self::createMock(LoggerInterface::class);

        $this->sut = new DeputyProvider($this->restClient, $this->logger);
    }

    public function testLogin(): void
    {
        $credentials = ['email' => 'Peter', 'password' => 'p'];

        $user = self::createMock(User::class);
        $user->method('getId')->willReturn(1);

        $this->restClient->expects(self::once())
            ->method('login')
            ->with($credentials)
            ->willReturn([$user, 'abc123']);

        $this->restClient->expects(self::once())->method('setLoggedUserId')->with(1);

        $this->logger->expects(self::never())->method('info');

        $this->sut->login($credentials);
    }

    public function testLoginFail(): void
    {
        self::expectException(UserNotFoundException::class);

        $credentials = ['email' => 'Peter', 'password' => 'p'];

        $this->restClient->expects(self::once())
            ->method('login')
            ->with($credentials)
            ->willThrowException(new \Exception('e'));

        $this->restClient->expects(self::never())->method('setLoggedUserId');

        $this->logger->expects(self::once())->method('info');

        $this->sut->login($credentials);
    }

    public function testLoadUserByIdentifier(): void
    {
        $mockUser = self::createMock(User::class);

        $this->restClient->method('setLoggedUserId')->with(1)->willReturn($this->restClient);
        $this->restClient->method('get')->with('user/1', 'User', new IsType(IsType::TYPE_ARRAY))->willReturn($mockUser);

        self::assertEquals($mockUser, $this->sut->loadUserByIdentifier('1'));
    }

    public function testSupportsClass(): void
    {
        self::assertTrue($this->sut->supportsClass(User::class));
        self::assertFalse($this->sut->supportsClass(Report::class));
    }
}
