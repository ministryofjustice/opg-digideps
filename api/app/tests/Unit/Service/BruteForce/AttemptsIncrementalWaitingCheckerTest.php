<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service\BruteForce;

use OPG\Digideps\Backend\Service\BruteForce\AttemptsIncrementalWaitingChecker;
use PHPUnit\Framework\TestCase;

final class AttemptsIncrementalWaitingCheckerTest extends TestCase
{
    private PredisMock $redis;
    private AttemptsIncrementalWaitingChecker $object;
    private string $key;

    public function setUp(): void
    {
        $this->redis = new PredisMock();
        $this->object = new AttemptsIncrementalWaitingChecker($this->redis, 'prefix');
        $this->key = 'key';
    }

    public function testMaxAttemptsReached(): void
    {
        $this->object->overrideTimestamp(9990 + 0);

        $this->object->addFreezingRule(2, 10); // after 2 attempts, lock for 10 seconds
        $this->object->addFreezingRule(4, 35); // after 4 attempts, lock for 35 seconds

        $this->assertAccessible();
        $this->attempt();
        $this->assertAccessible();
        $this->attempt();
        $this->assertFrozen();
        $this->assertFrozen();

        $this->assertEquals(9990 + 10, $this->object->getUnfrozenAt($this->key));

        $this->object->overrideTimestamp(9990 + 20);
        $this->assertAccessible();

        $this->attempt();
        $this->attempt();
        $this->assertEquals(9990 + 20 + 35, $this->object->getUnfrozenAt($this->key));

        $this->assertFrozen();
        $this->assertFrozen();

        $this->object->overrideTimestamp(9990 + 20 + 35 + 1);

        $this->assertAccessible();
    }

    private function attempt(): void
    {
        $this->object->registerAttempt($this->key);
    }

    private function assertFrozen(): void
    {
        $this->assertTrue($this->object->isFrozen($this->key));
    }

    private function assertAccessible(): void
    {
        $this->assertFalse($this->object->isFrozen($this->key));
    }

    public function testResetAttempts(): void
    {
        $this->object->overrideTimestamp(0);

        $this->object->addFreezingRule(1, 10); // after 2 attempts, lock for 10 seconds
        $this->attempt();
        $this->assertFrozen();
        $this->object->resetAttempts($this->key);
        $this->assertAccessible();
    }
}
