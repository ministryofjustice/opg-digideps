<?php

namespace App\Service\Availability;

use Symfony\Component\DependencyInjection\ContainerInterface;

class RedisAvailability extends ServiceAvailabilityAbstract
{
    const TEST_KEY = 'RedisAvailabilityTestKey';

    public function __construct(private ContainerInterface $container)
    {
        $this->isHealthy = false;
    }

    public function ping()
    {
        try {
            // get the redis service, save and read a key
            $redis = $this->container->get('snc_redis.default');
            $redis->set(self::TEST_KEY, 'valueSaved');

            if ($redis->get(self::TEST_KEY) == 'valueSaved') {
                $this->isHealthy = true;
            }
        } catch (\Throwable $e) {
            $this->errors = 'Redis Error: ' . $e->getMessage();
        }
    }

    public function getName()
    {
        return 'Redis';
    }
}
