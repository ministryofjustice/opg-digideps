<?php

namespace App\Service\Client\TokenStorage;

use Predis\ClientInterface as PredisClientInterface;

class RedisStorage implements TokenStorageInterface
{
    /**
     * @var PredisClientInterface
     */
    private $redis;

    /**
     * @var string
     */
    private $prefix;

    public function __construct(PredisClientInterface $redis, string $prefix)
    {
        $this->redis = $redis;
        $this->prefix = $prefix;
    }

    public function get($id)
    {
        return $this->redis->get($this->prefix.$id);
    }

    public function set($id, $value)
    {
        return $this->redis->set($this->prefix.$id, $value);
    }

    public function remove($id)
    {
        $this->redis->set($this->prefix.$id, null);
        $this->redis->expire($this->prefix.$id, 0);
    }
}
