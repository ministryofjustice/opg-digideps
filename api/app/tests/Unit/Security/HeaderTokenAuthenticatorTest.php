<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Security;

use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Repository\UserRepository;
use OPG\Digideps\Backend\Security\HeaderTokenAuthenticator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;
use Tests\OPG\Digideps\Backend\Unit\PredisMock;

final class HeaderTokenAuthenticatorTest extends TestCase
{
    private PredisMock&MockObject $redisClient;
    private UserRepository&MockObject $userRepository;
    private LoggerInterface&MockObject $logger;
    private HeaderTokenAuthenticator $sut;

    public function setUp(): void
    {
        $this->redisClient = self::createMock(PredisMock::class);
        $this->userRepository = self::createMock(UserRepository::class);
        $this->logger = self::createMock(LoggerInterface::class);

        $this->sut = new HeaderTokenAuthenticator(
            $this->redisClient,
            $this->userRepository,
            $this->logger
        );
    }

    public function testSupports(): void
    {
        $supportedRequest = new Request();
        $supportedRequest->headers->set('AuthToken', 'AuthTokenValue');

        $unsupportedRequest = new Request();
        $unsupportedRequest->headers->set('AuthToken', '');

        self::assertEquals(true, $this->sut->supports($supportedRequest));
        self::assertEquals(false, $this->sut->supports($unsupportedRequest));
    }

    public function testAuthenticate(): void
    {
        $supportedRequest = new Request();
        $supportedRequest->headers->set('AuthToken', 'AuthTokenValue');

        $user = new User()->setEmail('a@b.com');
        $postAuthToken = new PostAuthenticationToken($user, 'a_firewall', ['ROLE_LAY_DEPUTY']);

        $this->userRepository->expects($this->once())->method('findOneBy')->with(['email' => 'a@b.com'])->willReturn($user);

        $this->redisClient->expects(self::once())->method('get')->with('AuthTokenValue')->willReturn(serialize($postAuthToken));

        $passport = $this->sut->authenticate($supportedRequest);

        self::assertEquals($passport->getUser(), $user);
    }

    public function testAuthenticateRedisKeyDoesNotExistThrowsError(): void
    {
        self::expectExceptionObject(new UserNotFoundException('User not found'));

        $supportedRequest = new Request();
        $supportedRequest->headers->set('AuthToken', 'AuthTokenValue');

        $this->redisClient->expects(self::once())->method('get')->with('AuthTokenValue')->willReturn(null);

        $this->sut->authenticate($supportedRequest);
    }

    public function testAuthenticateUserWithIdentifierInTokenDoesNotExistReturnsPassport(): void
    {
        $supportedRequest = new Request();
        $supportedRequest->headers->set('AuthToken', 'AuthTokenValue');

        $user = new User()
            ->setEmail('a@b.com');
        $postAuthToken = new PostAuthenticationToken($user, 'a_firewall', ['ROLE_LAY_DEPUTY']);

        $this->redisClient->expects(self::once())->method('get')->with('AuthTokenValue')->willReturn(serialize($postAuthToken));
        $this->userRepository->expects(self::never())->method('findOneBy');

        $passport = new SelfValidatingPassport(
            new UserBadge($postAuthToken->getUserIdentifier(), function ($userEmail): User {
                $user = $this->userRepository->findOneBy(['email' => strtolower($userEmail)]);

                if ($user instanceof User) {
                    return $user;
                }

                throw new UserNotFoundException('User not found');
            })
        );

        self::assertEquals($passport, $this->sut->authenticate($supportedRequest));
    }
}
