<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Mockery as m;

class AccountControllerTest extends WebTestCase
{

    protected $restClient;

    /**
     * @var Client
     */
    protected $frameworkBundleClient;


    public function setUp()
    {
        $this->frameworkBundleClient = static::createClient(['environment' => 'test', 'debug' => true]);

        $this->restClient = m::mock('AppBundle\Service\Client\RestClient');
        $this->report = m::mock('AppBundle\Entity\Report');
        $this->t1 = m::mock('AppBundle\Entity\Transaction')
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('getHasMoreDetails')->andReturn(false)
            ->shouldReceive('getType')->andReturn('type1')
            
            ->getMock();
        
        
        static::$kernel->getContainer()->set('restClient', $this->restClient);
    }


    public function testmoneySaveJsonWithReportNotFound()
    {
        $restClientException = new \AppBundle\Exception\RestClientException('API returned an exception', 403, [
            'success' => false,
            'data' => '',
            'message' => 'Report does not belong to user',
            'stacktrace' => '...',
            'code' => 403,
        ]);
        $this->restClient
            ->shouldReceive('get')->withArgs(["report/1", "Report", m::any()])
            ->andThrow($restClientException);

        $responseArray = $this->getArrayReponseFrom('/report/1/accounts/transactionsIn.json');
        $this->assertEquals(false, $responseArray['success']);
        $this->assertEquals(1002, $responseArray['errors']['errorCode']);
        $this->assertContains('API', $responseArray['errors']['errorDescription']);
    }


    public function testmoneySaveJsonWithReportAlreadySubmitted()
    {
        $this->report->shouldReceive('getSubmitted')->andReturn(true);

        $this->restClient
            ->shouldReceive('get')->withArgs(["report/1", "Report", m::any()])
            ->andReturn($this->report);

        $responseArray = $this->getArrayReponseFrom('/report/1/accounts/transactionsIn.json');
        $this->assertEquals(false, $responseArray['success']);
        $this->assertEquals(1000, $responseArray['errors']['errorCode']);
        $this->assertContains('Unable to change submitted report', $responseArray['errors']['errorDescription']);
    }


    public function testmoneySaveJsonWithFormNotValid()
    {
        $this->t1->shouldReceive('getAmount')->andReturn('abc');
        
        $this->report
            ->shouldReceive('getSubmitted')->andReturn(false)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('getTransactionsIn')->andReturn([$this->t1]);

        $this->restClient
            ->shouldReceive('get')->withArgs(["report/1", "Report", m::any()])
            ->andReturn($this->report);

        $responseArray = $this->getArrayReponseFrom('/report/1/accounts/transactionsIn.json');
        $this->assertEquals(false, $responseArray['success']);
        $this->assertEquals(1002, $responseArray['errors']['errorCode']);
        $this->assertContains('Expected a numeric', $responseArray['errors']['errorDescription']);
    }


    public function testmoneySaveJsonExceptionOnApiPut()
    {
        $this->t1->shouldReceive('getAmount')->andReturn(123,34);
        
        $this->report
            ->shouldReceive('getSubmitted')->andReturn(false)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('getTransactionsIn')->andReturn([$this->t1]);

        $this->restClient
            ->shouldReceive('get')->withArgs(["report/1", "Report", m::any()])
            ->andReturn($this->report);

        $responseArray = $this->getArrayReponseFrom('/report/1/accounts/transactionsIn.json');
        $this->fail("form return validation error but not clear how to pass data"); 



        $this->assertEquals(false, $responseArray['success']);
        $this->assertEquals(1002, $responseArray['errors']['errorCode']);
        $this->assertContains('Expected a numeric', $responseArray['errors']['errorDescription']);
    }


    public function testmoneySaveJsonSuccess()
    {
        $this->markTestIncomplete();
    }


    private function getArrayReponseFrom($url)
    {
        $this->frameworkBundleClient->request("PUT", $url);
        $response = $this->frameworkBundleClient->getResponse();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        return json_decode($response->getContent(), 1);
    }

}