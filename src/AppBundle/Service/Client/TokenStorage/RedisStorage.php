<?php
namespace AppBundle\Service\Client\TokenStorage;

use Predis\Client as PredisClient;

class RedisStorage implements TokenStorageInterface
{
    /**
     * @var PredisClient 
     */
    private $redis;
    
    /**
     * @var string 
     */
    private $id;
    
    public function __construct(PredisClient $redis, $id)
    {
        $this->redis = $redis;
        $this->id = $id;
    }

    public function get()
    {
        return $this->redis->get($this->id);
    }


    public function set($value)
    {
        return $this->redis->set($this->id, $value);
    }
    
}