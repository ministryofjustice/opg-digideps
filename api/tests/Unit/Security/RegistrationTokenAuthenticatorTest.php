<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\RegistrationTokenAuthenticator;
use App\Service\Auth\AuthService;
use App\Service\BruteForce\AttemptsIncrementalWaitingChecker;
use App\Service\BruteForce\AttemptsInTimeChecker;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RegistrationTokenAuthenticatorTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy|UserRepository $userRepo;
    private ObjectProphecy|TokenStorageInterface $tokenStorage;
    private ObjectProphecy|AuthService $authService;
    private ObjectProphecy|AttemptsInTimeChecker $attemptsInTimeChecker;
    private ObjectProphecy|AttemptsIncrementalWaitingChecker $incrementalWaitingTimeChecker;

    public function setUp(): void
    {
        $this->userRepo = self::prophesize(UserRepository::class);
        $this->tokenStorage = self::prophesize(TokenStorageInterface::class);
        $this->authService = self::prophesize(AuthService::class);
        $this->attemptsInTimeChecker = self::prophesize(AttemptsInTimeChecker::class);
        $this->incrementalWaitingTimeChecker = self::prophesize(AttemptsIncrementalWaitingChecker::class);
    }

    /**
     * @test
     * @dataProvider loginRouteRequestProvider
     */
    public function supportsLoginRoute(Request $request, bool $expectedIsSupported)
    {
        $sut = new RegistrationTokenAuthenticator(
            $this->userRepo->reveal(),
            $this->tokenStorage->reveal(),
            $this->authService->reveal(),
            $this->attemptsInTimeChecker->reveal(),
            $this->incrementalWaitingTimeChecker->reveal()
        );

        self::assertEquals($expectedIsSupported, $sut->supports($request));
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

        $sut = new RegistrationTokenAuthenticator(
            $this->userRepo->reveal(),
            $this->tokenStorage->reveal(),
            $this->authService->reveal(),
            $this->attemptsInTimeChecker->reveal(),
            $this->incrementalWaitingTimeChecker->reveal()
        );

        self::assertEquals(true, $sut->supports($request));
    }

    /**
     * @test
     * @dataProvider setFirstPasswordRouteRequestProvider
     */
    public function supportsFirstPasswordRouteFailures(Request $request)
    {
        $this->userRepo->findOneBy(['registrationToken' => 'a-token'])
            ->willReturn((new User())->setId(1));

        $sut = new RegistrationTokenAuthenticator(
            $this->userRepo->reveal(),
            $this->tokenStorage->reveal(),
            $this->authService->reveal(),
            $this->attemptsInTimeChecker->reveal(),
            $this->incrementalWaitingTimeChecker->reveal()
        );

        self::assertEquals(false, $sut->supports($request));
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

        $sut = new RegistrationTokenAuthenticator(
            $this->userRepo->reveal(),
            $this->tokenStorage->reveal(),
            $this->authService->reveal(),
            $this->attemptsInTimeChecker->reveal(),
            $this->incrementalWaitingTimeChecker->reveal()
        );

        $request = Request::create(
            'user/1/set-password',
            'PUT',
            [], [], [], [],
            json_encode(['token' => 'a-token', 'password' => 'abc'])
        );

        self::assertEquals(false, $sut->supports($request));
    }
}
