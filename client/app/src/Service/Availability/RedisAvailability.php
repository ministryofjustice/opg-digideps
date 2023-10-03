<?php

namespace App\Service\Availability;

use Predis\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RedisAvailability extends ServiceAvailabilityAbstract
{
    const TEST_KEY = 'RedisAvailabilityTestKey';

    private ContainerInterface $container;
    private ClientInterface $redis;

    public function __construct(ContainerInterface $container, ClientInterface $redis)
    {
        $this->isHealthy = false;
        $this->container = $container;
        $this->redis = $redis;
    }

    public function ping()
    {
        try {
            $this->redis->set(self::TEST_KEY, 'valueSaved');

            if ('valueSaved' == $this->redis->get(self::TEST_KEY)) {
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
