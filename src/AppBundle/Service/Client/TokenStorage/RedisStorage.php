<?php

namespace AppBundle\Service\Client\TokenStorage;

use Predis\Client as PredisClient;

class RedisStorage implements TokenStorageInterface
{
    /**
     * @var PredisClient
     */
    private $redis;

    /**
     * @var string
     */
    private $prefix;

    public function __construct(PredisClient $redis, $prefix)
    {
        $this->redis = $redis;
        $this->prefix = $prefix;
    }

    public function get($id)
    {
        return $this->redis->get($this->prefix . $id);
    }

    public function set($id, $value)
    {
        return $this->redis->set($this->prefix . $id, $value);
    }

    public function remove($id)
    {
        $this->redis->set($this->prefix . $id, null);
        $this->redis->expire($this->prefix . $id, 0);
    }
}
