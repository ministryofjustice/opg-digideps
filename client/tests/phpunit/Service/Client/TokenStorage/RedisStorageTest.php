<?php

namespace AppBundle\Service\Client\TokenStorage;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Predis\Client;

class RedisStorageTest extends TestCase
{
    /**
     * @var RedisStorage
     */
    private $object;

    /**
     * @var Client
     */
    private $redis;

    public function setUp(): void
    {
        $this->redis = m::mock(Client::class);
        $this->prefix = 'prefix';

        $this->object = new RedisStorage($this->redis, $this->prefix);
    }

    public function testGet()
    {
        $value = 'v';
        $id = 1;

        $this->redis->shouldReceive('get')->with($this->prefix . $id)->andReturn($value);

        $this->assertEquals($value, $this->object->get($id));
    }

    public function testSet()
    {
        $value = 'v';
        $returnValue = 'rv';
        $id = 1;

        $this->redis->shouldReceive('set')->with($this->prefix . $id, $value)->andReturn($returnValue);

        $this->assertEquals($returnValue, $this->object->set($id, $value));
    }
}
