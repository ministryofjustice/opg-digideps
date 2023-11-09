<?php

namespace App\Service\Client\TokenStorage;

use Predis\ClientInterface as PredisClientInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class RedisStorage extends TokenStorage
{
    public function __construct(
        private PredisClientInterface $redis,
        private string $sessionPrefix,
        private string $workspace
    ) {
    }

    public function get($id)
    {
        return $this->redis->get($this->workspace.'_'.$this->sessionPrefix.$id);
    }

    public function set($id, $value)
    {
        return $this->redis->set($this->workspace.'_'.$this->sessionPrefix.$id, $value);
    }

    public function remove($id)
    {
        $this->redis->set($this->workspace.'_'.$this->sessionPrefix.$id, null);
        $this->redis->expire($this->workspace.'_'.$this->sessionPrefix.$id, 0);
    }
}
