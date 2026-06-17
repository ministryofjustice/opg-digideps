<?php

namespace OPG\Digideps\Backend\Service\BruteForce;

use Predis\Client as PredisClient;

class AttemptsInTimeChecker
{
    /** @var array<array<int, int>> */
    private array $triggers;

    public function __construct(
        private readonly PredisClient $redis,
        private readonly string $workspace,
        private ?string $redisPrefix = ''
    ) {
        $this->triggers = [];
    }

    public function setRedisPrefix(?string $redisPrefix = ''): static
    {
        $this->redisPrefix = $this->workspace . '_' . $redisPrefix;

        return $this;
    }

    public function addTrigger(int $maxAttempts, int $interval): static
    {
        if (!$maxAttempts || !$interval) {
            throw new \InvalidArgumentException('Invalid trigger value');
        }
        $this->triggers[] = [$maxAttempts, $interval];

        return $this;
    }

    /**
     * @return array{tooMany: bool, intervalMins: int}
     * intervalMins is always 0 if tooMany is false: the time interval is irrelevant as the login was within
     * all of the acceptable limits
     */
    public function maxAttemptsReached(string $key, ?int $timestamp = null): array
    {
        $currentTimestamp = ($timestamp === null) ? time() : $timestamp;

        $id = $this->keyToRedisId($key);
        $history = $this->redis->get($id) ? json_decode($this->redis->get($id), true) : [];

        foreach ($this->triggers as $trigger) {
            list($maxAttempts, $timeInterval) = $trigger;

            $attemptsInInterval = count(array_filter($history, function ($attemptTimeStamp) use ($currentTimestamp, $timeInterval): bool {
                return $attemptTimeStamp >= $currentTimestamp - $timeInterval;
            }));

            if ($attemptsInInterval >= $maxAttempts) {
                return ['tooMany' => true, 'intervalMins' => $timeInterval];
            }
        }

        return ['tooMany' => false, 'intervalMins' => 0];
    }

    public function registerAttempt(string $key, ?int $timestamp = null): static
    {
        $id = $this->keyToRedisId($key);
        $history = $this->redis->get($id) ? json_decode($this->redis->get($id), true) : [];

        $history[] = ($timestamp === null ? time() : $timestamp);

        $this->redis->set($id, json_encode($history));
        $this->redis->expire($id, 86400);

        return $this;
    }

    public function resetAttempts(string $key): void
    {
        $id = $this->keyToRedisId($key);

        $this->redis->set($id, null);
    }

    private function keyToRedisId(string $key): string
    {
        return $this->redisPrefix . $key;
    }
}
