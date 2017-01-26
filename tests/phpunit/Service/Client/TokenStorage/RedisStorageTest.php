<?php

namespace AppBundle\Service\Client\TokenStorage;

use Mockery as m;
use Predis\Client as PredisClient;

class RedisStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RedisStorage
     */
    private $object;

    /**
     * @var PredisClient
     */
    private $redis;

    public function __construct()
    {
        $this->redis = m::mock('Predis\Client');
        $this->prefix = 'prefix';

        $this->object = new RedisStorage($this->redis, $this->prefix);
    }

    public function testGet()
    {
        $value = 'v';
        $id = 1;

        $this->redis->shouldReceive('get')->with($this->prefix.$id)->andReturn($value);

        $this->assertEquals($value, $this->object->get($id));
    }

    public function testSet()
    {
        $value = 'v';
        $returnValue = 'rv';
        $id = 1;

        $this->redis->shouldReceive('set')->with($this->prefix.$id, $value)->andReturn($returnValue);

        $this->assertEquals($returnValue, $this->object->set($id, $value));
    }
}
