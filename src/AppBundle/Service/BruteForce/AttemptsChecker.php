<?php

namespace AppBundle\Service\BruteForce;

use Predis\Client as PredisClient;

class AttemptsChecker
{
    /**
     * @var PredisClient 
     */
    private $redis;

    /**
     * @var array 
     */
    private $triggers;


    public function __construct(PredisClient $redis, $prefix, $key, array $triggers)
    {
        $this->redis = $redis;
        $this->key = $key;
        $this->triggers = $triggers;
        $this->prefix = $prefix.md5(__CLASS__);
    }


    public function maxAttemptsReached($currentTimestamp = null)
    {
        $currentTimestamp = (null === $currentTimestamp) ? time() : $currentTimestamp;

        $id = $this->prefix . $this->key;
        $history = $this->redis->get($id) ? json_decode($this->redis->get($id), true) : [];

        foreach ($this->triggers as $maxAttempts => $timeInterval) { // 3 60
            $attemptsInInterval = count(array_filter($history, function($attemptTimeStamp) use ($currentTimestamp, $timeInterval) {
                return $attemptTimeStamp >= $currentTimestamp - $timeInterval;
            }));
            if ($attemptsInInterval >= $maxAttempts) {
                return true;
            }
        }
        return false;
    }


    public function registerAttempt($timestamp = null)
    {
        $id = $this->prefix . $this->key;
        $history = $this->redis->get($id) ? json_decode($this->redis->get($id), true) : [];

        $history[] = (null === $timestamp ? time() : $timestamp);

        $this->redis->set($id, json_encode($history));

        return $this;
    }


    public function resetAttempts()
    {
        $id = $this->prefix . $this->key;

        $this->redis->set($id, null);
    }

}