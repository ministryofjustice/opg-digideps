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
     * @return EntityDir\Client
     */
    public function createClient()
    {
        // add clent, cot, report, needed for assets
        $client = new EntityDir\Client;
        $client->setEmail('temp@temp.com');
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
    
    public function flush($e = null)
    {
        $this->em->flush($e);
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