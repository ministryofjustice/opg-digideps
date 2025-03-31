<?php

namespace App\Service;

use App\Service\Client\RestClient;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class DeputyProviderTest extends TestCase
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

    public function setUp(): void
    {
        $this->restClient = m::mock('App\Service\Client\RestClient');
        $this->logger = m::mock('Symfony\Bridge\Monolog\Logger');

        $this->object = new DeputyProvider($this->restClient, $this->logger);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testLogin()
    {
        $credentials = ['email' => 'Peter', 'password' => 'p'];

        $user = m::mock('App\Entity\User')
            ->shouldReceive('getId')->andReturn(1)
            ->getMock();
        $authToken = 'abc123';

        $this->restClient->shouldReceive('login')->once()->with($credentials)->andReturn([$user, $authToken]);
        $this->restClient->shouldReceive('setLoggedUserId')->once()->with(1);

        $this->logger->shouldReceive('info')->andReturnUsing(function ($e) {
            throw new \Exception($e);
        });

        $this->object->login($credentials);
    }

    public function testLoginFail()
    {
        $this->expectException(UserNotFoundException::class);

        $credentials = ['email' => 'Peter', 'password' => 'p'];

        $this->restClient->shouldReceive('login')->once()->with($credentials)->andThrow(new \Exception('e'));
        $this->logger->shouldReceive('info')->once();

        $this->object->login($credentials);
    }

    public function testLoadUserByIdentifier()
    {
        $mockUser = $this->createMock(UserInterface::class);

        $this->restClient->shouldReceive('setLoggedUserId')->with(1)->andReturn($this->restClient);
        $this->restClient->shouldReceive('get')
            ->with('user/1', 'User', m::any())
            ->andReturn($mockUser);

        $this->assertEquals($mockUser, $this->object->loadUserByIdentifier(1));
    }

    public function testSupportsClass()
    {
        $this->assertTrue($this->object->supportsClass('App\Entity\User'));
        $this->assertFalse($this->object->supportsClass('App\Entity\Report'));
    }

    public function tearDown(): void
    {
        m::close();
    }
}
