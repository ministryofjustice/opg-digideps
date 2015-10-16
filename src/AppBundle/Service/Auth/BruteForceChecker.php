<?php

namespace AppBundle\Service\Auth;

//use Symfony\Component\DependencyInjection\Container;
//use Symfony\Component\HttpFoundation\Request;
//use Doctrine\ORM\EntityRepository;
//use AppBundle\Entity\User;
//use Symfony\Bridge\Monolog\Logger;

use Predis\Client as PredisClient;

class BruteForceChecker
{
    /**
     * @var PredisClient 
     */
    private $redis;
    
    /**
     * @var array
     */
    private $options;
    
    const KEY = 'bruteForce';
    
    public function __construct(PredisClient $redis, $options)
    {
        $this->redis = $redis;
        
        $this->options['max_attempts_email'] = empty($options['max_attempts_email']) 
                ? null : $options['max_attempts_email'];
    }

    
    public function resetAll()
    {
        $this->redis->set(self::KEY, '');
        $this->redis->expire(self::KEY, 0);
    }
    
    public function resetAttacksByEmail($email)
    {
        $attempts = json_decode($this->redis->get(self::KEY), true) ?: [];
        $attempts[$email] = null;
        $this->redis->set(self::KEY, json_encode($attempts));
    }


    public function isAllowed($email, $password)
    {
        $mt = $this->options['max_attempts_email'];
        if (!$mt) {
            return;
        }
        
        $attempts = json_decode($this->redis->get(self::KEY), true) ?: [];
        
        $attempts[$email] = isset($attempts[$email]) ? $attempts[$email]+1 : 1;
        
        $this->redis->set(self::KEY, json_encode($attempts));
        
        if ($attempts[$email] <= $mt) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }


}