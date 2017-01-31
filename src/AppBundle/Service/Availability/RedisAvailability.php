<?php

namespace AppBundle\Service\Availability;

use Symfony\Component\DependencyInjection\ContainerInterface;

class RedisAvailability extends ServiceAvailabilityAbstract
{
    const TEST_KEY = 'RedisAvailabilityTestKey';

    public function __construct(ContainerInterface $container)
    {
        $this->isHealthy = false;

        try {
            // get the redis service, save and read a key
            $redis = $container->get('snc_redis.default');
            $redis->set(self::TEST_KEY, 'valueSaved');

            if ($redis->get(self::TEST_KEY) == 'valueSaved') {
                $this->isHealthy = true;
            }
        } catch (\Exception $e) {
            $this->errors = 'Redis Error: ' . $e->getMessage();
        }
    }

    public function getName()
    {
        return 'Redis';
    }
}
