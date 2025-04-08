<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Report\Report;
use App\Entity\User;
use App\Service\Client\RestClient;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class DeputyProviderTest extends TestCase
{
    private DeputyProvider $object;
    private RestClient $restClient;
    private Logger $logger;

    public function setUp(): void
    {
        $this->restClient = m::mock(RestClient::class);
        $this->logger = m::mock(Logger::class);

        $this->object = new DeputyProvider($this->restClient, $this->logger);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testLogin(): void
    {
        $credentials = ['email' => 'Peter', 'password' => 'p'];

        $user = m::mock(User::class)
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
        $mockUser = $this->createMock(User::class);

        $this->restClient->shouldReceive('setLoggedUserId')->with(1)->andReturn($this->restClient);
        $this->restClient->shouldReceive('get')->with('user/1', 'User', m::any())->andReturn($mockUser);

        $this->assertEquals($mockUser, $this->object->loadUserByIdentifier('1'));
    }

    public function testSupportsClass()
    {
        $this->assertTrue($this->object->supportsClass(User::class));
        $this->assertFalse($this->object->supportsClass(Report::class));
    }

    public function tearDown(): void
    {
        m::close();
    }
}
