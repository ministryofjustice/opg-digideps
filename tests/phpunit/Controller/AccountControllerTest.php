<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Report\Account;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Mockery as m;

class AccountControllerTest extends WebTestCase
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
        $this->report = m::mock('AppBundle\Entity\Report\Report');
        $this->t1 = m::mock('AppBundle\Entity\Transaction')
                ->shouldReceive('getId')->andReturn(1)
                ->shouldReceive('getHasMoreDetails')->andReturn(false)
                ->shouldReceive('getType')->andReturn('type1')
                ->getMock();

        $this->form = m::mock('Symfony\Component\Form\FormInterface')
                ->shouldReceive('handleRequest')
                ->shouldReceive('getName')->andReturn('form')
                ->getMock();

        $this->formFactory = m::mock('Symfony\Component\Form\FormFactory')
                ->shouldReceive('create')->andReturn($this->form)
                ->getMock();

        $this->formErrorsFormatter = m::mock('AppBundle\Service\FormErrorsFormatter');

        static::$kernel->getContainer()->set('restClient', $this->restClient);
        static::$kernel->getContainer()->set('form.factory', $this->formFactory);
        static::$kernel->getContainer()->set('formErrorsFormatter', $this->formErrorsFormatter);
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
                ->shouldReceive('get')->withArgs(['report/1', 'Report\\Report', m::any()])
                ->andThrow($restClientException);

        $responseArray = $this->getArrayResponseFrom('/report/1/accounts/transactionsIn.json');
        $this->assertEquals(false, $responseArray['success']);
        $this->assertEquals(1002, $responseArray['errors']['errorCode']);
        $this->assertContains('API', $responseArray['errors']['errorDescription']);
    }

    public function testmoneySaveJsonWithReportAlreadySubmitted()
    {
        $this->report->shouldReceive('getSubmitted')->andReturn(true);

        $this->restClient
                ->shouldReceive('get')->withArgs(['report/1', 'Report\\Report', m::any()])
                ->andReturn($this->report);

        $responseArray = $this->getArrayResponseFrom('/report/1/accounts/transactionsIn.json');
        $this->assertEquals(false, $responseArray['success']);
        $this->assertEquals(1000, $responseArray['errors']['errorCode']);
        $this->assertContains('Unable to change submitted report', $responseArray['errors']['errorDescription']);
    }

    public function testmoneySaveJsonWithFormNotValid()
    {
        $this->t1->shouldReceive('getAmounts')->andReturn(['abc']);

        $this->report
                ->shouldReceive('getSubmitted')->andReturn(false)
                ->shouldReceive('getId')->andReturn(1)
                ->shouldReceive('getTransactionsIn')->andReturn([$this->t1]);

        $this->form
                ->shouldReceive('isValid')->andReturn(false)
                ->shouldReceive('getErrors')->andReturn(['error1', 'error2']);

        $this->formErrorsFormatter
                ->shouldReceive('toArray')->andReturn([]);

        $this->restClient
                ->shouldReceive('get')->withArgs(['report/1', 'Report\\Report', m::any()])
                ->andReturn($this->report);

        $responseArray = $this->getArrayResponseFrom('/report/1/accounts/transactionsIn.json');
        $this->assertEquals(false, $responseArray['success']);
        $this->assertEquals(1001, $responseArray['errors']['errorCode']);
    }

    public function testmoneySaveJsonExceptionOnApiPut()
    {
        $this->t1->shouldReceive('getAmounts')->andReturn([123], 34);

        $this->report
                ->shouldReceive('getSubmitted')->andReturn(false)
                ->shouldReceive('getId')->andReturn(1)
                ->shouldReceive('getTransactionsIn')->andReturn([$this->t1]);

        $this->form
                ->shouldReceive('isValid')->andReturn(true)
                ->shouldReceive('getData')->andReturn([]);

        $this->restClient
                ->shouldReceive('get')->withArgs(['report/1', 'Report\\Report', m::any()])->andReturn($this->report)
                ->shouldReceive('put')->withArgs(['report/1', m::any(), m::any()])->andThrow(new \AppBundle\Exception\RestClientException('put error', 1))
        ;

        $responseArray = $this->getArrayResponseFrom('/report/1/accounts/transactionsIn.json');

        $this->assertEquals(false, $responseArray['success']);
        $this->assertEquals(1002, $responseArray['errors']['errorCode']);
        $this->assertEquals('put error', $responseArray['errors']['errorDescription']);
    }

    public function testmoneySaveJsonSuccess()
    {
        $this->t1->shouldReceive('getAmount')->andReturn(123, 34);

        $this->report
                ->shouldReceive('getSubmitted')->andReturn(false)
                ->shouldReceive('getId')->andReturn(1)
                ->shouldReceive('getTransactionsIn')->andReturn([$this->t1]);

        $this->form
                ->shouldReceive('isValid')->andReturn(true)
                ->shouldReceive('getData')->andReturn([]);

        $this->restClient
                ->shouldReceive('get')->with('report/1', 'Report\\Report', m::any())->andReturn($this->report)
                ->shouldReceive('put')->with('report/1', m::any(), m::any())->andReturn(null);

        $responseArray = $this->getArrayResponseFrom('/report/1/accounts/transactionsIn.json');

        $this->assertEquals(true, $responseArray['success']);
    }

    private function getArrayResponseFrom($url)
    {
        $this->frameworkBundleClient->request('PUT', $url);
        $response = $this->frameworkBundleClient->getResponse();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);

        return json_decode($response->getContent(), 1);
    }

    public function testaccountsetClosingBalanceTest()
    {
        $account = new Account();

        // false case
        $this->assertFalse($account->isClosingBalanceZero());
        $this->assertFalse($account->setClosingBalance(0.01)->isClosingBalanceZero());

        $this->assertFalse($account->setClosingBalance(1.00)->isClosingBalanceZero());

        // true cases
        $this->assertTrue($account->setClosingBalance(0)->isClosingBalanceZero());
        $this->assertTrue($account->setClosingBalance(0.0)->isClosingBalanceZero());
        $this->assertTrue($account->setClosingBalance(0.00)->isClosingBalanceZero());
        $this->assertTrue($account->setClosingBalance(0.0001)->isClosingBalanceZero());
        $this->assertTrue($account->setClosingBalance('0')->isClosingBalanceZero());
        $this->assertTrue($account->setClosingBalance('0.0')->isClosingBalanceZero());
        $this->assertTrue($account->setClosingBalance('0.00')->isClosingBalanceZero());
    }
}
