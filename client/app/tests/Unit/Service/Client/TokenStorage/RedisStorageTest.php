<?php

namespace Tests\OPG\Digideps\Frontend\Unit\Service\Client\TokenStorage;

use OPG\Digideps\Frontend\Service\Client\TokenStorage\RedisStorage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Predis\Client;

class RedisStorageTest extends TestCase
{
    private Client&MockObject $redis;
    private string $prefix;
    private string $workspace;
    private RedisStorage $object;

    public function setUp(): void
    {
        $this->redis = $this->getMockBuilder(Client::class)->addMethods(['get', 'set'])->getMock();
        $this->prefix = 'prefix';
        $this->workspace = 'testing';

        $this->object = new RedisStorage($this->redis, $this->prefix, $this->workspace);
    }

    public function testGet(): void
    {
        $value = 'v';
        $id = 1;

        $this->redis->method('get')->with($this->workspace . '_' . $this->prefix . $id)->willReturn($value);

        $this->assertEquals($value, $this->object->get($id));
    }

    public function testSet(): void
    {
        $value = 'v';
        $returnValue = 'rv';
        $id = 1;

        $this->redis->method('set')->with($this->workspace . '_' . $this->prefix . $id, $value)->willReturn($returnValue);

        $this->assertEquals($returnValue, $this->object->set($id, $value));
    }
}
