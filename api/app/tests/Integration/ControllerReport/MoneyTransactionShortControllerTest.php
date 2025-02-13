<?php

namespace App\Tests\Unit\ControllerReport;

use App\Entity\Report\MoneyTransactionShort;
use App\Entity\Report\MoneyTransactionShortIn;
use App\Entity\Report\MoneyTransactionShortOut;
use App\Entity\Report\Report;
use app\tests\Integration\Controller\AbstractTestController;

class MoneyTransactionShortControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $transaction1;
    private static $transaction2;
    private static $transaction3;
    private static $report1;
    private static $deputy2;
    private static $report2;
    private static $tokenAdmin;
    private static $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');

        $client1 = self::fixtures()->createClient(self::$deputy1);
        self::fixtures()->flush();

        self::$report1 = self::fixtures()->createReport($client1);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        $client2 = self::fixtures()->createClient(self::$deputy2);
        self::$report2 = self::fixtures()->createReport($client2);

        // transactions. 2 in, 1 out. one out for report 2
        self::$transaction1 = $t1 = new MoneyTransactionShortIn(self::$report1);
        $t1->setAmount(123.45)->setDescription('d1')->setDate(new \DateTime('2015-12-31'));
        self::$transaction2 = $t2 = new MoneyTransactionShortIn(self::$report1);
        $t2->setAmount(789.12)->setDescription('d2');
        self::$transaction3 = $t3 = new MoneyTransactionShortOut(self::$report1);
        $t3->setAmount(5000.59)->setDescription('d3');
        $t4 = new MoneyTransactionShortIn(self::$report2);
        $t4->setAmount(123)->setDescription('belongs to report2');
        self::fixtures()->persist($t1, $t2, $t3, $t4);

        self::fixtures()->flush()->clear();

        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function testGetTransactions()
    {
        $url = '/report/'.self::$report1->getId()
            .'?'.http_build_query(['groups' => ['moneyTransactionsShortIn', 'moneyTransactionsShortOut']]);

        // assert data is retrieved
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        // in
        $this->assertCount(2, $data['money_transactions_short_in']);
        $this->assertArrayHasKey('id', $data['money_transactions_short_in'][0]);
        $this->assertEquals('123.45', $data['money_transactions_short_in'][0]['amount']);
        $this->assertEquals('d1', $data['money_transactions_short_in'][0]['description']);
        $this->assertEquals('2015-12-31', $data['money_transactions_short_in'][0]['date']);
        // out
        $this->assertCount(1, $data['money_transactions_short_out']);
        $this->assertArrayHasKey('id', $data['money_transactions_short_out'][2]);
        $this->assertEquals('d3', $data['money_transactions_short_out'][2]['description']);
        $this->assertEquals('5000.59', $data['money_transactions_short_out'][2]['amount']);
    }

    public function testAddEditTransaction()
    {
        $url = '/report/'.self::$report1->getId().'/money-transaction-short';
        $url2 = '/report/'.self::$report2->getId().'/money-transaction-short';

        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('POST', $url2, self::$tokenDeputy);

        $data = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'type' => 'in',
                'amount' => 123.45,
                'description' => 'd',
                'date' => '2014-04-05',
            ],
        ])['data'];

        self::fixtures()->clear();

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_MONEY_IN_SHORT));

        $t = self::fixtures()->getRepo('Report\MoneyTransactionShortIn')->find($data); /* @var $t MoneyTransactionShortIn */
        $this->assertEquals(123.45, $t->getAmount());
        $this->assertEquals('d', $t->getDescription());
        $this->assertEquals('2014-04-05', $t->getDate()->format('Y-m-d'));
    }

    public function testEditTransaction()
    {
        $url = '/report/'.self::$report1->getId().'/money-transaction-short/'.self::$transaction1->getId();
        $url2 = '/report/'.self::$report2->getId().'/money-transaction-short/'.self::$transaction2->getId();

        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);

        $data = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'amount' => 124.46,
                'description' => 'd-changed',
                'date' => '2014-04-06',
            ],
        ])['data'];

        self::fixtures()->clear();

        $t = self::fixtures()->getRepo('Report\MoneyTransactionShort')->find($data); /* @var $t MoneyTransactionShort */
        $this->assertEquals(124.46, $t->getAmount());
        $this->assertEquals('d-changed', $t->getDescription());
        $this->assertEquals('2014-04-06', $t->getDate()->format('Y-m-d'));
    }

    public function testDelete()
    {
        $url = '/report/'.self::$report1->getId().'/money-transaction-short/'.self::$transaction3->getId();
        $url2 = '/report/'.self::$report2->getId().'/money-transfers/99';

        $this->assertEquals('yes', self::$report1->getMoneyTransactionsShortOutExist());

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);

        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        self::fixtures()->clear();
        self::$report1 = self::fixtures()->getReportById(self::$report1->getId());

        $t = self::fixtures()->getRepo('Report\MoneyTransactionShort')->find(self::$transaction3->getId());
        $this->assertTrue(null === $t);
        $this->assertCount(0, self::$report1->getMoneyTransactionsShortOut());
        $this->assertEquals('no', self::$report1->getMoneyTransactionsShortOutExist());
    }

    public function testExist()
    {
        $report = self::fixtures()->getReportById(self::$report1->getId());
        $this->assertTrue(count($report->getMoneyTransactionsShortIn()) > 0);
        $this->assertEquals('yes', $report->getMoneyTransactionsShortInExist());

        $url = '/report/'.self::$report1->getId();
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'money_transactions_short_in_exist' => 'no',
            ],
        ]);

        self::fixtures()->clear();
        $report = self::fixtures()->getReportById(self::$report1->getId());
        $this->assertEquals('no', $report->getMoneyTransactionsShortInExist());
        $this->assertCount(0, $report->getMoneyTransactionsShortIn());
    }
}
