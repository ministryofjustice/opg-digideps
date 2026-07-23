<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Security;

use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Exception\InvalidRegistrationTokenException;
use OPG\Digideps\Backend\Exception\UnauthorisedException;
use OPG\Digideps\Backend\Exception\UserWrongCredentialsManyAttempts;
use OPG\Digideps\Backend\Repository\UserRepository;
use OPG\Digideps\Backend\Security\RegistrationTokenAuthenticator;
use OPG\Digideps\Backend\Service\Auth\AuthService;
use OPG\Digideps\Backend\Service\BruteForce\AttemptsIncrementalWaitingChecker;
use OPG\Digideps\Backend\Service\BruteForce\AttemptsInTimeChecker;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class RegistrationTokenAuthenticatorTest extends TestCase
{
    private UserRepository&Stub $userRepo;
    private TokenStorageInterface&MockObject $tokenStorage;
    private AuthService&MockObject $authService;
    private AttemptsInTimeChecker&MockObject $attemptsInTimeChecker;
    private AttemptsIncrementalWaitingChecker&MockObject $incrementalWaitingTimeChecker;
    private RegistrationTokenAuthenticator $sut;

    public function setUp(): void
    {
        $this->userRepo = self::createStub(UserRepository::class);
        $this->tokenStorage = self::createMock(TokenStorageInterface::class);
        $this->authService = self::createMock(AuthService::class);
        $this->attemptsInTimeChecker = self::createMock(AttemptsInTimeChecker::class);
        $this->incrementalWaitingTimeChecker = self::createMock(AttemptsIncrementalWaitingChecker::class);
        $verboseLogger = self::createMock(LoggerInterface::class);

        $this->sut = new RegistrationTokenAuthenticator(
            $this->userRepo,
            $this->tokenStorage,
            $this->authService,
            $this->attemptsInTimeChecker,
            $this->incrementalWaitingTimeChecker,
            $verboseLogger,
        );
    }

    #[DataProvider('loginRouteRequestProvider')]
    #[Test]
    public function supportsLoginRoute(Request $request, bool $expectedIsSupported): void
    {
        self::assertEquals($expectedIsSupported, $this->sut->supports($request));
    }

    public static function loginRouteRequestProvider(): array
    {
        return [
            'Valid request - login route' => [
                Request::create(
                    '/auth/login',
                    'POST',
                    [],
                    [],
                    [],
                    [],
                    json_encode(['token' => 'a-token'])
                ),
                true,
            ],
            'Valid uri and method, invalid body' => [
                Request::create(
                    '/auth/login',
                    'POST',
                    [],
                    [],
                    [],
                    [],
                    json_encode(['not-a' => 'token'])
                ),
                false,
            ],
            'Valid uri and method, empty body' => [
                Request::create('/auth/login', 'POST'), false, ],
            'Valid uri, invalid method' => [Request::create('/auth/login', 'GET'), false],
            'Invalid uri, valid method' => [Request::create('/auth/logout', 'POST'), false],
            'Invalid values' => [Request::create('/auth/logout', 'DELETE'), false],
        ];
    }

    #[Test]
    public function supportsFirstPasswordRoute(): void
    {
        $request = Request::create(
            'user/1/set-password',
            'PUT',
            [],
            [],
            [],
            [],
            json_encode(['token' => 'a-token', 'password' => 'abc'])
        );

        $this->userRepo->method('findOneBy')->willReturn(new User('', '', '')->setId(1));

        self::assertEquals(true, $this->sut->supports($request));
    }

    #[DataProvider('setFirstPasswordRouteRequestProvider')]
    #[Test]
    public function supportsFirstPasswordRouteFailures(Request $request): void
    {
        $this->userRepo->method('findOneBy')->willReturn(new User('', '', '')->setId(1));

        self::assertEquals(false, $this->sut->supports($request));
    }

    public static function setFirstPasswordRouteRequestProvider(): array
    {
        return [
            'Valid uri, valid method, missing token from body' => [
                Request::create(
                    'user/1/set-password',
                    'PUT',
                    [],
                    [],
                    [],
                    [],
                    json_encode(['password' => 'abc'])
                ),
            ],
            'Valid uri, valid method, missing password from body' => [
                Request::create(
                    'user/1/set-password',
                    'PUT',
                    [],
                    [],
                    [],
                    [],
                    json_encode(['token' => 'a-token'])
                ),
            ],
            'Valid uri, valid method, empty body' => [
                Request::create('user/1/set-password', 'PUT'),
            ],
            'Valid request, invalid method' => [
                Request::create(
                    'user/1/set-password',
                    'POST',
                    [],
                    [],
                    [],
                    [],
                    json_encode(['token' => 'a-token', 'password' => 'abc'])
                ),
            ],
        ];
    }

    #[Test]
    public function supportsFirstPasswordRouteUserWithTokenDoesNotExist(): void
    {
        $this->userRepo->method('findOneBy')->willReturn(null);

        $request = Request::create(
            'user/1/set-password',
            'PUT',
            [],
            [],
            [],
            [],
            json_encode(['token' => 'a-token', 'password' => 'abc'])
        );

        self::assertEquals(false, $this->sut->supports($request));
    }

    #[Test]
    public function authenticate(): void
    {
        $request = Request::create(
            '/auth/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode(['token' => '_abc'])
        );

        $this->authService->expects(self::once())
            ->method('isSecretValid')
            ->with($request)
            ->willReturn(true);

        $expectedUser = new User('', '', 'user@example.org')
            ->setId(1)
            ->setRoleName('FAKE_ROLE');

        $this->userRepo->method('findOneBy')->willReturn($expectedUser);

        $expectedBruteForceKey = 'token_abc';

        $this->attemptsInTimeChecker->expects(self::once())
            ->method('registerAttempt')
            ->with($expectedBruteForceKey);

        $this->incrementalWaitingTimeChecker->expects(self::once())
            ->method('registerAttempt')
            ->with($expectedBruteForceKey);

        $this->incrementalWaitingTimeChecker->expects(self::once())
            ->method('isFrozen')
            ->with($expectedBruteForceKey)
            ->willReturn(false);

        $this->authService->expects(self::once())
            ->method('isSecretValidForRole')
            ->with('FAKE_ROLE', $request)
            ->willReturn(true);

        $expectedPassport = new SelfValidatingPassport(
            new UserBadge('user@example.org'),
        );

        $actualPassport = $this->sut->authenticate($request);

        self::assertEquals($expectedPassport, $actualPassport);
    }

    #[Test]
    public function authenticateClientSecretNotValid(): void
    {
        self::expectExceptionObject(new UnauthorisedException('client secret not accepted.'));

        $request = Request::create(
            '/auth/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode(['token' => '_abc'])
        );

        $this->authService->expects(self::once())
            ->method('isSecretValid')
            ->with($request)
            ->willReturn(false);

        $this->sut->authenticate($request);
    }

    #[Test]
    public function authenticateAccountIsFrozen(): void
    {
        self::expectException(UnauthorisedException::class);

        $request = Request::create(
            '/auth/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode(['token' => '_abc'])
        );

        $this->authService->expects(self::once())
            ->method('isSecretValid')
            ->with($request)
            ->willReturn(true);

        $expectedBruteForceKey = 'token_abc';

        $this->attemptsInTimeChecker->expects(self::once())
            ->method('registerAttempt')
            ->with($expectedBruteForceKey)
            ->willReturn(null);
        $this->incrementalWaitingTimeChecker->expects(self::once())
            ->method('registerAttempt')
            ->with($expectedBruteForceKey)
            ->willReturn(null);
        $this->incrementalWaitingTimeChecker->expects(self::once())
            ->method('isFrozen')
            ->with($expectedBruteForceKey)
            ->willReturn(true);
        $this->incrementalWaitingTimeChecker->expects(self::once())
            ->method('getUnfrozenAt')
            ->with($expectedBruteForceKey)
            ->willReturn('10000000000');

        $this->sut->authenticate($request);
    }

    #[Test]
    public function authenticateUserWithTokenDoesNotExist(): void
    {
        self::expectExceptionObject(new UserNotFoundException('User not found'));

        $request = Request::create(
            '/auth/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode(['token' => '_abc'])
        );

        $this->authService->expects(self::once())
            ->method('isSecretValid')
            ->with($request)
            ->willReturn(true);

        $this->userRepo->method('findOneBy')->willReturn(null);

        $expectedBruteForceKey = 'token_abc';

        $this->attemptsInTimeChecker->expects(self::once())
            ->method('registerAttempt')
            ->with($expectedBruteForceKey)
            ->willReturn(null);
        $this->incrementalWaitingTimeChecker->expects(self::once())
            ->method('registerAttempt')
            ->with($expectedBruteForceKey)
            ->willReturn(null);
        $this->incrementalWaitingTimeChecker->expects(self::once())
            ->method('isFrozen')
            ->with($expectedBruteForceKey)
            ->willReturn(false);

        $this->sut->authenticate($request);
    }

    #[Test]
    public function authenticateUserHasInvalidRole(): void
    {
        self::expectExceptionObject(new UnauthorisedException('FAKE_ROLE user role not allowed from this client.'));

        $request = Request::create(
            '/auth/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode(['token' => '_abc'])
        );

        $this->authService->expects(self::once())
            ->method('isSecretValid')
            ->with($request)
            ->willReturn(true);

        $expectedUser = new User('', '', 'user@example.org')
            ->setId(1)
            ->setRoleName('FAKE_ROLE');

        $this->userRepo->method('findOneBy')->willReturn($expectedUser);

        $expectedBruteForceKey = 'token_abc';

        $this->attemptsInTimeChecker->expects(self::once())
            ->method('registerAttempt')
            ->with($expectedBruteForceKey)
            ->willReturn($this->attemptsInTimeChecker);
        $this->incrementalWaitingTimeChecker->expects(self::once())
            ->method('registerAttempt')
            ->with($expectedBruteForceKey)
            ->willReturn($this->incrementalWaitingTimeChecker);
        $this->incrementalWaitingTimeChecker->expects(self::once())
            ->method('isFrozen')
            ->with($expectedBruteForceKey)
            ->willReturn(false);

        $this->authService->expects(self::once())
            ->method('isSecretValidForRole')
            ->with('FAKE_ROLE', $request)
            ->willReturn(false);

        $this->sut->authenticate($request);
    }

    #[Test]
    public function onAuthenticationSuccess(): void
    {
        $this->sut->setBruteForceKey('_abc');
        $expectedToken = new NullToken();

        $this->attemptsInTimeChecker->expects(self::once())->method('resetAttempts');
        $this->incrementalWaitingTimeChecker->expects(self::once())->method('resetAttempts')->with('_abc');
        $this->tokenStorage->expects(self::once())->method('setToken')->with($expectedToken);

        $this->sut->onAuthenticationSuccess(new Request(), $expectedToken, 'a-firewall');
    }

    #[Test]
    public function onAuthenticationFailure(): void
    {
        self::expectExceptionObject(
            new InvalidRegistrationTokenException()
        );

        $this->sut->setBruteForceKey('_abc');

        $this->attemptsInTimeChecker->expects(self::once())
            ->method('maxAttemptsReached')
            ->with('_abc')
            ->willReturn(false);

        $authException = new AuthenticationException('Failure message', 123);
        $this->sut->onAuthenticationFailure(new Request(), $authException);
    }

    #[Test]
    public function onAuthenticationFailureMaxLoginAttemptsReached(): void
    {
        self::expectExceptionObject(
            new UserWrongCredentialsManyAttempts()
        );

        $this->sut->setBruteForceKey('_abc');

        $this->attemptsInTimeChecker->expects(self::once())
            ->method('maxAttemptsReached')
            ->with('_abc')
            ->willReturn(true);

        $authException = new AuthenticationException('Failure message', 123);
        $this->sut->onAuthenticationFailure(new Request(), $authException);
    }
}
