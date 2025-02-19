<?php

namespace App\Tests\Integration\ControllerReport;

use App\Entity\Report\MoneyTransaction;
use App\Entity\Report\Report;
use App\Tests\Integration\Controller\AbstractTestController;

class MoneyTransactionControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $report1;
    private static $deputy2;
    private static $report2;
    private static $tokenAdmin;
    private static $tokenDeputy;
    private static $t1;
    private static $t2;

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

        // transactions
        self::$t1 = new MoneyTransaction(self::$report1);
        self::$t1->setCategory('dividends')->setAmount(123.45)->setDescription('d1');
        self::$t2 = new MoneyTransaction(self::$report1);
        self::$t2->setCategory('dividends')->setAmount(789.12)->setDescription('d2');
        $t3 = new MoneyTransaction(self::$report1);
        $t3->setCategory('loans')->setAmount(5000.59)->setDescription('d3');
        $t4 = new MoneyTransaction(self::$report2);
        $t4->setCategory('loans')->setAmount(123)->setDescription('belongs to report2');

        self::fixtures()->persist(self::$t1, self::$t2, $t3, $t4);
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
            .'?'.http_build_query(['groups' => ['transactionsIn', 'transactionsOut']]);

        // assert data is retrieved
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        // in
        $this->assertCount(2, $data['money_transactions_in']);
        $this->assertArrayHasKey('id', $data['money_transactions_in'][0]);
        $this->assertEquals('dividends', $data['money_transactions_in'][0]['category']);
        $this->assertEquals('123.45', $data['money_transactions_in'][0]['amount']);
        $this->assertArrayHasKey('id', $data['money_transactions_in'][1]);
        $this->assertEquals('dividends', $data['money_transactions_in'][1]['category']);
        $this->assertEquals('789.12', $data['money_transactions_in'][1]['amount']);
        // out
        $this->assertCount(1, $data['money_transactions_out']);
        $this->assertArrayHasKey('id', $data['money_transactions_out'][2]);
        $this->assertEquals('loans', $data['money_transactions_out'][2]['category']);
        $this->assertEquals('5000.59', $data['money_transactions_out'][2]['amount']);
    }

    public function testAddEditTransaction()
    {
        $url = '/report/'.self::$report1->getId().'/money-transaction';
        $url2 = '/report/'.self::$report2->getId().'/money-transaction';

        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('POST', $url2, self::$tokenDeputy);

        $data = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'category' => 'dividends',
                'amount' => 123.45,
                'description' => 'd',
            ],
        ])['data'];

        self::fixtures()->clear();

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_MONEY_IN));

        $t = self::fixtures()->getRepo('Report\MoneyTransaction')->find($data); /* @var $t MoneyTransaction */
        $this->assertEquals(123.45, $t->getAmount());
        $this->assertEquals('d', $t->getDescription());
        $this->assertEquals('dividends', $t->getCategory());
    }

    public function testEditTransaction()
    {
        $url = '/report/'.self::$report1->getId().'/money-transaction/'.self::$t1->getId();
        $url2 = '/report/'.self::$report2->getId().'/money-transaction/'.self::$t2->getId();

        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);

        $data = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'amount' => 124.46,
                'description' => 'd-changed',
            ],
        ])['data'];

        self::fixtures()->clear();

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_MONEY_IN));

        $t = self::fixtures()->getRepo('Report\MoneyTransaction')->find($data); /* @var $t MoneyTransaction */
        $this->assertEquals(124.46, $t->getAmount());
        $this->assertEquals('d-changed', $t->getDescription());
        $this->assertEquals('dividends', $t->getCategory());
    }
}
