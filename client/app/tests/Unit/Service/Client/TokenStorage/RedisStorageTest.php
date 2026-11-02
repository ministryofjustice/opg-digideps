<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\Client\TokenStorage;

use OPG\Digideps\Frontend\Service\Client\TokenStorage\RedisStorage;
use PHPUnit\Framework\TestCase;
use Predis\Client;

class RedisStorageTest extends TestCase
{
    public function testGet(): void
    {
        $redis = $this->getMockBuilder(Client::class)->getMock();
        $prefix = 'prefix';
        $workspace = 'testing';

        $object = new RedisStorage($redis, $prefix, $workspace);
        $value = 'v';
        $id = 1;

        $redis->expects($this->once())->method('__call')->with('get', [$workspace . '_' . $prefix . $id])->willReturn($value);

        self::assertEquals($value, $object->get($id));
    }

    public function testSet(): void
    {
        $redis = $this->getMockBuilder(Client::class)->getMock();
        $prefix = 'prefix';
        $workspace = 'testing';

        $object = new RedisStorage($redis, $prefix, $workspace);
        $value = 'v';
        $returnValue = 'rv';
        $id = 1;

        $redis->expects($this->once())->method('__call')->with('set', [$workspace . '_' . $prefix . $id, $value])->willReturn($returnValue);

        self::assertEquals($returnValue, $object->set($id, $value));
    }
}
