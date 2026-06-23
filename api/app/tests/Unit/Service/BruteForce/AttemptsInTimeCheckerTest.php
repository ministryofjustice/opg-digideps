<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service\BruteForce;

use OPG\Digideps\Backend\Service\BruteForce\AttemptsInTimeChecker;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\OPG\Digideps\Backend\Unit\Service\PredisMock;

final class AttemptsInTimeCheckerTest extends TestCase
{
    private AttemptsInTimeChecker $sut;
    private string $key = 'key';

    public function setUp(): void
    {
        $redis = new PredisMock();
        $this->sut = new AttemptsInTimeChecker($redis, 'prefix');
    }

    public static function attempts(): array
    {
        return [
                [[], [0 => ['tooMany' => false, 'intervalMins' => 0], 100 => ['tooMany' => false, 'intervalMins' => 0]]],
                // 1 attempt in last 60 secs
                [[[1, 10]], [0 => ['tooMany' => true, 'intervalMins' => 10]]],
                // 2 attempts in last 60 secs
                [[[2, 10]], [0 => ['tooMany' => false, 'intervalMins' => 0], 10 => ['tooMany' => true, 'intervalMins' => 10]]],
                // as above with previous history of failures
                [[[2, 10]], [
                    0 => ['tooMany' => false, 'intervalMins' => 0],
                    1 => ['tooMany' => true, 'intervalMins' => 10],
                    2 => ['tooMany' => true, 'intervalMins' => 10],
                    3 => ['tooMany' => true, 'intervalMins' => 10],
                    14 => ['tooMany' => false, 'intervalMins' => 0],
                    15 => ['tooMany' => true, 'intervalMins' => 10],
                    100 => ['tooMany' => false, 'intervalMins' => 0],
                    200 => ['tooMany' => false, 'intervalMins' => 0]
                ]],
                // two intervals with differing criteria
                [[[3, 60], [5, 120]], [
                    0 => ['tooMany' => false, 'intervalMins' => 0],
                    1 => ['tooMany' => false, 'intervalMins' => 0],
                    2 => ['tooMany' => true, 'intervalMins' => 60],
                    63 => ['tooMany' => false, 'intervalMins' => 0],
                    64 => ['tooMany' => true, 'intervalMins' => 120],
                ]],
            ];
    }

    #[DataProvider('attempts')]
    public function testMaxAttemptsReached(array $triggers, array $attemptsTimeStampToExpected): void
    {
        /** @var array<int, int> $trigger */
        foreach ($triggers as $trigger) {
            list($maxAttempts, $interval) = $trigger;
            $this->sut->addTrigger($maxAttempts, $interval);
        }

        foreach ($attemptsTimeStampToExpected as $timestamp => $expected) {
            $actual = $this->sut->registerAttempt($this->key, $timestamp)->maxAttemptsReached($this->key, $timestamp);
            $this->assertEquals($expected, $actual);
        }
    }

    public function testResetAttempts(): void
    {
        $this->sut->addTrigger(1, 10);

        $this->assertTrue($this->sut->registerAttempt($this->key)->maxAttemptsReached($this->key)['tooMany']);

        $this->sut->resetAttempts('wrong key');
        $this->assertTrue($this->sut->maxAttemptsReached($this->key)['tooMany']);

        $this->sut->resetAttempts($this->key);
        $this->assertFalse($this->sut->maxAttemptsReached($this->key)['tooMany']);
    }
}
