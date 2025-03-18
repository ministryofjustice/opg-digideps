<?php

declare(strict_types=1);

namespace App\Service\Client\TokenStorage;

use Predis\ClientInterface as PredisClientInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;


/**
 * Unless we actually implement something other than directly interacting with Predis, 
 * we should ignore unit tests for this classs
 *
 * @codeCoverageIgnore
 */
class RedisStorage extends TokenStorage
{
    public function __construct(
        private readonly PredisClientInterface $redis,
        private readonly string $sessionPrefix
    ) {
    }

    public function get($id)
    {
        return $this->redis->get($this->sessionPrefix.$id);
    }

    public function set($id, $value)
    {
        return $this->redis->set($this->sessionPrefix.$id, $value);
    }

    public function remove($id)
    {
        $this->redis->set($this->sessionPrefix.$id, null);
        $this->redis->expire($this->sessionPrefix.$id, 0);
    }
}
