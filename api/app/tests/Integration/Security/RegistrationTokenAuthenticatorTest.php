<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\User;
use App\Exception\InvalidRegistrationTokenException;
use App\Exception\UnauthorisedException;
use App\Exception\UserWrongCredentialsManyAttempts;
use App\Repository\UserRepository;
use App\Security\RegistrationTokenAuthenticator;
use App\Service\Auth\AuthService;
use App\Service\BruteForce\AttemptsIncrementalWaitingChecker;
use App\Service\BruteForce\AttemptsInTimeChecker;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class RegistrationTokenAuthenticatorTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy|UserRepository $userRepo;
    private ObjectProphecy|TokenStorageInterface $tokenStorage;
    private ObjectProphecy|AuthService $authService;
    private ObjectProphecy|AttemptsInTimeChecker $attemptsInTimeChecker;
    private ObjectProphecy|AttemptsIncrementalWaitingChecker $incrementalWaitingTimeChecker;
    private RegistrationTokenAuthenticator $sut;

    public function setUp(): void
    {
        $this->userRepo = self::prophesize(UserRepository::class);
        $this->tokenStorage = self::prophesize(TokenStorageInterface::class);
        $this->authService = self::prophesize(AuthService::class);
        $this->attemptsInTimeChecker = self::prophesize(AttemptsInTimeChecker::class);
        $this->incrementalWaitingTimeChecker = self::prophesize(AttemptsIncrementalWaitingChecker::class);

        $this->sut = new RegistrationTokenAuthenticator(
            $this->userRepo->reveal(),
            $this->tokenStorage->reveal(),
            $this->authService->reveal(),
            $this->attemptsInTimeChecker->reveal(),
            $this->incrementalWaitingTimeChecker->reveal()
        );
    }

    /**
     * @test
     *
     * @dataProvider loginRouteRequestProvider
     */
    public function supportsLoginRoute(Request $request, bool $expectedIsSupported)
    {
        self::assertEquals($expectedIsSupported, $this->sut->supports($request));
    }

    public function loginRouteRequestProvider(): array
    {
        return [
            'Valid request - login route' => [
                Request::create(
                    '/auth/login',
                    'POST',
                    [], [], [], [],
                    json_encode(['token' => 'a-token'])
                ),
                true,
            ],
            'Valid uri and method, invalid body' => [
                Request::create(
                    '/auth/login',
                    'POST',
                    [], [], [], [],
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

    /**
     * @test
     */
    public function supportsFirstPasswordRoute()
    {
        $request = Request::create(
            'user/1/set-password',
            'PUT',
            [], [], [], [],
            json_encode(['token' => 'a-token', 'password' => 'abc'])
        );

        $this->userRepo->findOneBy(['registrationToken' => 'a-token'])
            ->shouldBeCalled()
            ->willReturn((new User())->setId(1));

        self::assertEquals(true, $this->sut->supports($request));
    }

    /**
     * @test
     *
     * @dataProvider setFirstPasswordRouteRequestProvider
     */
    public function supportsFirstPasswordRouteFailures(Request $request)
    {
        $this->userRepo->findOneBy(['registrationToken' => 'a-token'])
            ->willReturn((new User())->setId(1));

        self::assertEquals(false, $this->sut->supports($request));
    }

    public function setFirstPasswordRouteRequestProvider(): array
    {
        return [
            'Valid uri, valid method, missing token from body' => [
                Request::create(
                    'user/1/set-password',
                    'PUT',
                    [], [], [], [],
                    json_encode(['password' => 'abc'])
                ),
            ],
            'Valid uri, valid method, missing password from body' => [
                Request::create(
                    'user/1/set-password',
                    'PUT',
                    [], [], [], [],
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
                    [], [], [], [],
                    json_encode(['token' => 'a-token', 'password' => 'abc'])
                ),
            ],
        ];
    }

    /**
     * @test
     */
    public function supportsFirstPasswordRouteUserWithTokenDoesNotExist()
    {
        $this->userRepo->findOneBy(['registrationToken' => 'a-token'])
            ->willReturn(null);

        $request = Request::create(
            'user/1/set-password',
            'PUT',
            [], [], [], [],
            json_encode(['token' => 'a-token', 'password' => 'abc'])
        );

        self::assertEquals(false, $this->sut->supports($request));
    }

    /** @test */
    public function authenticate()
    {
        $request = Request::create(
            '/auth/login',
            'POST',
            [], [], [], [],
            json_encode(['token' => '_abc'])
        );

        $this->authService->isSecretValid($request)
            ->shouldBeCalled()
            ->willReturn(true);

        $expectedUser = (new User())
            ->setId(1)
            ->setEmail('user@example.org')
            ->setRoleName('FAKE_ROLE');

        $this->userRepo->findOneBy(['registrationToken' => '_abc'])
            ->shouldBeCalled()
            ->willReturn($expectedUser);

        $expectedBruteForceKey = 'token_abc';

        $this->attemptsInTimeChecker->registerAttempt($expectedBruteForceKey)
            ->shouldBeCalled();
        $this->incrementalWaitingTimeChecker->registerAttempt($expectedBruteForceKey)
            ->shouldBeCalled();
        $this->incrementalWaitingTimeChecker->isFrozen($expectedBruteForceKey)
            ->shouldBeCalled()
            ->willReturn(false);

        $this->authService->isSecretValidForRole('FAKE_ROLE', $request)
            ->shouldBecalled()
            ->willReturn(true);

        $expectedPassport = new SelfValidatingPassport(
            new UserBadge('user@example.org'),
        );

        $actualPassport = $this->sut->authenticate($request);

        self::assertEquals($expectedPassport, $actualPassport);
    }

    /** @test */
    public function authenticateClientSecretNotValid()
    {
        self::expectExceptionObject(new UnauthorisedException('client secret not accepted.'));

        $request = Request::create(
            '/auth/login',
            'POST',
            [], [], [], [],
            json_encode(['token' => '_abc'])
        );

        $this->authService->isSecretValid($request)
            ->shouldBeCalled()
            ->willReturn(false);

        $this->sut->authenticate($request);
    }

    /** @test */
    public function authenticateAccountIsFrozen()
    {
        self::expectException(UnauthorisedException::class);

        $request = Request::create(
            '/auth/login',
            'POST',
            [], [], [], [],
            json_encode(['token' => '_abc'])
        );

        $this->authService->isSecretValid($request)
            ->shouldBeCalled()
            ->willReturn(true);

        $expectedBruteForceKey = 'token_abc';

        $this->attemptsInTimeChecker->registerAttempt($expectedBruteForceKey)
            ->willReturn(null);
        $this->incrementalWaitingTimeChecker->registerAttempt($expectedBruteForceKey)
            ->willReturn(null);
        $this->incrementalWaitingTimeChecker->isFrozen($expectedBruteForceKey)
            ->willReturn(true);
        $this->incrementalWaitingTimeChecker->getUnfrozenAt($expectedBruteForceKey)
            ->shouldBeCalled()
            ->willReturn('10000000000');

        $this->sut->authenticate($request);
    }

    /** @test */
    public function authenticateUserWithTokenDoesNotExist()
    {
        self::expectExceptionObject(new UserNotFoundException('User not found'));

        $request = Request::create(
            '/auth/login',
            'POST',
            [], [], [], [],
            json_encode(['token' => '_abc'])
        );

        $this->authService->isSecretValid($request)
            ->willReturn(true);

        $this->userRepo->findOneBy(['registrationToken' => '_abc'])
            ->willReturn(null);

        $expectedBruteForceKey = 'token_abc';

        $this->attemptsInTimeChecker->registerAttempt($expectedBruteForceKey)
            ->willReturn(null);
        $this->incrementalWaitingTimeChecker->registerAttempt($expectedBruteForceKey)
            ->willReturn(null);
        $this->incrementalWaitingTimeChecker->isFrozen($expectedBruteForceKey)
            ->willReturn(false);

        $this->sut->authenticate($request);
    }

    /** @test */
    public function authenticateUserHasInvalidRole()
    {
        self::expectExceptionObject(new UnauthorisedException('FAKE_ROLE user role not allowed from this client.'));

        $request = Request::create(
            '/auth/login',
            'POST',
            [], [], [], [],
            json_encode(['token' => '_abc'])
        );

        $this->authService->isSecretValid($request)
            ->willReturn(true);

        $expectedUser = (new User())
            ->setId(1)
            ->setEmail('user@example.org')
            ->setRoleName('FAKE_ROLE');

        $this->userRepo->findOneBy(['registrationToken' => '_abc'])
            ->willReturn($expectedUser);

        $expectedBruteForceKey = 'token_abc';

        $this->attemptsInTimeChecker->registerAttempt($expectedBruteForceKey)
            ->willReturn(null);
        $this->incrementalWaitingTimeChecker->registerAttempt($expectedBruteForceKey)
            ->willReturn(null);
        $this->incrementalWaitingTimeChecker->isFrozen($expectedBruteForceKey)
            ->willReturn(false);

        $this->authService->isSecretValidForRole('FAKE_ROLE', $request)
            ->shouldBecalled()
            ->willReturn(false);

        $this->sut->authenticate($request);
    }

    /** @test */
    public function onAuthenticationSuccess()
    {
        $this->sut->setBruteForceKey('_abc');
        $expectedToken = new NullToken();

        $this->attemptsInTimeChecker->resetAttempts('_abc')
            ->shouldBeCalled();
        $this->incrementalWaitingTimeChecker->resetAttempts('_abc')
            ->shouldBeCalled();
        $this->tokenStorage->setToken($expectedToken)
            ->shouldBeCalled();

        $this->sut->onAuthenticationSuccess(new Request(), $expectedToken, 'a-firewall');
    }

    /** @test */
    public function onAuthenticationFailure()
    {
        self::expectExceptionObject(
            new InvalidRegistrationTokenException('Failure message', 123)
        );

        $this->sut->setBruteForceKey('_abc');

        $this->attemptsInTimeChecker->maxAttemptsReached('_abc')
            ->shouldBeCalled()
            ->willReturn(false);

        $authException = new AuthenticationException('Failure message', 123);
        $this->sut->onAuthenticationFailure(new Request(), $authException);
    }

    /** @test */
    public function onAuthenticationFailureMaxLoginAttemptsReached()
    {
        self::expectExceptionObject(
            new UserWrongCredentialsManyAttempts()
        );

        $this->sut->setBruteForceKey('_abc');

        $this->attemptsInTimeChecker->maxAttemptsReached('_abc')
            ->shouldBeCalled()
            ->willReturn(true);

        $authException = new AuthenticationException('Failure message', 123);
        $this->sut->onAuthenticationFailure(new Request(), $authException);
    }
}
