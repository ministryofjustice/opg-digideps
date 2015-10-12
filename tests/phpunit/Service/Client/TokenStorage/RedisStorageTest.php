<?php
namespace AppBundle\Service\Client\TokenStorage;

use Predis\Client as PredisClient;
use Mockery as m;

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
        $this->id = 'id';
        
        $this->object = new RedisStorage($this->redis, $this->id);
    }

    public function testGet()
    {
        $value = 'v';
        
        $this->redis->shouldReceive('get')->with($this->id)->andReturn($value);
        
        $this->assertEquals($value, $this->object->get());
    }


    public function testSet()
    {
        $value = 'v';
        $returnValue = 'rv';
        
        $this->redis->shouldReceive('set')->with($this->id, $value)->andReturn($returnValue);
        
        $this->assertEquals($returnValue, $this->object->set($value));
    }
    
}