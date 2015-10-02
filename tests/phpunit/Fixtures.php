<?php

use AppBundle\Entity as EntityDir;
use Doctrine\ORM\EntityManager;

/**
 * Used for unit testing
 */
class Fixtures extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    private $em;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return EntityDir\User
     */
    public function createUser(array $settersMap = [])
    {
        // add clent, cot, report, needed for assets
        $ret = new EntityDir\User;
        $ret->setEmail('temp@temp.com');
        $ret->setPassword('temp@temp.com');
        $ret->setFirstname('name');
        $ret->setLastname('surname');
        foreach ($settersMap as $k=>$v) {
            $ret->$k($v);
        }
        $this->em->persist($ret);
        
        return $ret;
    }

    /**
     * @return EntityDir\Client
     */
    public function createClient(array $settersMap = [])
    {
        // add clent, cot, report, needed for assets
        $ret = new EntityDir\Client;
        $ret->setEmail('temp@temp.com');
        foreach ($settersMap as $k=>$v) {
            $ret->$k($v);
        }
        $this->em->persist($ret);
        
        return $ret;
    }
    
    /**
     * @return EntityDir\Report
     */
    public function createReport(EntityDir\Client $client, array $settersMap = [])
    {
        $cot = new EntityDir\CourtOrderType;
        $cot->setName('test');
        $this->em->persist($cot);
        
        $ret = new EntityDir\Report;
        $ret->setClient($client);
        $ret->setCourtOrderType($cot);
        foreach ($settersMap as $k=>$v) {
            $ret->$k($v);
        }
        $this->em->persist($ret);
        
        return $ret;
    }
    
    public function flush()
    {
        $args = func_get_args();
        if (empty($args)) {
            $this->em->flush();
        }
        
        foreach ($args as $e) {
            $this->em->flush($e);
        }
    }
    
    public function persist()
    {
        foreach (func_get_args() as $e) {
            $this->em->persist($e);
        }
    }
    
    public function clear()
    {
        $this->em->clear();
        
        return $this;
    }
    
    public function getRepo($entity)
    {
         return $this->em->getRepository("AppBundle\\Entity\\{$entity}");
    }
    
}