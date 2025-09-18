<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth;

use InvalidArgumentException;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use App\Repository\UserRepository;
use App\Service\Auth\AuthService;
use App\Service\JWT\JWTService;
use Mockery;
use MockeryStub as m;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

final class AuthServiceTest extends TestCase
{
    private AuthService $authService;

    private array $clientPermissions = [
        'frontend' => [
            'ROLE_DEPUTY',
        ],
        'admin' => [
            'ROLE_ADMIN',
        ],
    ];

    private RoleHierarchy $roleHierarchy;
    private MockInterface|UserRepository $userRepo;
    private MockInterface|LoggerInterface $logger;
    private MockInterface|UserPasswordHasherInterface $passwordHasher;
    private MockInterface|JWTService $JWTService;

    public function setUp(): void
    {
        $this->userRepo = m::stub(UserRepository::class);
        $this->logger = m::mock(LoggerInterface::class);
        $this->passwordHasher = m::stub(UserPasswordHasherInterface::class);
        $this->JWTService = m::mock(JWTService::class);

        $hierarchy = [
            'ROLE_SUPER_ADMIN' => ['ROLE_ADMIN'],
            'ROLE_LAY_DEPUTY' => ['ROLE_DEPUTY'],
            'ROLE_PROF_DEPUTY' => ['ROLE_DEPUTY'],
        ];

        $this->roleHierarchy = new RoleHierarchy($hierarchy);
        $this->authService = new AuthService(
            $this->logger,
            $this->userRepo,
            $this->roleHierarchy,
            $this->clientPermissions,
            $this->JWTService,
            $this->passwordHasher,
        );
    }

    public function testMissingSecrets(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->authService = new AuthService($this->logger,
            $this->userRepo,
            $this->roleHierarchy,
            [],
            $this->JWTService,
            $this->passwordHasher
        );
    }

    public static function isSecretValidProvider(): array
    {
        return [
            ['layDeputySecret', true],
            ['layDeputySecret ', false],
            ['LAYDEPUTYSECRET-deputy ', false],
            ['123', false],
            [null, false],
        ];
    }

    #[DataProvider('isSecretValidProvider')]
    public function testisSecretValid(?string $clientSecret, bool $expectedValidity): void
    {
        $request = new Request();
        $request->headers->set(AuthService::HEADER_CLIENT_SECRET, $clientSecret);

        $this->assertEquals($expectedValidity, $this->authService->isSecretValid($request));
    }

    public function testgetUserByEmailAndPasswordUserNotFound(): void
    {
        $this->userRepo->shouldReceive('findOneBy')->with(['email' => 'email@example.org'])->andReturn(null);
        $this->logger->shouldReceive('info')->with(Mockery::pattern('/not found/'))->once();

        $this->assertEquals(false, $this->authService->getUserByEmailAndPassword('email@example.org', 'plainPassword'));
    }

    public function testgetUserByEmailAndPasswordMismatchPassword(): void
    {
        $user = m::stub('App\Entity\User', [
                'getSalt' => 'salt',
                'getPassword' => 'encodedPassword',
        ]);
        $this->userRepo->shouldReceive('findOneBy')->with(['email' => 'email@example.org'])->andReturn($user);

        $this->passwordHasher->shouldReceive('isPasswordValid')->with($user, 'plainPassword')->andReturn(false);

        $this->logger->shouldReceive('info')->with(Mockery::pattern('/password mismatch/'))->once();

        $this->assertEquals(null, $this->authService->getUserByEmailAndPassword('email@example.org', 'plainPassword'));
    }

    public function testgetUserByEmailAndPasswordCorrect(): void
    {
        $user = m::stub('App\Entity\User', [
                'getSalt' => 'salt',
                'getPassword' => 'encodedPassword',
        ]);
        $this->userRepo->shouldReceive('findOneBy')->with(['email' => 'email@example.org'])->andReturn($user);

        $this->passwordHasher->shouldReceive('isPasswordValid')->with($user, 'plainPassword')->andReturn(true);

        $this->assertEquals($user, $this->authService->getUserByEmailAndPassword('email@example.org', 'plainPassword'));
    }

    public function testgetUserByToken(): void
    {
        $user = m::mock('App\Entity\User');

        $this->userRepo->shouldReceive('findOneBy')->with(['registrationToken' => 'token'])->andReturn($user);

        $this->assertEquals($user, $this->authService->getUserByToken('token'));

        $this->userRepo->shouldReceive('findOneBy')->with(['registrationToken' => 'wrongtoken'])->andReturn(null);

        $this->assertEquals(null, $this->authService->getUserByToken('wrongtoken'));
    }

    public static function isSecretValidForUserProvider(): array
    {
        return [
            ['layDeputySecret', 'ROLE_LAY_DEPUTY', true],
            ['layDeputySecret', 'ROLE_ADMIN', false],
            ['layDeputySecret', 'OTHER_ROLE', false],
            ['layDeputySecret', 'ROLE_PROF_DEPUTY', true],
            ['layDeputySecret', null, false],
            ['adminSecret', 'ROLE_LAY_DEPUTY', false],
            ['adminSecret', 'ROLE_ADMIN', true],
            ['adminSecret', 'ROLE_SUPER_ADMIN', true],
            ['adminSecret', 'OTHER_ROLE', false],
            ['adminSecret', null, false],
            ['layDeputySecretNoPermissions', '', false],
            ['layDeputySecretNoPermissions', null, false],
            ['layDeputySecretWrongFormat', '', false],
            [null, null, false],
            [null, 'ROLE_LAY_DEPUTY', false],
        ];
    }

    #[DataProvider('isSecretValidForUserProvider')]
    public function testisSecretValidForRole(?string $clientSecret, ?string $role, bool $expectedResult): void
    {
        $request = new Request();
        $request->headers->set(AuthService::HEADER_CLIENT_SECRET, $clientSecret);

        $this->assertEquals($expectedResult, $this->authService->isSecretValidForRole($role, $request));
    }

    #[Test]
    public function jWTIsValid(): void
    {
        $this->JWTService->shouldReceive('verify')->with('not-a.real-jwt')->andReturn(true);

        $request = new Request();
        $request->headers->set(AuthService::HEADER_JWT, 'not-a.real-jwt');

        $this->assertEquals(true, $this->authService->JWTIsValid($request));
    }

    #[DataProvider('JWTValidFailureProvider')]
    #[Test]
    public function jWTIsValidFailures(Request $request): void
    {
        $this->assertEquals(false, $this->authService->JWTIsValid($request));
    }

    public static function JWTValidFailureProvider(): array
    {
        $requestNoHeader = new Request();

        $requestHeaderNull = new Request();
        $requestHeaderNull->headers->set(AuthService::HEADER_JWT, null);

        return [
            'JWT header does not exist' => [$requestNoHeader],
            'JWT header exists but is null' => [$requestHeaderNull],
        ];
    }

    public function tearDown(): void
    {
        m::close();
    }
}
