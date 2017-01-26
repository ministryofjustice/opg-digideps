<?php

namespace AppBundle\Service;

use Symfony\Bridge\Monolog\Logger;
use AppBundle\Service\Client\RestClient;
use Mockery as m;

class DeputyProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DeputyProvider
     */
    private $object;

    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var Logger
     */
    private $logger;

    public function setUp()
    {
        $this->restClient = m::mock('AppBundle\Service\Client\RestClient');
        $this->logger = m::mock('Symfony\Bridge\Monolog\Logger');

        $this->object = new DeputyProvider($this->restClient, $this->logger);
    }

    public function testLogin()
    {
        $credentials = ['email' => 'Peter', 'password' => 'p'];

        $user = m::mock('AppBundle\Entity\User')
            ->shouldReceive('getId')->andReturn(1)
            ->getMock();

        $this->restClient->shouldReceive('login')->once()->with($credentials)->andReturn($user);
        $this->restClient->shouldReceive('setLoggedUserId')->once()->with(1);

        $this->logger->shouldReceive('info')->andReturnUsing(function ($e) {
            throw new \Exception($e);
        });

        $this->object->login($credentials);
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testLoginFail()
    {
        $credentials = ['email' => 'Peter', 'password' => 'p'];

        $this->restClient->shouldReceive('login')->once()->with($credentials)->andThrow(new \Exception('e'));
        $this->logger->shouldReceive('info')->once();

        $this->object->login($credentials);
    }

    public function testLoadUserByUsername()
    {
        $this->restClient->shouldReceive('setLoggedUserId')->with(1)->andReturn($this->restClient);
        $this->restClient->shouldReceive('get')->with('user/1', 'User', ['user', 'role', 'user-login'])->andReturn('user');

        $this->assertEquals('user', $this->object->LoadUserByUsername(1));
    }

    public function testSupportsClass()
    {
        $this->assertTrue($this->object->supportsClass('AppBundle\Entity\User'));
        $this->assertFalse($this->object->supportsClass('AppBundle\Entity\Report'));
    }

    public function tearDown()
    {
        m::close();
    }
}
