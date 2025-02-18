<?php

namespace App\Tests\Integration\Service\Auth;

use App\Repository\UserRepository;
use App\Security\RedisUserProvider;
use MockeryStub as m;
use PHPUnit\Framework\TestCase;

class UserProviderTest extends TestCase
{
    /**
     * @var RedisUserProvider
     */
    private $userProvider;

    public function setUp(): void
    {
        $this->repo = m::stub(UserRepository::class);
        $this->em = m::stub('Doctrine\ORM\EntityManager', [
            'getRepository(App\Entity\User)' => $this->repo,
        ]);
        $this->redis = m::stub('Predis\Client');
        $this->logger = m::stub('Symfony\Bridge\Monolog\Logger');
        $options = ['timeout_seconds' => 7];

        $this->userProvider = new RedisUserProvider($this->em, $this->redis, $this->logger, $options, $this->repo, 'testing');
    }

    public function testloadUserByUsernameRedisNotFound()
    {
        $this->redis->shouldReceive('get')->with('token')->andReturn(null);
        $this->logger->shouldReceive('warning')->with(\Mockery::pattern('/Token.*not.*found/'));
        $this->expectException(\RuntimeException::class);

        $this->userProvider->loadUserByUsername('token');
    }

    public function testloadUserByUsernameDbNotFound()
    {
        $this->redis->shouldReceive('get')->with('token')->andReturn(1);
        $this->repo->shouldReceive('find')->with(1)->andReturn(null);

        $this->logger->shouldReceive('warning')->with(\Mockery::pattern('/not found/'));
        $this->expectException(\RuntimeException::class);

        $this->userProvider->loadUserByUsername('token');
    }

    public function testloadUserByUsernameFound()
    {
        $user = m::mock('App\Entity\User');

        $this->redis->shouldReceive('get')->with('token')->andReturn(1);
        $this->redis->shouldReceive('expire')->with('token', 7)->once()->andReturn(1);
        $this->repo->shouldReceive('find')->with(1)->andReturn($user);

        $this->logger->shouldReceive('warning')->never();

        $this->assertEquals($user, $this->userProvider->loadUserByUsername('token'));
    }

    public function testsupportsClass()
    {
        $user = m::mock('App\Entity\User');

        $this->assertFalse($this->userProvider->supportsClass('not a user class'));
        $this->assertTrue($this->userProvider->supportsClass(get_class($user)));
        $this->assertTrue($this->userProvider->supportsClass('App\Entity\User'));
    }

    public function testgenerateRandomTokenAndStore()
    {
        $user = m::stub('App\Entity\User', [
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

    public function testremoveToken()
    {
        $this->redis->shouldReceive('set')->with('token', null)->once()->andReturn('redisReturn');

        $this->assertEquals('redisReturn', $this->userProvider->removeToken('token'));
    }

    public function tearDown(): void
    {
        m::close();
    }
}
