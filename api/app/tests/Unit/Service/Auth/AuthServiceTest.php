<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service\Auth;

use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Repository\UserRepository;
use OPG\Digideps\Backend\Service\Auth\AuthService;
use OPG\Digideps\Backend\Service\JWT\JWTService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
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
    private MockObject&UserRepository $userRepo;
    private MockObject&LoggerInterface $logger;
    private MockObject&UserPasswordHasherInterface $passwordHasher;
    private MockObject&JWTService $JWTService;

    public function setUp(): void
    {
        $this->userRepo = $this->createMock(UserRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->JWTService = $this->createMock(JWTService::class);

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
        $this->expectException(\InvalidArgumentException::class);

        $this->authService = new AuthService(
            $this->logger,
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

    public function testGetUserByEmailAndPasswordUserNotFound(): void
    {
        $this->userRepo->method('findOneBy')->with(['email' => 'email@example.org'])->willReturn(null);
        $this->logger->expects($this->once())->method('info')->with($this->matchesRegularExpression('/not found/'));

        $this->assertEquals(false, $this->authService->getUserByEmailAndPassword('email@example.org', 'plainPassword'));
    }

    public function testGetUserByEmailAndPasswordMismatchPassword(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getPassword')->willReturn('encodedPassword');

        $this->userRepo->method('findOneBy')->with(['email' => 'email@example.org'])->willReturn($user);
        $this->passwordHasher->method('isPasswordValid')->with($user, 'plainPassword')->willReturn(false);
        $this->logger->expects($this->once())->method('info')->with($this->matchesRegularExpression('/password mismatch/'));

        $this->assertEquals(null, $this->authService->getUserByEmailAndPassword('email@example.org', 'plainPassword'));
    }

    public function testGetUserByEmailAndPasswordCorrect(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getPassword')->willReturn('encodedPassword');

        $this->userRepo->method('findOneBy')->with(['email' => 'email@example.org'])->willReturn($user);
        $this->passwordHasher->method('isPasswordValid')->with($user, 'plainPassword')->willReturn(true);

        $this->assertEquals($user, $this->authService->getUserByEmailAndPassword('email@example.org', 'plainPassword'));
    }

    public function testGetUserByToken(): void
    {
        $user = $this->createMock(User::class);

        $this->userRepo->method('findOneBy')
            ->with(['registrationToken' => 'token'])
            ->willReturn($user);

        $this->assertEquals($user, $this->authService->getUserByToken('token'));
    }

    public function testGetUserByInvalidToken(): void
    {
        $this->userRepo->method('findOneBy')
            ->with(['registrationToken' => 'wrongtoken'])
            ->willReturn(null);

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
        $this->JWTService->method('verify')->with('not-a.real-jwt')->willReturn(true);

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
}
