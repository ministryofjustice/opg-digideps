<?php

namespace AppBundle\Service\BruteForce;

use Predis\Client as PredisClient;

class AttemptsCounter
{
    const PREFIX = 'bf_';

    /**
     * @var PredisClient 
     */
    private $redis;

    /**
     * @var string
     */
    private $key;


    public function __construct(PredisClient $redis)
    {
        $this->redis = $redis;
    }


    public function reachedWarning()
    {
        $id = self::PREFIX . $this->key;
        $currentValue = $this->redis->get($id);

        return $currentValue > $this->warningTriggers[$this->key];
    }


    public function registerAttempt($key)
    {
        $id = self::PREFIX . $key;
        $currentValue = $this->redis->get($id) ? : 0;
        $this->redis->set($id, $currentValue + 1);

        return $this;
    }


    public function resetAttempts()
    {
        $id = self::PREFIX . $this->key;
        $this->redis->set($id, []);
    }



    public function addTrigger($key, $attempts)
    {
        $this->warningTriggers[$key] = $attempts;
    }


    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

}