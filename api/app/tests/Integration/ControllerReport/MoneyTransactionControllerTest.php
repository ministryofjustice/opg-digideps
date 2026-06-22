<?php

namespace Tests\OPG\Digideps\Backend\Integration\ControllerReport;

use OPG\Digideps\Backend\Entity\Report\MoneyTransaction;
use OPG\Digideps\Backend\Entity\Report\Report;
use Tests\OPG\Digideps\Backend\Fixture\Scenario;
use Tests\OPG\Digideps\Backend\Integration\Controller\AbstractTestController;

class MoneyTransactionControllerTest extends AbstractTestController
{
    private static Report $report1;
    private static Report $report2;
    private static string $tokenAdmin;
    private static string $tokenDeputy;
    private static MoneyTransaction $t1;
    private static MoneyTransaction $t2;

    public function setUp(): void
    {
        parent::setUp();

        ['persons' => ['users' => ['lay1' => $user1]], 'orders' => [['pfa' => ['reports' => [self::$report1]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());
        ['orders' => [['pfa' => ['reports' => [self::$report2]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());

        self::$t1 = new MoneyTransaction(self::$report1, 'dividends')->setAmount(123.45)->setDescription('d1');
        self::$t2 = new MoneyTransaction(self::$report1, 'dividends')->setAmount(789.12)->setDescription('d2');
        $t3 = new MoneyTransaction(self::$report1, 'loans')->setAmount(5000.59)->setDescription('d3');
        $t4 = new MoneyTransaction(self::$report2, 'loans')->setAmount(123)->setDescription('belongs to report2');

        self::fixtures()->persist(self::$t1, self::$t2, $t3, $t4);
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
        $url = '/report/' . self::$report1->getId()
            . '?' . http_build_query(['groups' => ['transactionsIn', 'transactionsOut']]);

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

    public function testAddEditTransaction(): void
    {
        $url = '/report/' . self::$report1->getId() . '/money-transaction';
        $url2 = '/report/' . self::$report2->getId() . '/money-transaction';

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

        $t = self::fixtures()->getRepo(MoneyTransaction::class)->find($data); /* @var $t MoneyTransaction */
        $this->assertEquals(123.45, $t->getAmount());
        $this->assertEquals('d', $t->getDescription());
        $this->assertEquals('dividends', $t->getCategory());
    }

    public function testEditTransaction(): void
    {
        $url = '/report/' . self::$report1->getId() . '/money-transaction/' . self::$t1->getId();
        $url2 = '/report/' . self::$report2->getId() . '/money-transaction/' . self::$t2->getId();

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

        $t = self::fixtures()->getRepo(MoneyTransaction::class)->find($data); /* @var $t MoneyTransaction */
        $this->assertEquals(124.46, $t->getAmount());
        $this->assertEquals('d-changed', $t->getDescription());
        $this->assertEquals('dividends', $t->getCategory());
    }
}
