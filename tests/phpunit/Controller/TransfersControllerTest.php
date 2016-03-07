<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Mockery as m;

class TransfersControllerTest extends WebTestCase
{

    protected $restClient;
    protected $form;
    protected $formErrorsFormatter;

    /**
     * @var Client
     */
    protected $frameworkBundleClient;

    public function setUp()
    {
        $this->frameworkBundleClient = static::createClient(['environment' => 'test', 'debug' => true]);

        $this->restClient = m::mock('AppBundle\Service\Client\RestClient');

        static::$kernel->getContainer()->set('restClient', $this->restClient);
    }

    public function testTransfersGetJsonApiException()
    {
        $this->restClient
                ->shouldReceive('get')->with('report/1', 'array', [ 'query' => [ 'groups' => 'transfers']])
            ->andThrow(new \AppBundle\Exception\RestClientException('sth went wrong', 500));

        
        $this->frameworkBundleClient->request('GET', '/report/1/transfers', [], [], ['CONTENT_TYPE' => 'application/json', 'X-Requested-With'=>'XMLHttpRequest']);
        $response = $this->frameworkBundleClient->getResponse();
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $responseArray = json_decode($response->getContent(), 1);
        
        $this->assertEquals(['success'=>false, 'exception'=>'sth went wrong'], $responseArray);
    }
    public function testTransfersGetJson()
    {
        $this->restClient
                ->shouldReceive('get')->with('report/1', 'array', [ 'query' => [ 'groups' => 'transfers']])
            ->andReturn(['money_transfers'=>'test']);

        $this->frameworkBundleClient->request('GET', '/report/1/transfers', [], [], ['CONTENT_TYPE' => 'application/json', 'X-Requested-With'=>'XMLHttpRequest']);
        $response = $this->frameworkBundleClient->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $responseArray = json_decode($response->getContent(), 1);
        
        $this->assertEquals('test', $responseArray['transfers']);
    }
    
    

}
