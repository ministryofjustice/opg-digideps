<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service\Auth;

use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Repository\UserRepository;
use OPG\Digideps\Backend\Security\RedisUserProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tests\OPG\Digideps\Backend\Unit\Service\BruteForce\PredisMock;

final class UserProviderTest extends TestCase
{
    private UserRepository&MockObject $repo;
    private PredisMock $redis;
    private LoggerInterface&MockObject $logger;
    private RedisUserProvider $userProvider;

    public function setUp(): void
    {
        $this->repo = $this->createMock(UserRepository::class);
        $this->redis = new PredisMock();
        $this->logger = $this->createMock(LoggerInterface::class);
        $options = ['timeout_seconds' => 7];

        $this->userProvider = new RedisUserProvider($this->redis, $this->logger, $options, $this->repo, 'testing');
    }

    public function testloadUserByUsernameRedisNotFound(): void
    {
        // 'token' not in store, so get() returns null
        $this->expectException(\RuntimeException::class);

        $this->userProvider->loadUserByUsername('token');
    }

    public function testloadUserByUsernameDbNotFound(): void
    {
        $this->redis->set('token', 1);
        $this->repo->method('find')->with(1)->willReturn(null);
        $this->logger->expects($this->once())->method('warning')->with($this->matchesRegularExpression('/not found/'));
        $this->expectException(\RuntimeException::class);

        $this->userProvider->loadUserByUsername('token');
    }

    public function testloadUserByUsernameFound(): void
    {
        $user = $this->createMock(User::class);
        $this->redis->set('token', 1);
        $this->repo->method('find')->with(1)->willReturn($user);
        $this->logger->expects($this->never())->method('warning');

        $this->assertEquals($user, $this->userProvider->loadUserByUsername('token'));

        $expireCalls = array_filter($this->redis->calls, fn($c) => $c[0] === 'expire' && $c[1] === 'token' && $c[2] === 7);
        $this->assertCount(1, $expireCalls, 'expire should be called once with the token and timeout');
    }

    public function testsupportsClass(): void
    {
        $this->assertFalse($this->userProvider->supportsClass('not a user class'));
        $this->assertTrue($this->userProvider->supportsClass(User::class));
    }

    public function testgenerateRandomTokenAndStore(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(123);

        $tokenMatchPattern = '/^testing_123_[0-9a-f]{5,40}[\d]{1,}/';

        $token = $this->userProvider->generateRandomTokenAndStore($user);
        $this->assertMatchesRegularExpression($tokenMatchPattern, $token);

        $token2 = $this->userProvider->generateRandomTokenAndStore($user);
        $this->assertNotEquals($token, $token2, 'token must generate a new value when called for the 2nd time');

        $setCalls = array_filter($this->redis->calls, fn($c) => $c[0] === 'set');
        $this->assertGreaterThanOrEqual(1, count($setCalls));

        $expireCalls = array_filter($this->redis->calls, fn($c) => $c[0] === 'expire');
        $this->assertGreaterThanOrEqual(1, count($expireCalls));
    }

    public function testRemoveToken(): void
    {
        $this->userProvider->removeToken('token');

        $setCalls = array_filter($this->redis->calls, fn($c) => $c[0] === 'set' && $c[1] === 'token' && $c[2] === null);
        $this->assertCount(1, $setCalls);
    }
}
