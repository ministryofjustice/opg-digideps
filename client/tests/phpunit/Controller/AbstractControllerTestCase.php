<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;

abstract class AbstractControllerTestCase extends WebTestCase
{
    /** @var Client */
    protected $client;

    public function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'unittest', 'debug' => false]);
    }

    protected function mockLoggedInUser(array $roleNames, User $user = null): void
    {
        $container = $this->client->getContainer();

        $roles = array_map(function () {
            return new Role('ROLE_ADMIN');
        }, $roleNames);

        $token = self::prophesize(TokenInterface::class);
        $token->getUser()->willReturn(is_null($user) ? new User() : $user);
        $token->serialize()->willReturn('');
        $token->isAuthenticated()->willReturn(true);
        $token->getRoles()->willReturn($roles);

        $tokenStorage = self::prophesize(TokenStorage::class);
        $tokenStorage->getToken()->willReturn($token);
        $tokenStorage->setToken(null)->willReturn();

        $container->set('security.token_storage', $tokenStorage->reveal());
    }
}
