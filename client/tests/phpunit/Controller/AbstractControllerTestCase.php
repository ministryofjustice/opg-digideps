<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Service\DeputyProvider;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Role\Role;

abstract class AbstractControllerTestCase extends WebTestCase
{
    /** @var Client */
    protected $client;

    public function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'unittest', 'debug' => false]);
        $this->client->disableReboot();
    }

    protected function mockLoggedInUser(array $roleNames, User $user = null): void
    {
        $container = $this->client->getContainer();

        if (is_null($user)) {
            $user = new User();
        }

        if (is_null($user->getId())) {
            $user->setId(1);
        }

        $roles = array_map(function () {
            return new Role('ROLE_ADMIN');
        }, $roleNames);

        $token = new UsernamePasswordToken($user, 'password', 'mock', $roles);

        $tokenStorage = self::prophesize(TokenStorage::class);
        $tokenStorage->getToken()->willReturn($token);
        $tokenStorage->setToken(Argument::cetera())->willReturn();

        $container->set('security.token_storage', $tokenStorage->reveal());
    }
}
