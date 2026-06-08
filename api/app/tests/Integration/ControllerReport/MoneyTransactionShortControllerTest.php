<?php

namespace Tests\OPG\Digideps\Backend\Integration\ControllerReport;

use OPG\Digideps\Backend\Entity\Report\MoneyTransactionShort;
use OPG\Digideps\Backend\Entity\Report\MoneyTransactionShortIn;
use OPG\Digideps\Backend\Entity\Report\MoneyTransactionShortOut;
use OPG\Digideps\Backend\Entity\Report\Report;
use Tests\OPG\Digideps\Backend\Fixture\Scenario;
use Tests\OPG\Digideps\Backend\Integration\Controller\AbstractTestController;

class MoneyTransactionShortControllerTest extends AbstractTestController
{
    private static Report $report1;
    private static Report $report2;
    private static MoneyTransactionShortIn $transaction1;
    private static MoneyTransactionShortIn $transaction2;
    private static MoneyTransactionShortOut $transaction3;
    private static string $tokenAdmin;
    private static string $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        ['persons' => ['users' => ['lay1' => $user1]], 'orders' => [['pfa' => ['reports' => [self::$report1]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());
        ['orders' => [['pfa' => ['reports' => [self::$report2]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());

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


        self::$tokenAdmin = $this->loginAsAdmin();
        self::$tokenDeputy = $this->loginAsDeputy($user1->getEmail());
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function testGetTransactions(): void
    {
        $url = '/report/' . self::$report1->getId() . '?' . http_build_query(['groups' => ['moneyTransactionsShortIn', 'moneyTransactionsShortOut']]);

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

    public function testAddEditTransaction(): void
    {
        $url = '/report/' . self::$report1->getId() . '/money-transaction-short';
        $url2 = '/report/' . self::$report2->getId() . '/money-transaction-short';

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

        $t = self::fixtures()->getRepo(MoneyTransactionShortIn::class)->find($data); /* @var $t MoneyTransactionShortIn */
        $this->assertEquals(123.45, $t->getAmount());
        $this->assertEquals('d', $t->getDescription());
        $this->assertEquals('2014-04-05', $t->getDate()?->format('Y-m-d'));
    }

    public function testEditTransaction(): void
    {
        $url = '/report/' . self::$report1->getId() . '/money-transaction-short/' . self::$transaction1->getId();
        $url2 = '/report/' . self::$report2->getId() . '/money-transaction-short/' . self::$transaction2->getId();

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

        $t = self::fixtures()->getRepo(MoneyTransactionShort::class)->find($data); /* @var $t MoneyTransactionShort */
        $this->assertEquals(124.46, $t->getAmount());
        $this->assertEquals('d-changed', $t->getDescription());
        $this->assertEquals('2014-04-06', $t->getDate()?->format('Y-m-d'));
    }

    public function testDelete(): void
    {
        $url = '/report/' . self::$report1->getId() . '/money-transaction-short/' . self::$transaction3->getId();
        $url2 = '/report/' . self::$report2->getId() . '/money-transfers/99';

        $this->assertEquals('yes', self::$report1->getMoneyTransactionsShortOutExist());

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);

        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        self::fixtures()->clear();
        self::$report1 = self::fixtures()->getReportById(self::$report1->getId()) ?? throw new \LogicException('Bad fixture setup');

        $t = self::fixtures()->getRepo(MoneyTransactionShort::class)->find(self::$transaction3->getId());
        $this->assertTrue($t === null);
        $this->assertCount(0, self::$report1->getMoneyTransactionsShortOut());
        $this->assertEquals('no', self::$report1->getMoneyTransactionsShortOutExist());
    }

    public function testExist(): void
    {
        $report = self::fixtures()->getReportById(self::$report1->getId());
        $this->assertTrue(count($report->getMoneyTransactionsShortIn()) > 0);
        $this->assertEquals('yes', $report->getMoneyTransactionsShortInExist());

        $url = '/report/' . self::$report1->getId();
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
