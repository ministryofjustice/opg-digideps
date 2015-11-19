<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Service\Mailer\MailSenderMock;

class ReportControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $report1;
    private static $deputy2;
    private static $client2;
    private static $report2;
    private static $tokenAdmin = null;
    private static $tokenDeputy = null;


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');

        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);
        self::fixtures()->flush();

        self::$report1 = self::fixtures()->createReport(self::$client1);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2);
        self::$report2 = self::fixtures()->createReport(self::$client2);

        self::fixtures()->flush()->clear();
    }
    
    /**
     * clear fixtures 
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        
        self::fixtures()->clear();
    }


    public function setUp()
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }
    }


    public function testAddAuth()
    {
        $url = '/report';
        $this->assertEndpointNeedsAuth('POST', $url);

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
    }


    public function testAddAcl()
    {
        $url = '/report';
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenDeputy, [
            'id' => self::$report2->getId()
        ]);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenDeputy, [
            'client' => self::$client2->getId()
        ]);
    }

    private $fixedData = [
            'court_order_type' => 1,
            'start_date' => '2015-01-01',
            'end_date' => '2015-12-31',
        ];

    public function testAdd()
    {
        $url = '/report';
        
        $reportId = $this->assertJsonRequest('POST', $url, [
                'mustSucceed' => true,
                'AuthToken' => self::$tokenDeputy,
                'data' => ['client' => self::$client1->getId()] + $this->fixedData
        ])['data']['report'];

        self::fixtures()->clear();

        // assert creation
        $report = self::fixtures()->getRepo('Report')->find($reportId); /* @var $report \AppBundle\Entity\Report */
        $this->assertEquals(self::$client1->getId(), $report->getClient()->getId());
        $this->assertEquals('2015-01-01', $report->getStartDate()->format('Y-m-d'));
        $this->assertEquals('2015-12-31', $report->getEndDate()->format('Y-m-d'));

        $transactionTypesCount = count(self::fixtures()->getRepo('TransactionType')->findAll());
        $this->assertTrue($transactionTypesCount > 1, 'transaction type not added');

        // assert transactions have been added
        $this->assertCount($transactionTypesCount, $report->getTransactions());
        $this->assertEquals(null, $report->getTransactions()[0]->getAmount());

    }
    
    public function testEdit()
    {
        $url = '/report';

        //POST but passes ID in the request
        $reportId = $this->assertJsonRequest('POST', $url, [
                'mustSucceed' => true,
                'AuthToken' => self::$tokenDeputy,
                'data' => ['id' => self::$report1->getId()] + $this->fixedData
        ])['data']['report'];

        self::fixtures()->clear();

        // assert account created with transactions
        $report = self::fixtures()->getRepo('Report')->find($reportId); /* @var $report \AppBundle\Entity\Report */
        $this->assertEquals(self::$client1->getId(), $report->getClient()->getId());
        $this->assertEquals('2015-01-01', $report->getStartDate()->format('Y-m-d'));
        $this->assertEquals('2015-12-31', $report->getEndDate()->format('Y-m-d'));
    }


    public function testGetByIdAuth()
    {
        $url = '/report/' . self::$report1->getId();
        $this->assertEndpointNeedsAuth('GET', $url);

        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }


    public function testGetByIdAcl()
    {
        $url2 = '/report/' . self::$report2->getId();

        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }


    /**
     * @depends testAdd
     * @depends testEdit
     */
    public function testGetById()
    {
        $url = '/report/' . self::$report1->getId();

        // assert get groups=basic
        $data = $this->assertJsonRequest('GET', $url, [
                'mustSucceed' => true,
                'AuthToken' => self::$tokenDeputy,
            ])['data'];
        $this->assertArrayHasKey('contacts', $data);
        $this->assertArrayHasKey('accounts', $data);
        $this->assertArrayHasKey('decisions', $data);
        $this->assertArrayHasKey('assets', $data);
        $this->assertArrayHasKey('court_order_type', $data);
        $this->assertArrayHasKey('report_seen', $data);
        $this->assertArrayNotHasKey('transactions', $data);
        $this->assertEquals(self::$report1->getId(), $data['id']);
        $this->assertEquals(self::$client1->getId(), $data['client']);
        $this->assertEquals('2015-01-01', $data['start_date']);
        $this->assertEquals('2015-12-31', $data['end_date']);


        //  assert get groups=transactions
        $data = $this->assertJsonRequest('GET', $url . '?groups=transactions', [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];
        $this->assertCount(62, $data['transactions']);
        $this->assertArrayHasKey('money_in_total', $data);
        $this->assertArrayHasKey('money_out_total', $data);
        $this->assertArrayHasKey('money_total', $data);
        $first = array_shift($data['transactions']);
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('type', $first);
        $this->assertArrayHasKey('category', $first);
        $this->assertArrayHasKey('has_more_details', $first);

        // both
        $q = http_build_query(['groups'=>['transactions','basic']]);
        //assert both groups (quick)
        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];
        $this->assertCount(62, $data['transactions']);
        $this->assertArrayHasKey('start_date', $data);
        $this->assertArrayHasKey('end_date', $data);
    }

    public function testSubmitAuth()
    {
        $url = '/report/' . self::$report1->getId() . '/submit';
        
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }
    
    public function testSubmitAcl()
    {
        $url2 = '/report/' . self::$report2->getId() . '/submit';
        
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);
    }

    public function testSubmitNotAllAgree()
    {
        MailSenderMock::resetessagesSent();
        $this->assertEquals(false, self::$report1->getSubmitted());

        $reportId = self::$report1->getId();
        $url = '/report/' . $reportId . '/submit';

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'submit_date' => '2015-12-30',
                'reason_not_all_agreed' => 'dont agree reason'
            ]
        ]);

        // assert account created with transactions
        $report = self::fixtures()->clear()->getRepo('Report')->find($reportId); /* @var $report \AppBundle\Entity\Report */
        $this->assertEquals(true, $report->getSubmitted());
        $this->assertEquals(false, $report->isAllAgreed());
        $this->assertEquals('dont agree reason', $report->getReasonNotAllAgreed());

    }

    public function testSubmit()
    {
        MailSenderMock::resetessagesSent();
        $this->assertEquals(false, self::$report1->getSubmitted());

        $reportId = self::$report1->getId();
        $url = '/report/' . $reportId . '/submit';

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'submit_date' => '2015-12-30'
            ]
        ]);

        // assert account created with transactions
        $report = self::fixtures()->clear()->getRepo('Report')->find($reportId); /* @var $report \AppBundle\Entity\Report */
        $this->assertEquals(true, $report->getSubmitted());
        $this->assertEquals(true, $report->isAllAgreed());

        // todo put back in test for submit date

    }

    public function testUpdateAuth()
    {
        $url = '/report/' . self::$report1->getId();

        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    public function testUpdateAcl()
    {
        $url2 = '/report/' . self::$report2->getId();

        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);
    }

    public function testUpdate()
    {
        $reportId = self::$report1->getId();
        $url = '/report/' . $reportId;

        // assert get
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'start_date' => '2015-01-29',
                'end_date' =>  '2015-12-29',
                'transactionsIn' => [
                    ['id'=>'dividends', 'amount'=>1200, 'more_details'=>''],
                    ['id'=>'income-from-investments', 'amount'=>760],
                ],
                'transactionsOut' => [
                    ['id'=>'cash-withdrawn', 'amount'=>24, 'more_details'=>'to pay bills'],
                ]
            ]
        ]);

        $report = self::fixtures()->clear()->getRepo('Report')->find($reportId); /* @var $report \AppBundle\Entity\Report */
        $this->assertEquals('2015-01-29', $report->getStartDate()->format('Y-m-d'));
        $this->assertEquals('2015-12-29', $report->getEndDate()->format('Y-m-d'));

        // assert transactions changes
        $tDividend = $report->getTransactionByTypeId('dividends');
        $this->assertInstanceOf('AppBundle\Entity\TransactionTypeIn', $tDividend->getTransactionType());
        $this->assertEquals(1200, $tDividend->getAmount());
        $this->assertEquals('', $tDividend->getMoreDetails());

        $tIfd = $report->getTransactionByTypeId('income-from-investments');
        $this->assertInstanceOf('AppBundle\Entity\TransactionTypeIn', $tIfd->getTransactionType());
        $this->assertEquals(760, $tIfd->getAmount());
        $this->assertEquals('', $tIfd->getMoreDetails());

        $tCashW = $report->getTransactionByTypeId('cash-withdrawn');
        $this->assertInstanceOf('AppBundle\Entity\TransactionTypeOut', $tCashW->getTransactionType());
        $this->assertEquals(24, $tCashW->getAmount());
        $this->assertEquals('to pay bills', $tCashW->getMoreDetails());

        $tGifts = $report->getTransactionByTypeId('gifts');
        $this->assertEquals(null, $tGifts->getAmount());
    }

}