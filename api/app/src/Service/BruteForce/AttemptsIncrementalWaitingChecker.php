<?php

namespace App\Service\BruteForce;

use Predis\Client as PredisClient;

class AttemptsIncrementalWaitingChecker
{
    /**
     * @var array
     */
    private $freezeRules;

    private $timeOffset;

    private array $secondsBeforeNextAttempt;

    /**
     * @param mixed[] $prefix
     * @param string $workspace
     */
    public function __construct(private readonly PredisClient $redis, private $workspace, private $redisPrefix = null)
    {
        $this->freezeRules = [];
        $this->secondsBeforeNextAttempt = [];
    }

    public function setRedisPrefix($redisPrefix)
    {
        $this->redisPrefix = $this->workspace.'_'.$redisPrefix;

        return $this;
    }

    public function addFreezingRule($maxAttempts, $freezeFor)
    {
        if (!$maxAttempts || !$freezeFor) {
            throw new \InvalidArgumentException(__METHOD__.' : Invalid values');
        }
        $this->freezeRules[] = [$maxAttempts, $freezeFor];

        return $this;
    }

    public function isFrozen($key)
    {
        $id = $this->keyToRedisId($key);
        $data = $this->redis->get($id) ? json_decode($this->redis->get($id), true) : [];

        if (empty($data['freezeUntil'])) {
            return false;
        }

        return $this->getTimestamp() <= $data['freezeUntil'];
    }

    public function getUnfrozenAt($key)
    {
        $id = $this->keyToRedisId($key);
        $data = $this->redis->get($id) ? json_decode($this->redis->get($id), true) : [];

        return isset($data['freezeUntil']) ? $data['freezeUntil'] : null;
    }

    public function registerAttempt($key)
    {
        $timestamp = $this->getTimestamp();
        $id = $this->keyToRedisId($key);
        $data = $this->redis->get($id) ? json_decode($this->redis->get($id), true) : [];

        $data['totalAttempts'] = isset($data['totalAttempts']) ? ($data['totalAttempts'] + 1) : 1;
        $data['lastAttemptTimestamp'] = $timestamp;

        foreach ($this->freezeRules as $rule) {
            list($maxAttempts, $freezeFor) = $rule;
            if ($maxAttempts == $data['totalAttempts']) {
                $data['freezeUntil'] = $timestamp + $freezeFor;
                $this->secondsBeforeNextAttempt[$key] = $freezeFor;
            }
        }

        $this->redis->set($id, json_encode($data));
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
        return $this->redisPrefix.$key;
    }

    public function getTimestamp()
    {
        return (null === $this->timeOffset) ? time() : $this->timeOffset;
    }

    /**
     * For testing reasons.
     *
     * @param int $timestamp
     */
    public function overrideTimestamp($timestamp)
    {
        $this->timeOffset = $timestamp;
    }
}
