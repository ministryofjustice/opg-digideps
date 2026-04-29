<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service\Auth;

use OPG\Digideps\Backend\Entity\User;
use Psr\Log\LoggerInterface;
use OPG\Digideps\Backend\Repository\UserRepository;
use OPG\Digideps\Backend\Security\RedisUserProvider;
use Mockery\MockInterface;
use Tests\OPG\Digideps\Backend\Unit\MockeryStub as m;
use PHPUnit\Framework\TestCase;
use Predis\Client;

final class UserProviderTest extends TestCase
{
    private UserRepository&MockInterface $repo;
    private Client&MockInterface $redis;
    private LoggerInterface&MockInterface $logger;
    private RedisUserProvider $userProvider;

    public function setUp(): void
    {
        $this->repo = m::stub(UserRepository::class);
        $this->redis = m::stub(Client::class);
        $this->logger = m::stub(LoggerInterface::class);
        $options = ['timeout_seconds' => 7];

        $this->userProvider = new RedisUserProvider($this->redis, $this->logger, $options, $this->repo, 'testing');
    }

    public function testloadUserByUsernameRedisNotFound(): void
    {
        $this->redis->shouldReceive('get')->with('token')->andReturn(null);
        $this->logger->shouldReceive('warning')->with(\Mockery::pattern('/Token.*not.*found/'));
        $this->expectException(\RuntimeException::class);

        $this->userProvider->loadUserByUsername('token');
    }

    public function testloadUserByUsernameDbNotFound(): void
    {
        $this->redis->shouldReceive('get')->with('token')->andReturn(1);
        $this->repo->shouldReceive('find')->with(1)->andReturn(null);

        $this->logger->shouldReceive('warning')->with(\Mockery::pattern('/not found/'));
        $this->expectException(\RuntimeException::class);

        $this->userProvider->loadUserByUsername('token');
    }

    public function testloadUserByUsernameFound(): void
    {
        $user = m::mock(User::class);

        $this->redis->shouldReceive('get')->with('token')->andReturn(1);
        $this->redis->shouldReceive('expire')->with('token', 7)->once()->andReturn(1);
        $this->repo->shouldReceive('find')->with(1)->andReturn($user);

        $this->logger->shouldReceive('warning')->never();

        $this->assertEquals($user, $this->userProvider->loadUserByUsername('token'));
    }

    public function testsupportsClass(): void
    {
        $user = m::mock(User::class);

        $this->assertFalse($this->userProvider->supportsClass('not a user class'));
        $this->assertTrue($this->userProvider->supportsClass(get_class($user)));
        $this->assertTrue($this->userProvider->supportsClass(User::class));
    }

    public function testgenerateRandomTokenAndStore(): void
    {
        $user = m::stub(User::class, [
            'getId' => 123,
        ]);

        $tokenMatchPattern = '/^testing_123_[0-9a-f]{5,40}[\d]{1,}/';

        $this->redis->shouldReceive('set')->with(\Mockery::pattern($tokenMatchPattern), 123)->atLeast(1);
        $this->redis->shouldReceive('expire')->with(\Mockery::pattern($tokenMatchPattern), 7)->atLeast(1);

        $token = $this->userProvider->generateRandomTokenAndStore($user);
        $this->assertMatchesRegularExpression($tokenMatchPattern, $token);

        $token2 = $this->userProvider->generateRandomTokenAndStore($user);
        $this->assertNotEquals($token, $token2, 'token must generate a new value when called for the 2nd time');
    }

    public function testRemoveToken(): void
    {
        $this->redis->shouldReceive('set')->with('token', null)->once();

        $this->userProvider->removeToken('token');
    }

    public function tearDown(): void
    {
        m::close();
    }
}
