<?php

namespace App\Service\Availability;

use Predis\ClientInterface;

class RedisAvailability extends ServiceAvailabilityAbstract
{
    public const TEST_KEY = 'RedisAvailabilityTestKey';
    private ClientInterface $redis;
    private string $workspace;

    public function __construct(ClientInterface $redis, $workspace)
    {
        $this->isHealthy = false;
        $this->redis = $redis;
        $this->workspace = $workspace;
    }

    public function ping()
    {
        try {
            $this->redis->set($this->workspace.'_'.self::TEST_KEY, 'valueSaved');

            if ('valueSaved' == $this->redis->get($this->workspace.'_'.self::TEST_KEY)) {
                $this->isHealthy = true;
            }
        } catch (\Throwable $e) {
            $this->errors = 'Redis Error: '.$e->getMessage();
        }
    }

    public function getName()
    {
        return 'Redis';
    }
}
