<?php

namespace App\Tests\Unit\Service\Auth;

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

class AuthServiceTest extends TestCase
{
    /**
     * @var AuthService
     */
    private $authService;

    private $clientPermissions = [
        'frontend' => [
            'ROLE_DEPUTY',
        ],
        'admin' => [
            'ROLE_ADMIN',
        ],
    ];

    /**
     * @var RoleHierarchy
     */
    private $roleHierarchy;

    /**
     * @var Mockery\MockInterface
     */
    private $userRepo;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|\Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * @var Mockery\MockInterface
     */
    private $passwordHasher;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|JWTService
     */
    private $JWTService;

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

    public function testMissingSecrets()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->authService = new AuthService($this->logger,
            $this->userRepo,
            $this->roleHierarchy,
            [],
            $this->JWTService,
            $this->passwordHasher
        );
    }

    public function isSecretValidProvider()
    {
        return [
            ['layDeputySecret', true],
            ['layDeputySecret ', false],
            ['LAYDEPUTYSECRET-deputy ', false],
            ['123', false],
            [null, false],
            [0, false],
            [false, false],
        ];
    }

    /**
     * @dataProvider isSecretValidProvider
     */
    public function testisSecretValid($clientSecret, $expectedValidity)
    {
        $request = new Request();
        $request->headers->set(AuthService::HEADER_CLIENT_SECRET, $clientSecret);

        $this->assertEquals($expectedValidity, $this->authService->isSecretValid($request));
    }

    public function testgetUserByEmailAndPasswordUserNotFound()
    {
        $this->userRepo->shouldReceive('findOneBy')->with(['email' => 'email@example.org'])->andReturn(null);
        $this->logger->shouldReceive('info')->with(\Mockery::pattern('/not found/'))->once();

        $this->assertEquals(false, $this->authService->getUserByEmailAndPassword('email@example.org', 'plainPassword'));
    }

    public function testgetUserByEmailAndPasswordMismatchPassword()
    {
        $user = m::stub('App\Entity\User', [
            'getSalt' => 'salt',
            'getPassword' => 'encodedPassword',
        ]);
        $this->userRepo->shouldReceive('findOneBy')->with(['email' => 'email@example.org'])->andReturn($user);

        $this->passwordHasher->shouldReceive('isPasswordValid')->with($user, 'plainPassword')->andReturn(false);

        $this->logger->shouldReceive('info')->with(\Mockery::pattern('/password mismatch/'))->once();

        $this->assertEquals(null, $this->authService->getUserByEmailAndPassword('email@example.org', 'plainPassword'));
    }

    public function testgetUserByEmailAndPasswordCorrect()
    {
        $user = m::stub('App\Entity\User', [
            'getSalt' => 'salt',
            'getPassword' => 'encodedPassword',
        ]);
        $this->userRepo->shouldReceive('findOneBy')->with(['email' => 'email@example.org'])->andReturn($user);

        $encoder = m::stub('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface', [
            'encodePassword(plainPassword,salt)' => 'encodedPassword',
        ]);
        $this->passwordHasher->shouldReceive('isPasswordValid')->with($user, 'plainPassword')->andReturn(true);

        $this->assertEquals($user, $this->authService->getUserByEmailAndPassword('email@example.org', 'plainPassword'));
    }

    public function testgetUserByToken()
    {
        $user = m::mock('App\Entity\User');

        $this->userRepo->shouldReceive('findOneBy')->with(['registrationToken' => 'token'])->andReturn($user);

        $this->assertEquals($user, $this->authService->getUserByToken('token'));

        $this->userRepo->shouldReceive('findOneBy')->with(['registrationToken' => 'wrongtoken'])->andReturn(null);

        $this->assertEquals(null, $this->authService->getUserByToken('wrongtoken'));
    }

    public function isSecretValidForUserProvider()
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
            ['layDeputySecretNoPermissions', false, false],
            ['layDeputySecretWrongFormat', '', false],
            [null, null, false],
            [null, 'ROLE_LAY_DEPUTY', false],
        ];
    }

    /**
     * @dataProvider isSecretValidForUserProvider
     */
    public function testisSecretValidForRole($clientSecret, $role, $expectedResult)
    {
        $request = new Request();
        $request->headers->set(AuthService::HEADER_CLIENT_SECRET, $clientSecret);

        $this->assertEquals($expectedResult, $this->authService->isSecretValidForRole($role, $request));
    }

    /** @test */
    public function jWTIsValid()
    {
        $this->JWTService->shouldReceive('verify')->with('not-a.real-jwt')->andReturn(true);

        $request = new Request();
        $request->headers->set(AuthService::HEADER_JWT, 'not-a.real-jwt');

        $this->assertEquals(true, $this->authService->JWTIsValid($request));
    }

    /**
     * @test
     *
     * @dataProvider JWTValidFailureProvider
     */
    public function jWTIsValidFailures(Request $request)
    {
        $this->assertEquals(false, $this->authService->JWTIsValid($request));
    }

    public function JWTValidFailureProvider()
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
