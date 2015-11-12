<?php
namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

use Mockery as m;

abstract class ControllerTestCase extends WebTestCase {

    protected $report;
    protected $client;
    protected $restClient;

    /**
     * @var Client
     */
    protected $frameworkBundleClient;

    public function setUp()
    {
        $this->frameworkBundleClient = static::createClient(['environment' => 'test','debug' => true]);

        $this->report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('getDecisions')->andReturn([])
            ->shouldReceive('getSubmitted')->andReturn(false)
            ->shouldReceive('getClient')->andReturn(1)
            ->shouldReceive('getReasonForNoDecisions')->andReturn("")
            ->getMock();

        $this->client = m::mock('AppBundle\Entity\Client')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->getMock();

        $this->restClient = m::mock('AppBundle\Service\Client\RestClient')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('get')->withArgs(["report/1", "Report",[ 'query' => [ 'groups' => [ 'transactions', 'basic']]]])->andReturn($this->report)

            ->shouldReceive('get')->withArgs(['client/1', 'Client', [ 'query' => [ 'groups' => [ "basic"]]]])->andReturn($this->client)
            ->getMock();

        static::$kernel->getContainer()->set('restClient', $this->restClient);

    }
    
    
}
