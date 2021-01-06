<?php

namespace AppBundle\Service\BruteForce;

use Predis\Client as PredisClient;

class AttemptsInTimeChecker
{
    /**
     * @var PredisClient
     */
    private $redis;

    /**
     * @var array
     */
    private $triggers;

    /**
     * @var array
     */
    private $redisPrefix;

    public function __construct(PredisClient $redis, $prefix = null)
    {
        $this->redis = $redis;
        $this->redisPrefix = $prefix;
        $this->triggers = [];
    }

    public function setRedisPrefix($redisPrefix)
    {
        $this->redisPrefix = $redisPrefix;

        return $this;
    }

    public function addTrigger($maxAttempts, $interval)
    {
        if (!$maxAttempts || !$interval) {
            throw new \InvalidArgumentException('Invalid trigger value');
        }
        $this->triggers[] = [$maxAttempts, $interval];

        return $this;
    }

    public function maxAttemptsReached($key, $timestamp = null)
    {
        $currentTimestamp = (null === $timestamp) ? time() : $timestamp;

        $id = $this->keyToRedisId($key);
        $history = $this->redis->get($id) ? json_decode($this->redis->get($id), true) : [];

        foreach ($this->triggers as $trigger) {
            list($maxAttempts, $timeInterval) = $trigger;

            $attemptsInInterval = count(array_filter($history, function ($attemptTimeStamp) use ($currentTimestamp, $timeInterval) {
                return $attemptTimeStamp >= $currentTimestamp - $timeInterval;
            }));
            if ($attemptsInInterval >= $maxAttempts) {
                return true;
            }
        }

        return false;
    }

    public function registerAttempt($key, $timestamp = null)
    {
        $id = $this->keyToRedisId($key);
        $history = $this->redis->get($id) ? json_decode($this->redis->get($id), true) : [];

        $history[] = (null === $timestamp ? time() : $timestamp);

        $this->redis->set($id, json_encode($history));
        $this->redis->expire($id, 86400);

        return $this;
    }

    public function resetAttempts($key)
    {
        $id = $this->keyToRedisId($key);

        $this->redis->set($id, null);
    }

    private function keyToRedisId($key)
    {
        return $this->redisPrefix . $key;
    }
}
