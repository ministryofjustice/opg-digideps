<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Service\Client\TokenStorage;

use Predis\ClientInterface as PredisClientInterface;
use Predis\Connection\ConnectionException;
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

    public function get($id): ?string
    {
        return $this->executeWithRetry(fn () => $this->redis->get($this->sessionPrefix . $id));
    }

    public function set($id, $value): void
    {
        $this->executeWithRetry(fn () => $this->redis->set($this->sessionPrefix . $id, $value));
    }

    public function remove($id): void
    {
        $this->executeWithRetry(function () use ($id) {
            $this->redis->set($this->sessionPrefix . $id, null);
            $this->redis->expire($this->sessionPrefix . $id, 0);
        });
    }

    private function executeWithRetry(callable $operation, int $maxRetries = 3)
    {
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                return $operation();
            } catch (ConnectionException $e) {
                $attempt++;

                if ($attempt >= $maxRetries) {
                    throw new \RuntimeException("Operation failed after {$maxRetries} retries.", 0, $e);
                }

                usleep(1000 * 1000); // Delay in microseconds
            }
        }
    }
}
