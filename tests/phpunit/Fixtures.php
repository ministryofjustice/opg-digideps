<?php

use AppBundle\Entity as EntityDir;
use Doctrine\ORM\EntityManager;

/**
 * Used for unit testing
 */
class Fixtures
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
        $user = new EntityDir\User;
        $user->setEmail('temp'.time().'@temp.com');
        $user->setPassword('temp@temp.com');
        $user->setFirstname('name'.time());
        $user->setLastname('surname'.time());
        foreach ($settersMap as $k=>$v) {
            $user->$k($v);
        }
        
        $this->em->persist($user);
        
        return $user;
    }

    /**
     * @return EntityDir\Client
     */
    public function createClient(EntityDir\User $user, array $settersMap = [])
    {
        // add clent, cot, report, needed for assets
        $client = new EntityDir\Client;
        $client->setEmail('temp@temp.com');
        foreach ($settersMap as $k=>$v) {
            $client->$k($v);
        }
        
        $user->addClient($client);
        
        $this->em->persist($client);
        
        return $client;
    }
    
    /**
     * @return EntityDir\Report
     */
    public function createReport(EntityDir\Client $client, array $settersMap = [])
    {
        $cot = new EntityDir\CourtOrderType;
        $cot->setName('test');
        $this->em->persist($cot);
        
        $report = new EntityDir\Report;
        $report->setClient($client);
        $report->setCourtOrderType($cot);
        foreach ($settersMap as $k=>$v) {
            $report->$k($v);
        }
        
        $this->em->persist($report);
        
        return $report;
    }
    
    /**
     * @return EntityDir\Account
     */
    public function createAccount(EntityDir\Report $report, array $settersMap = [])
    {
        $ret = new EntityDir\Account;
        $ret->setReport($report);
        $ret->setAccountNumber('1234')
            ->setBank('hsbc')
            ->setSortCode('101010');
        
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
        
        return $this;
    }
    
    public function persist()
    {
        foreach (func_get_args() as $e) {
            $this->em->persist($e);
        }
        
        return $this;
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