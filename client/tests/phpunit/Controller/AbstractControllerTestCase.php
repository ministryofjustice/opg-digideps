<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\EventDispatcher\ObservableEventDispatcher;
use AppBundle\Service\Client\RestClient;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Role\Role;

abstract class AbstractControllerTestCase extends WebTestCase
{
    /** @var Client */
    protected $client;

    /** @var RestClient|ObjectProphecy */
    protected $restClient;

    public function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'unit_test_old', 'debug' => false]);
        $this->client->disableReboot();

        $this->restClient = $this->injectProphecyService(RestClient::class, function () {
        }, ['rest_client']);
    }

    protected function logInAs(string $roleName)
    {
        $session = $this->client->getContainer()->get('session');

        $firewallName = 'secured_area';
        // if you don't define multiple connected firewalls, the context defaults to the firewall name
        // See https://symfony.com/doc/current/reference/configuration/security.html#firewall-context
        $firewallContext = 'secured_area';

        // you may need to use a different token class depending on your application.
        // for example, when using Guard authentication you must instantiate PostAuthenticationGuardToken
        $user = (new User())->setRoleName($roleName);
        $token = new UsernamePasswordToken($user, null, $firewallName, [$roleName]);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    /**
     * Create a prophet for a Symfony service and overwrite it in the client container
     */
    protected function injectProphecyService(string $className, callable $callback = null, array $aliases = []): ObjectProphecy
    {
        /** @var Container $container */
        $container = $this->client->getContainer();

        $prophet = self::prophesize($className);
        $container->set($className, $prophet->reveal());

        foreach ($aliases as $alias) {
            $container->set($alias, $prophet->reveal());
        }

        if (is_callable($callback)) {
            call_user_func($callback, $prophet);
        }

        return $prophet;
    }

    /**
     * Provide the services necessary to mock the currently logged in user
     */
    protected function mockLoggedInUser(array $roleNames, User $user = null): User
    {
        if (is_null($user)) {
            $user = (new User())
                ->setFirstname('Test')
                ->setLastname('User')
                ->setRoleName($roleNames[0])
                ->setEmail('logged-in-user@email.com')
                ->setPhoneMain('01213214321')
                ->setAddress1('1 Fake Road')
                ->setAddressPostcode('B31 1PP')
                ->setAddressCountry('GB');
        }

        if (is_null($user->getId())) {
            $user->setId(1);
        }

        $roles = array_map(function ($roleName) {
            return new Role($roleName);
        }, $roleNames);

        $token = new UsernamePasswordToken($user, 'password', 'mock', $roles);

        // Mock token storage to return our fake token
        $this->injectProphecyService(TokenStorage::class, function ($tokenStorage) use ($token) {
            $tokenStorage->getToken()->willReturn($token);
            $tokenStorage->setToken(Argument::cetera())->willReturn();
        }, ['security.token_storage']);

        // Respond to calls to hydrate user details from API
        $this->restClient->setLoggedUserId(1)->willReturn($this->restClient->reveal());
        $this->restClient->get('user/1', Argument::cetera())->willReturn($user);

        return $user;
    }
}
