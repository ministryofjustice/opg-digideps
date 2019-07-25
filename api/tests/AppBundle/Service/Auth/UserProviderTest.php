<?php

namespace Tests\AppBundle\Service\Auth;

use AppBundle\Service\Auth\UserProvider;
use MockeryStub as m;

class UserProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UserProvider
     */
    private $userProvider;

    public function setUp()
    {
        $this->repo = m::stub('Doctrine\ORM\EntityRepository');
        $this->em = m::stub('Doctrine\ORM\EntityManager', [
                'getRepository(AppBundle\Entity\User)' => $this->repo,
        ]);
        $this->redis = m::stub('Predis\Client');
        $this->logger = m::stub('Symfony\Bridge\Monolog\Logger');
        $options = ['timeout_seconds' => 7];

        $this->userProvider = new UserProvider($this->em, $this->redis, $this->logger, $options);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testloadUserByUsernameRedisNotFound()
    {
        $this->redis->shouldReceive('get')->with('token')->andReturn(null);
        $this->logger->shouldReceive('warning')->with(matchesPattern('/Token.*not.*found/'));

        $this->userProvider->loadUserByUsername('token');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testloadUserByUsernameDbNotFound()
    {
        $this->redis->shouldReceive('get')->with('token')->andReturn(1);
        $this->repo->shouldReceive('find')->with(1)->andReturn(null);

        $this->logger->shouldReceive('warning')->with(matchesPattern('/not found/'));

        $this->userProvider->loadUserByUsername('token');
    }

    public function testloadUserByUsernameFound()
    {
        $user = m::mock('AppBundle\Entity\User');

        $this->redis->shouldReceive('get')->with('token')->andReturn(1);
        $this->redis->shouldReceive('expire')->with('token', 7)->once()->andReturn(1);
        $this->repo->shouldReceive('find')->with(1)->andReturn($user);

        $this->logger->shouldReceive('warning')->never();

        $this->assertEquals($user, $this->userProvider->loadUserByUsername('token'));
    }

    public function testsupportsClass()
    {
        $user = m::mock('AppBundle\Entity\User');

        $this->assertFalse($this->userProvider->supportsClass('not a user class'));
        $this->assertTrue($this->userProvider->supportsClass(get_class($user)));
        $this->assertTrue($this->userProvider->supportsClass('AppBundle\Entity\User'));
    }

    public function testgenerateRandomTokenAndStore()
    {
        $user = m::stub('AppBundle\Entity\User', [
            'getId' => 123,
        ]);

        $tokenMatchPattern = '/^123_' . '[0-9a-f]{5,40}' . '[\d]{1,}/';

        $this->redis->shouldReceive('set')->with(matchesPattern($tokenMatchPattern), 123)->atLeast(1);
        $this->redis->shouldReceive('expire')->with(matchesPattern($tokenMatchPattern), 7)->atLeast(1);

        $token = $this->userProvider->generateRandomTokenAndStore($user);
        $this->assertRegExp($tokenMatchPattern, $token);

        $token2 = $this->userProvider->generateRandomTokenAndStore($user);
        $this->assertNotEquals($token, $token2, 'token must generate a new value when called for the 2nd time');
    }

    public function testremoveToken()
    {
        $this->redis->shouldReceive('set')->with('token', null)->once()->andReturn('redisReturn');

        $this->assertEquals('redisReturn', $this->userProvider->removeToken('token'));
    }

    public function tearDown()
    {
        m::close();
    }
}
