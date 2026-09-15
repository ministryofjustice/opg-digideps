<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Security;

use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Exception\UnauthorisedException;
use OPG\Digideps\Backend\Exception\UserWrongCredentialsException;
use OPG\Digideps\Backend\Exception\UserWrongCredentialsManyAttempts;
use OPG\Digideps\Backend\Repository\UserRepository;
use OPG\Digideps\Backend\Security\LoginRequestAuthenticator;
use OPG\Digideps\Backend\Service\Auth\AuthService;
use OPG\Digideps\Backend\Service\BruteForce\AttemptsIncrementalWaitingChecker;
use OPG\Digideps\Backend\Service\BruteForce\AttemptsInTimeChecker;
use OPG\Digideps\Backend\Service\DateTimeProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

final class LoginRequestAuthenticatorTest extends TestCase
{
    private UserRepository&MockObject $userRepo;
    private AttemptsInTimeChecker&MockObject $attemptsInTimeChecker;
    private AttemptsIncrementalWaitingChecker&MockObject $incrementalWaitingTimeChecker;
    private AuthService&MockObject $authService;
    private TokenStorageInterface&MockObject $tokenStorage;
    private DateTimeProvider&MockObject $dateTimeProvider;
    private LoginRequestAuthenticator $sut;

    public function setUp(): void
    {
        $this->userRepo = self::createMock(UserRepository::class);
        $this->attemptsInTimeChecker = self::createMock(AttemptsInTimeChecker::class);
        $this->incrementalWaitingTimeChecker = self::createMock(AttemptsIncrementalWaitingChecker::class);
        $this->authService = self::createMock(AuthService::class);
        $this->tokenStorage = self::createMock(TokenStorageInterface::class);
        $verboseLogger = self::createMock(LoggerInterface::class);
        $this->dateTimeProvider = self::createMock(DateTimeProvider::class);

        $this->sut = new LoginRequestAuthenticator(
            $this->userRepo,
            $this->attemptsInTimeChecker,
            $this->incrementalWaitingTimeChecker,
            $this->authService,
            $this->tokenStorage,
            $verboseLogger,
            $this->dateTimeProvider
        );
    }

    #[DataProvider('requestProvider')]
    #[Test]
    public function supports(Request $request, bool $expectedIsSupported): void
    {
        self::assertEquals($expectedIsSupported, $this->sut->supports($request));
    }

    public static function requestProvider(): array
    {
        return [
            'Valid request' => [
                Request::create(
                    '/auth/login',
                    'POST',
                    [],
                    [],
                    [],
                    [],
                    json_encode(['email' => 'a@b.com', 'password' => 'password123']),
                ),
                true,
            ],
            'Valid uri, invalid method' => [
                Request::create(
                    '/auth/login',
                    'GET',
                    [],
                    [],
                    [],
                    [],
                    json_encode(['email' => 'a@b.com', 'password' => 'password123']),
                ),
                false,
            ],
            'Invalid uri, valid method' => [
                Request::create(
                    '/auth/logout',
                    'POST',
                    [],
                    [],
                    [],
                    [],
                    json_encode(['email' => 'a@b.com', 'password' => 'password123']),
                ),
                false,
            ],
            'Invalid body - missing email' => [
                Request::create(
                    '/auth/login',
                    'POST',
                    [],
                    [],
                    [],
                    [],
                    json_encode(['password' => 'password123']),
                ),
                false,
            ],
            'Invalid body - missing password' => [
                Request::create(
                    '/auth/login',
                    'POST',
                    [],
                    [],
                    [],
                    [],
                    json_encode(['email' => 'a@b.com']),
                ),
                false,
            ],
            'Invalid body - empty email' => [
                Request::create(
                    '/auth/login',
                    'POST',
                    [],
                    [],
                    [],
                    [],
                    json_encode(['email' => '', 'password' => 'password123']),
                ),
                false,
            ],
            'Invalid body - empty password' => [
                Request::create(
                    '/auth/login',
                    'POST',
                    [],
                    [],
                    [],
                    [],
                    json_encode(['email' => 'a@example.org', 'password' => '']),
                ),
                false,
            ],
            'Invalid body - empty body' => [
                Request::create(
                    '/auth/login',
                    'POST',
                ),
                false,
            ],
        ];
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
            json_encode(['email' => 'a@b.com', 'password' => 'password123']),
        );

        $user = new User('', '', 'a@b.com')
            ->setPassword('password123')
            ->setRoleName('ROLE_USER');

        $this->authService->expects(self::once())
            ->method('isSecretValid')
            ->with($request)
            ->willReturn(true);
        $this->authService->expects(self::once())
            ->method('isSecretValidForRole')
            ->with('ROLE_USER', $request)
            ->willReturn(true);

        $this->userRepo->expects(self::once())
            ->method('findOneBy')
            ->with(['email' => 'a@b.com'])
            ->willReturn($user);

        $expectedPassport = new Passport(
            new UserBadge('a@b.com'),
            new PasswordCredentials('password123')
        );

        self::assertEquals($expectedPassport, $this->sut->authenticate($request));
    }

    #[Test]
    public function authenticateRequiresAValidSecret(): void
    {
        self::expectExceptionObject(new UnauthorisedException('client secret not accepted.'));

        $request = Request::create(
            '/auth/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode(['email' => 'a@b.com', 'password' => 'password123']),
        );

        $this->authService->expects(self::once())
            ->method('isSecretValid')
            ->with($request)
            ->willReturn(false);

        $this->sut->authenticate($request);
    }

    #[DataProvider('loginDetailsProvider')]
    #[Test]
    public function authenticateRequiresLoginDetails(array $loginDetails): void
    {
        self::expectExceptionObject(new UserNotFoundException('User not found'));

        $request = Request::create(
            '/auth/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode($loginDetails),
        );

        $this->authService->expects(self::once())
            ->method('isSecretValid')
            ->with($request)
            ->willReturn(true);

        $this->sut->authenticate($request);
    }

    public static function loginDetailsProvider(): array
    {
        return [
            'Valid keys, empty password' => [['email' => 'a@b.com', 'password' => '']],
            'Valid keys, empty email' => [['email' => null, 'password' => 'xgdfghdfgh']],
            'Valid keys, both empty' => [['email' => '', 'password' => null]],
            'Missing email key' => [['username' => 'a@b.com', 'password' => 'asdfsgsdfg']],
            'Missing password key' => [['email' => 'a@b.com', 'secret' => 'sdfggsgh']],
            'Missing both keys' => [['username' => 'a@b.com', 'secret' => 'sdfsdfsdf']],
        ];
    }

    #[Test]
    public function authenticateDoesNotAuthenticateIfUserIsFrozenOut(): void
    {
        $now = new \DateTime();
        $nowPlusOneHour = new \DateTime($now->format('Y-m-d'))->modify('+1 hour');

        $nowTime = intval($now->format('U'));
        $nowPlusOneHourTime = intval($nowPlusOneHour->format('U'));

        $nextAttemptIn = ceil(($nowPlusOneHourTime - $nowTime) / 60);

        $expectedException = new UnauthorisedException(
            "Attack detected. Please try again in $nextAttemptIn minutes",
            423
        );
        $expectedException->setData($nextAttemptIn);

        $request = Request::create(
            '/auth/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode(['email' => 'a@b.com', 'password' => 'password123']),
        );

        $this->authService->expects(self::once())
            ->method('isSecretValid')
            ->with($request)
            ->willReturn(true);

        $this->attemptsInTimeChecker->expects(self::once())
            ->method('registerAttempt')
            ->with('emaila@b.com')
            ->willReturn($this->attemptsInTimeChecker);
        $this->incrementalWaitingTimeChecker->expects(self::once())
            ->method('registerAttempt')
            ->with('emaila@b.com')
            ->willReturn($this->incrementalWaitingTimeChecker);

        $this->incrementalWaitingTimeChecker->expects(self::once())
            ->method('isFrozen')
            ->with('emaila@b.com')
            ->willReturn(true);
        $this->dateTimeProvider->expects(self::once())
            ->method('getDateTime')
            ->willReturn($now);
        $this->incrementalWaitingTimeChecker->expects(self::once())
            ->method('getUnfrozenAt')
            ->with('emaila@b.com')
            ->willReturn($nowPlusOneHourTime);

        try {
            $this->sut->authenticate($request);
            $this->fail('UnauthorisedException was not thrown');
        } catch (UnauthorisedException $e) {
            $this->assertSame(
                $nextAttemptIn,
                $e->getData()
            );
        }
    }

    #[Test]
    public function authenticateDoesNotAuthenticateIfUserCannotBeFound(): void
    {
        self::expectExceptionObject(new UserNotFoundException('User not found'));

        $request = Request::create(
            '/auth/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode(['email' => 'a@b.com', 'password' => 'password123']),
        );

        $this->authService->expects(self::once())
            ->method('isSecretValid')
            ->with($request)
            ->willReturn(true);

        $this->attemptsInTimeChecker->expects(self::once())
            ->method('registerAttempt')
            ->with('emaila@b.com')
            ->willReturn($this->attemptsInTimeChecker);
        $this->incrementalWaitingTimeChecker->expects(self::once())
            ->method('registerAttempt')
            ->with('emaila@b.com')
            ->willReturn($this->incrementalWaitingTimeChecker);

        $this->incrementalWaitingTimeChecker->expects(self::once())
            ->method('isFrozen')
            ->with('emaila@b.com')
            ->willReturn(false);

        $this->userRepo->expects(self::once())
            ->method('findOneBy')
            ->with(['email' => 'a@b.com'])
            ->willReturn(null);

        $this->sut->authenticate($request);
    }

    #[Test]
    public function authenticateDoesNotAuthenticateIfSecretIsNotValidForUserRole(): void
    {
        self::expectExceptionObject(new UnauthorisedException('ROLE_USER user role not allowed from this client.'));

        $request = Request::create(
            '/auth/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode(['email' => 'a@b.com', 'password' => 'password123']),
        );

        $this->authService->expects(self::once())
            ->method('isSecretValid')
            ->with($request)
            ->willReturn(true);

        $this->attemptsInTimeChecker->expects(self::once())
            ->method('registerAttempt')
            ->with('emaila@b.com')
            ->willReturn($this->attemptsInTimeChecker);
        $this->incrementalWaitingTimeChecker->expects(self::once())
            ->method('registerAttempt')
            ->with('emaila@b.com')
            ->willReturn($this->incrementalWaitingTimeChecker);

        $this->incrementalWaitingTimeChecker->expects(self::once())
            ->method('isFrozen')
            ->with('emaila@b.com')
            ->willReturn(false);

        $user = new User('', '', 'a@b.com')
            ->setPassword('password123')
            ->setRoleName('ROLE_USER');
        $this->userRepo->expects(self::once())
            ->method('findOneBy')
            ->with(['email' => 'a@b.com'])
            ->willReturn($user);

        $this->authService->expects(self::once())
            ->method('isSecretValidForRole')
            ->with('ROLE_USER', $request)
            ->willReturn(false);

        $this->sut->authenticate($request);
    }

    #[Test]
    public function onAuthenticationSuccess(): void
    {
        $token = new UsernamePasswordToken(new User('', '', ''), 'private-firewall');

        $this->tokenStorage->expects(self::once())
            ->method('setToken')
            ->with($token);
        $this->attemptsInTimeChecker->expects(self::once())
            ->method('resetAttempts')
            ->with('');
        $this->incrementalWaitingTimeChecker->expects(self::once())
            ->method('resetAttempts')
            ->with('');

        $request = Request::create(
            '/auth/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode(['email' => 'a@b.com', 'password' => 'password123']),
        );

        self::assertNull($this->sut->onAuthenticationSuccess($request, $token, 'private-firewall'));
    }

    #[Test]
    public function onAuthenticationFailure(): void
    {
        self::expectExceptionObject(new UserWrongCredentialsException());

        $authException = new AuthenticationException('It broke', 444);
        $this->attemptsInTimeChecker->expects(self::once())
            ->method('maxAttemptsReached')
            ->with('')
            ->willReturn(false);

        $request = Request::create(
            '/auth/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode(['email' => 'a@b.com', 'password' => 'password123']),
        );

        self::assertNull($this->sut->onAuthenticationFailure($request, $authException));
    }

    #[Test]
    public function onAuthenticationFailureThrowsUserWrongCredentialsManyAttemptsExceptionOnTooManyAttempts(): void
    {
        $authException = new AuthenticationException('It broke', 444);

        self::expectExceptionObject(new UserWrongCredentialsManyAttempts());

        $this->attemptsInTimeChecker->expects(self::once())
            ->method('maxAttemptsReached')
            ->with('')
            ->willReturn(true);

        $request = Request::create(
            '/auth/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode(['email' => 'a@b.com', 'password' => 'password123']),
        );

        self::assertNull($this->sut->onAuthenticationFailure($request, $authException));
    }
}
