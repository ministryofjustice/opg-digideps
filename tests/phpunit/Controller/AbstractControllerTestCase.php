<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Mockery as m;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractControllerTestCase extends WebTestCase
{
    protected $report;
    protected $client;
    protected $restClient;

    /**
     * @var Client
     */
    protected $frameworkBundleClient;

    public function setUp()
    {
        $this->frameworkBundleClient = static::createClient(['environment' => 'test', 'debug' => true]);

        $this->report = m::mock('AppBundle\Entity\Report\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('getDecisions')->andReturn([])
            ->shouldReceive('getSubmitted')->andReturn(false)
            ->shouldReceive('getClient')->andReturn(1)
            ->shouldReceive('getReasonForNoDecisions')->andReturn('')
            ->getMock();

        $this->client = m::mock('AppBundle\Entity\Client')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->getMock();

        $this->restClient = m::mock('AppBundle\Service\Client\RestClient')
            ->shouldReceive('get')->withArgs(['report/1', 'Report\\Report', m::any()])->andReturn($this->report)
            ->shouldReceive('get')->withArgs(['client/1', 'Client', m::any()])->andReturn($this->client)
            ->getMock();

        static::$kernel->getContainer()->set('rest_client', $this->restClient);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array  $parameters
     * @param array  $files
     * @param array  $server
     * 
     * @return Response
     */
    protected function ajaxRequest($method, $uri, array $parameters = array(), array $files = array(), array $server = array())
    {
        $this->frameworkBundleClient->request($method, $uri, $parameters, $files, ['CONTENT_TYPE' => 'application/json', 'HTTP_X-Requested-With' => 'XMLHttpRequest'] + $server);

        return $this->frameworkBundleClient->getResponse();
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array  $parameters
     * @param array  $files
     * @param array  $server
     *
     * @return Response
     */
    protected function httpRequest($method, $uri, array $parameters = array(), array $files = array(), array $server = array())
    {
        $this->frameworkBundleClient->request($method, $uri, $parameters, $files, $server);

        return $this->frameworkBundleClient->getResponse();
    }
}
