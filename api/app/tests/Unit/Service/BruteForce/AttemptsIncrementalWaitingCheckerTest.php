<?php

namespace App\Tests\Unit\Service\BruteForce;

use App\Service\BruteForce\AttemptsIncrementalWaitingChecker;
use MockeryStub as m;
use PHPUnit\Framework\TestCase;

// create a simple predis Mock to just return keys

require_once __DIR__.'/PredisMock.php';

class AttemptsIncrementalWaitingCheckerTest extends TestCase
{
    /**
     * @var AttemptsInTime
     */
    private $object;

    public function setUp(): void
    {
        $this->redis = new PredisMock();
        $this->object = new AttemptsIncrementalWaitingChecker($this->redis, 'prefix');
        $this->key = 'key';
    }

    //    public static function attempts()
    //    {
    //        return [
    //                [ [], [0=>false, 100=>false, 1000=>false]  ],
    //                // 1 attempt in last 60 secs
    //                [ [[1,10]], [0=>true]  ],
    //                // 2 attempts in last 60 secs
    //                [ [[2,10]], [0=>false, 10=>true]  ],
    //                // as above with previous history of failures
    //                [ [[2,10]], [0=>false, 1=>true, 2=>true, 3=>true, 14=>false, 15=>true, 100=>false, 200=>false]],
    //                // two intervals
    //                [ [[3, 60], [5, 120]],  [0=>false, 1=>false, 2=>true, 63=>false, 64=>true, 65=>true]],
    //            ];
    //    }
    //

    public function testMaxAttemptsReached()
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

    private function attempt()
    {
        $this->object->registerAttempt($this->key);
    }

    private function assertFrozen()
    {
        $this->assertTrue($this->object->isFrozen($this->key));
    }

    private function assertAccessible()
    {
        $this->assertFalse($this->object->isFrozen($this->key));
    }

    public function testResetAttempts()
    {
        $this->object->overrideTimestamp(0);

        $this->object->addFreezingRule(1, 10); // after 2 attempts, lock for 10 seconds
        $this->attempt();
        $this->assertFrozen();
        $this->object->resetAttempts($this->key);
        $this->assertAccessible();
    }

    public function tearDown(): void
    {
        m::close();
    }
}
