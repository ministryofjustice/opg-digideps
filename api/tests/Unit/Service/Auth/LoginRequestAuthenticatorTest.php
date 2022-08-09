<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth;

use App\Entity\User;
use App\Exception\UnauthorisedException;
use App\Repository\UserRepository;
use App\Security\LoginRequestAuthenticator;
use App\Service\Auth\AuthService;
use App\Service\BruteForce\AttemptsIncrementalWaitingChecker;
use App\Service\BruteForce\AttemptsInTimeChecker;
use App\Service\DateTimeProvider;
use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class LoginRequestAuthenticatorTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy|UserRepository $userRepo;
    private ObjectProphecy|AttemptsInTimeChecker $attemptsInTimeChecker;
    private ObjectProphecy|AttemptsIncrementalWaitingChecker $incrementalWaitingTimechecker;
    private ObjectProphecy|AuthService $authService;
    private ObjectProphecy|TokenStorageInterface $tokenStorage;
    private ObjectProphecy|LoggerInterface $logger;
    private ObjectProphecy|DateTimeProvider $dateTimeProvider;

    public function setUp(): void
    {
        $this->userRepo = self::prophesize(UserRepository::class);
        $this->attemptsInTimeChecker = self::prophesize(AttemptsInTimeChecker::class);
        $this->incrementalWaitingTimechecker = self::prophesize(AttemptsIncrementalWaitingChecker::class);
        $this->authService = self::prophesize(AuthService::class);
        $this->tokenStorage = self::prophesize(TokenStorageInterface::class);
        $this->logger = self::prophesize(LoggerInterface::class);
        $this->dateTimeProvider = self::prophesize(DateTimeProvider::class);

        $this->sut = new LoginRequestAuthenticator(
            $this->userRepo->reveal(),
            $this->attemptsInTimeChecker->reveal(),
            $this->incrementalWaitingTimechecker->reveal(),
            $this->authService->reveal(),
            $this->tokenStorage->reveal(),
            $this->logger->reveal(),
            $this->dateTimeProvider->reveal()
        );
    }

    /**
     * @test
     * @dataProvider requestProvider
     */
    public function supports(Request $request, bool $expectedIsSupported)
    {
        self::assertEquals($expectedIsSupported, $this->sut->supports($request));
    }

    public function requestProvider()
    {
        return [
            'Valid request' => [Request::create('/auth/login', 'POST'), true],
            'Valid uri, invalid method' => [Request::create('/auth/login', 'GET'), false],
            'Invalid uri, valid method' => [Request::create('/auth/logout', 'POST'), false],
            'Invalid values' => [Request::create('/auth/logout', 'DELETE'), false],
        ];
    }

    /** @test */
    public function authenticate()
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

        $user = (new User())
            ->setPassword('password123')
            ->setRoleName('ROLE_USER');

        $this->authService->isSecretValid($request)->willReturn(true);
        $this->authService->isSecretValidForRole('ROLE_USER', $request)->willReturn(true);

        $this->userRepo->findOneBy(['email' => 'a@b.com'])->willReturn($user);

        $expectedPassport = new Passport(
            new UserBadge('a@b.com'),
            new PasswordCredentials('password123')
        );

        self::assertEquals($expectedPassport, $this->sut->authenticate($request));
    }

    /** @test */
    public function authenticateRequiresAValidSecret()
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

        $this->authService->isSecretValid($request)->willReturn(false);

        $this->sut->authenticate($request);
    }

    /**
     * @test
     * @dataProvider loginDetailsProvider
     */
    public function authenticateRequiresLoginDetails(array $loginDetails)
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

        $this->authService->isSecretValid($request)->willReturn(true);

        $this->sut->authenticate($request);
    }

    public function loginDetailsProvider()
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

    /**
     * @test
     */
    public function authenticateDoesNotAuthenticateIfUserIsFrozenOut()
    {
        $now = new DateTime();
        $nowPlusOneHour = (new DateTime($now->format('Y-m-d')))->modify('+1 hour');

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

        $this->authService->isSecretValid($request)->willReturn(true);

        $this->attemptsInTimeChecker->registerAttempt('emaila@b.com')->willReturn($this->attemptsInTimeChecker);
        $this->incrementalWaitingTimechecker->registerAttempt('emaila@b.com')->willReturn($this->incrementalWaitingTimechecker);

        $this->incrementalWaitingTimechecker->isFrozen('emaila@b.com')->willReturn(true);
        $this->dateTimeProvider->getDateTime()->willReturn($now);
        $this->incrementalWaitingTimechecker->getUnfrozenAt('emaila@b.com')->willReturn($nowPlusOneHourTime);

        try {
            $this->sut->authenticate($request);
            $this->fail('UnauthorisedException was not thrown');
        } catch (UnauthorisedException $e) {
            $this->assertSame(
                $nowPlusOneHourTime,
                $e->getData()
            );
        }
    }
}
