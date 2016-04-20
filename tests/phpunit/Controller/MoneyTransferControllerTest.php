<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Mockery as m;

class MoneyTransfersControllerTest extends AbstractControllerTestCase
{

    protected $restClient;
    protected $form;
    protected $formErrorsFormatter;


    public function testTransfersSaveJsonApiException()
    {
        $this->restClient
                ->shouldReceive('post')->with('report/1/money-transfers', ['account_from_id'=>1,'account_to_id'=>2,'amount'=>3])
            ->andThrow(new \AppBundle\Exception\RestClientException('sth went wrong', 500));

        
        $response = $this->ajaxRequest('POST', 'report/1/transfers', ['account' => [1, 2], 'amount' => 3]);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $responseArray = json_decode($response->getContent(), 1);

        $this->assertEquals(['success'=>false, 'exception'=>'sth went wrong'], $responseArray);
    }


    public function testTransfersSaveJson()
    {
        $this->restClient
                ->shouldReceive('post')->with('report/1/money-transfers', ['account_from_id'=>1,'account_to_id'=>2,'amount'=>3])
            ->andReturn(7);

        $this->frameworkBundleClient->request('POST', '/report/1/transfers', ['account' => [1, 2], 'amount' => 3], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X-Requested-With'=>'XMLHttpRequest']);
        $response = $this->frameworkBundleClient->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $responseArray = json_decode($response->getContent(), 1);
        
        $this->assertEquals(7, $responseArray['transferId']);
        $this->assertEquals(1, $responseArray['success']);
    }

    public function testTransfersUpdateJsonApiException()
    {
        $this->restClient
                ->shouldReceive('put')->with('report/1/money-transfers/2', ['account_from_id'=>1,'account_to_id'=>2,'amount'=>3])
            ->andThrow(new \AppBundle\Exception\RestClientException('sth went wrong', 500));


        $this->frameworkBundleClient->request('PUT', 'report/1/transfers', ['id'=>2, 'account' => [1, 2], 'amount' => 3], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X-Requested-With'=>'XMLHttpRequest']);
        $response = $this->frameworkBundleClient->getResponse();
        $this->assertEquals(500, $response->getStatusCode(), $response->getContent());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $responseArray = json_decode($response->getContent(), 1);
        
        $this->assertEquals(['success'=>false, 'exception'=>'sth went wrong'], $responseArray);
    }


    public function testTransfersUpdateJson()
    {
        $this->restClient
                ->shouldReceive('put')->with('report/1/money-transfers/2', ['account_from_id'=>1,'account_to_id'=>2,'amount'=>3])
            ->andReturn('data-returned');

        $this->frameworkBundleClient->request('PUT', '/report/1/transfers', ['id'=>2, 'account' => [1, 2], 'amount' => 3], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X-Requested-With'=>'XMLHttpRequest']);
        $response = $this->frameworkBundleClient->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $responseArray = json_decode($response->getContent(), 1);
       
        $this->assertEquals(1, $responseArray['success']);
    }

    public function testTransfersDeleteJsonApiException()
    {
        $this->restClient
                ->shouldReceive('delete')->with('report/1/money-transfers/2')
            ->andThrow(new \AppBundle\Exception\RestClientException('sth went wrong', 500));


        $this->frameworkBundleClient->request('DELETE', 'report/1/transfers', ['id'=>2], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X-Requested-With'=>'XMLHttpRequest']);
        $response = $this->frameworkBundleClient->getResponse();
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response, $response->getContent());
        $responseArray = json_decode($response->getContent(), 1);

        $this->assertEquals(['success'=>false, 'exception'=>'sth went wrong'], $responseArray);
    }


    public function testTransfersDeleteJson()
    {
        $this->restClient
                ->shouldReceive('delete')->with('report/1/money-transfers/2')
            ->andReturn('data-returned');

        $this->frameworkBundleClient->request('DELETE', '/report/1/transfers', ['id'=>2], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X-Requested-With'=>'XMLHttpRequest']);
        $response = $this->frameworkBundleClient->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response, $response->getContent());
        $responseArray = json_decode($response->getContent(), 1);
       
        $this->assertEquals(1, $responseArray['success']);
    }

}
