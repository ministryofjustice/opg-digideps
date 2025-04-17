<?php

namespace App\Tests\Unit\Service\BruteForce;

use App\Service\BruteForce\AttemptsInTimeChecker;
use MockeryStub as m;
use PHPUnit\Framework\TestCase;

// create a simple predis Mock to just return keys

require_once __DIR__.'/PredisMock.php';

class AttemptsInTimeCheckerTest extends TestCase
{
    private PredisMock $redis;
    private AttemptsInTimeChecker $object;
    private string $key;

    public function setUp(): void
    {
        $this->redis = new PredisMock();
        $this->object = new AttemptsInTimeChecker($this->redis, 'prefix');
        $this->key = 'key';
    }

    public static function attempts()
    {
        return [
                [[], [0 => false, 100 => false, 1000 => false]],
                // 1 attempt in last 60 secs
                [[[1, 10]], [0 => true]],
                // 2 attempts in last 60 secs
                [[[2, 10]], [0 => false, 10 => true]],
                // as above with previous history of failures
                [[[2, 10]], [0 => false, 1 => true, 2 => true, 3 => true, 14 => false, 15 => true, 100 => false, 200 => false]],
                // two intervals
                [[[3, 60], [5, 120]],  [0 => false, 1 => false, 2 => true, 63 => false, 64 => true, 65 => true]],
            ];
    }

    /**
     * @dataProvider attempts
     */
    public function testMaxAttemptsReached(array $triggers, array $attemptsTimeStampToExpected)
    {
        foreach ($triggers as $trigger) {
            list($maxAttempts, $interval) = $trigger;
            $this->object->addTrigger($maxAttempts, $interval);
        }

        // 1st interval reached
        foreach ($attemptsTimeStampToExpected as $timestamp => $expected) {
            $this->assertEquals($expected, $this->object->registerAttempt($this->key, $timestamp)->maxAttemptsReached($this->key, $timestamp));
        }
    }

    public function testResetAttempts()
    {
        $this->object->addTrigger(1, 10);

        $this->assertTrue($this->object->registerAttempt($this->key)->maxAttemptsReached($this->key));
        $this->object->resetAttempts('wrong key');
        $this->assertTrue($this->object->maxAttemptsReached($this->key));

        $this->object->resetAttempts($this->key);
        $this->assertFalse($this->object->maxAttemptsReached($this->key));
    }

    public function tearDown(): void
    {
        m::close();
    }
}
