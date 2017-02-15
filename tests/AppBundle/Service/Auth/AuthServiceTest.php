<?php

namespace Tests\AppBundle\Service\Auth;

use AppBundle\Service\Auth\AuthService;
use MockeryStub as m;
use Symfony\Component\HttpFoundation\Request;

class AuthServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AuthService
     */
    private $authService;

    private $clientSecrets = [
        '123abc-deputy' => [
            'permissions' => ['ROLE_LAY_DEPUTY'],
        ],
        '123abc-both' => [
            'permissions' => ['ROLE_ADMIN', 'ROLE_LAY_DEPUTY'],
        ],
        '123abc-admin' => [
            'permissions' => ['ROLE_ADMIN'],
        ],
        '123abc-deputyNoPermissions' => [
        ],
        '123abc-deputyWrongFormat' => 'IShouldBeAnArray',
    ];

    public function setUp()
    {
        $this->userRepo = m::stub('Doctrine\ORM\EntityRepository');
        $this->logger = m::mock('Symfony\Bridge\Monolog\Logger');
        $this->encoderFactory = m::stub('Symfony\Component\Security\Core\Encoder\EncoderFactory');

        $this->container = m::stub('Symfony\Component\DependencyInjection\Container', [
                'getParameter(client_secrets)' => $this->clientSecrets,
                'get(em)->getRepository(AppBundle\Entity\User)' => $this->userRepo,
                'get(logger)' => $this->logger,
                'get(security.encoder_factory)' => $this->encoderFactory,
        ]);

        $this->authService = new AuthService($this->container);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMissingSecrets()
    {
        $container = m::stub('Symfony\Component\DependencyInjection\Container', [
                'getParameter(client_secrets)' => [],
        ]);

        $this->authService = new AuthService($container);
    }

    public function isSecretValidProvider()
    {
        return [
            ['123abc-deputy', true],
            [' 123abc-deputy ', false],
            ['123ABC-deputy ', false],
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
        $this->logger->shouldReceive('info')->with(matchesPattern('/not found/'))->once();

        $this->assertEquals(false, $this->authService->getUserByEmailAndPassword('email@example.org', 'plainPassword'));
    }

    public function testgetUserByEmailAndPasswordMismatchPassword()
    {
        $user = m::stub('AppBundle\Entity\User', [
                'getSalt' => 'salt',
                'getPassword' => 'encodedPassword',
        ]);
        $this->userRepo->shouldReceive('findOneBy')->with(['email' => 'email@example.org'])->andReturn($user);

        $encoder = m::stub('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface', [
                'encodePassword(plainPassword,salt)' => 'encodedPassword-WRONG',
        ]);
        $this->encoderFactory->shouldReceive('getEncoder')->with($user)->andReturn($encoder);

        $this->logger->shouldReceive('info')->with(matchesPattern('/password mismatch/'))->once();

        $this->assertEquals(null, $this->authService->getUserByEmailAndPassword('email@example.org', 'plainPassword'));
    }

    public function testgetUserByEmailAndPasswordCorrect()
    {
        $user = m::stub('AppBundle\Entity\User', [
                'getSalt' => 'salt',
                'getPassword' => 'encodedPassword',
        ]);
        $this->userRepo->shouldReceive('findOneBy')->with(['email' => 'email@example.org'])->andReturn($user);

        $encoder = m::stub('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface', [
                'encodePassword(plainPassword,salt)' => 'encodedPassword',
        ]);
        $this->encoderFactory->shouldReceive('getEncoder')->with($user)->andReturn($encoder);

        $this->assertEquals($user, $this->authService->getUserByEmailAndPassword('email@example.org', 'plainPassword'));
    }

    public function testgetUserByToken()
    {
        $user = m::mock('AppBundle\Entity\User');

        $this->userRepo->shouldReceive('findOneBy')->with(['registrationToken' => 'token'])->andReturn($user);
        $this->assertEquals($user, $this->authService->getUserByToken('token'));

        $this->userRepo->shouldReceive('findOneBy')->with(['registrationToken' => 'wrongtoken'])->andReturn(false);
        $this->assertEquals(null, $this->authService->getUserByToken('wrongtoken'));
    }

    public function isSecretValidForUserProvider()
    {
        return [
            ['123abc-deputy', 'ROLE_LAY_DEPUTY', true],
            ['123abc-deputy', 'ROLE_ADMIN', false],
            ['123abc-deputy', 'OTHER_ROLE', false],
            ['123abc-deputy', null, false],
            ['123abc-admin', 'ROLE_LAY_DEPUTY', false],
            ['123abc-admin', 'ROLE_ADMIN', true],
            ['123abc-admin', 'OTHER_ROLE', false],
            ['123abc-admin', null, false],
            ['123abc-both', 'ROLE_LAY_DEPUTY', true],
            ['123abc-both', 'ROLE_ADMIN', true],
            ['123abc-both', 'OTHER_ROLE', false],
            ['123abc-both', null, false],
            ['123abc-deputyNoPermissions', '', false],
            ['123abc-deputyNoPermissions', null, false],
            ['123abc-deputyNoPermissions', false, false],
            ['123abc-deputyWrongFormat', '', false],
            [null, null, false],
        ];
    }

    /**
     * @dataProvider isSecretValidForUserProvider
     */
    public function testisSecretValidForUser($clientSecret, $role, $expectedResult)
    {
        $user = m::stub('AppBundle\Entity\User', [
                'getRoleName' => $role,
        ]);
        $request = new Request();
        $request->headers->set(AuthService::HEADER_CLIENT_SECRET, $clientSecret);

        $this->assertEquals($expectedResult, $this->authService->isSecretValidForUser($user, $request));
    }

    public function tearDown()
    {
        m::close();
    }
}
