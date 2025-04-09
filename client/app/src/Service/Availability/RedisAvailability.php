<?php

namespace App\Service\Availability;

use Predis\ClientInterface;

class RedisAvailability extends ServiceAvailabilityAbstract
{
    public const TEST_KEY = 'RedisAvailabilityTestKey';

    public function __construct(
        private readonly ClientInterface $redis,
        private readonly string $workspace
    ) {
        $this->isHealthy = false;
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
