<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\LoginRequestAuthenticator;
use App\Service\Auth\AuthService;
use App\Service\BruteForce\AttemptsIncrementalWaitingChecker;
use App\Service\BruteForce\AttemptsInTimeChecker;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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

    public function setUp(): void
    {
        $this->userRepo = self::prophesize(UserRepository::class);
        $this->attemptsInTimeChecker = self::prophesize(AttemptsInTimeChecker::class);
        $this->incrementalWaitingTimechecker = self::prophesize(AttemptsIncrementalWaitingChecker::class);
        $this->authService = self::prophesize(AuthService::class);
        $this->tokenStorage = self::prophesize(TokenStorageInterface::class);
        $this->logger = self::prophesize(LoggerInterface::class);
    }

    /**
     * @test
     * @dataProvider requestProvider
     */
    public function supports(Request $request, bool $expectedIsSupported)
    {
        $sut = new LoginRequestAuthenticator(
            $this->userRepo->reveal(),
            $this->attemptsInTimeChecker->reveal(),
            $this->incrementalWaitingTimechecker->reveal(),
            $this->authService->reveal(),
            $this->tokenStorage->reveal(),
            $this->logger->reveal()
        );

        self::assertEquals($expectedIsSupported, $sut->supports($request));
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

        $sut = new LoginRequestAuthenticator(
            $this->userRepo->reveal(),
            $this->attemptsInTimeChecker->reveal(),
            $this->incrementalWaitingTimechecker->reveal(),
            $this->authService->reveal(),
            $this->tokenStorage->reveal(),
            $this->logger->reveal()
        );

        $expectedPassport = new Passport(
            new UserBadge('a@b.com'),
            new PasswordCredentials('password123')
        );

        self::assertEquals($expectedPassport, $sut->authenticate($request));
    }
}
