<?php

namespace App\Service\Client\TokenStorage;

use Predis\ClientInterface as PredisClientInterface;

class RedisStorage implements TokenStorageInterface
{
    public function __construct(private PredisClientInterface $redis, private string $prefix)
    {
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
