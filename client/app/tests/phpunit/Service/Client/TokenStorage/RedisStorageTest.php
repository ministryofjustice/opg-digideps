<?php

namespace App\Service\Client\TokenStorage;

use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Predis\Client;

class RedisStorageTest extends TestCase
{
    private Client&MockInterface $redis;
    private string $prefix;
    private string $workspace;
    private RedisStorage $object;

    public function setUp(): void
    {
        $this->redis = m::mock(Client::class);
        $this->prefix = 'prefix';
        $this->workspace = 'testing';

        $this->object = new RedisStorage($this->redis, $this->prefix, $this->workspace);
    }

    public function testGet()
    {
        $value = 'v';
        $id = 1;

        $this->redis->shouldReceive('get')->with($this->workspace.'_'.$this->prefix.$id)->andReturn($value);

        $this->assertEquals($value, $this->object->get($id));
    }

    public function testSet()
    {
        $value = 'v';
        $returnValue = 'rv';
        $id = 1;

        $this->redis->shouldReceive('set')->with($this->workspace.'_'.$this->prefix.$id, $value)->andReturn($returnValue);

        $this->assertEquals($returnValue, $this->object->set($id, $value));
    }
}
