<?php

namespace Tests\OPG\Digideps\Backend\Integration\ControllerReport;

use OPG\Digideps\Backend\Entity\Report\Expense;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\User;
use Tests\OPG\Digideps\Backend\Fixture\Scenario;
use Tests\OPG\Digideps\Backend\Integration\Controller\AbstractTestController;

class ExpenseControllerTest extends AbstractTestController
{
    private static Report $report1;
    private static Expense $expense1;
    private static Expense $expense2;
    private static Report $report2;
    private static string $tokenAdmin;
    private static string $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        ['persons' => ['users' => ['lay1' => $user1]], 'orders' => [['pfa' => ['reports' => [self::$report1]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());
        ['orders' => [['pfa' => ['reports' => [self::$report2]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());

        self::$expense1 = self::fixtures()->createReportExpense('other', self::$report1, ['setExplanation' => 'e1', 'setAmount' => 1.1]);
        self::$expense2 = self::fixtures()->createReportExpense('other', self::$report2, ['setExplanation' => 'e2', 'setAmount' => 2.2]);

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

    public function testGetOneByIdAuth(): void
    {
        $url = '/report/' . self::$report1->getId() . '/expense/' . self::$expense1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testGetOneByIdAcl(): void
    {
        $url2 = '/report/' . self::$report1->getId() . '/expense/' . self::$expense2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testGetOneById(): void
    {
        $url = '/report/' . self::$report1->getId() . '/expense/' . self::$expense1->getId();

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertEquals(self::$expense1->getId(), $data['id']);
        $this->assertEquals(self::$expense1->getExplanation(), $data['explanation']);
        $this->assertEquals(self::$expense1->getAmount(), $data['amount']);
    }

    public function testPostPutAuth(): void
    {
        $url = '/report/' . self::$report1->getId() . '/expense';
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);

        $url = '/report/' . self::$report1->getId() . '/expense/' . self::$expense1->getId();
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    public function testPostPutAcl(): void
    {
        $url2 = '/report/' . self::$report2->getId() . '/expense';
        $this->assertEndpointNotAllowedFor('POST', $url2, self::$tokenDeputy);

        $url2 = '/report/' . self::$report2->getId() . '/expense/' . self::$expense1->getId();
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);

        $url3 = '/report/' . self::$report2->getId() . '/expense/' . self::$expense2->getId();
        $this->assertEndpointNotAllowedFor('PUT', $url3, self::$tokenDeputy);
    }

    public function testPostPutGetAll(): void
    {
        // POST
        $url = '/report/' . self::$report1->getId() . '/expense';
        $return = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'amount' => 3.3,
                'explanation' => 'e3',
            ],
        ]);
        $expenseId = $return['data']['id'];
        $this->assertTrue($expenseId > 0);

        self::fixtures()->clear();

        $expense = self::fixtures()->getRepo(Expense::class)->find($expenseId);
        /* @var $expense Expense */
        $this->assertEquals(3.3, $expense->getAmount());
        $this->assertEquals('e3', $expense->getExplanation());

        // UPDATE
        $url = '/report/' . self::$report1->getId() . '/expense/' . $expenseId;
        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'amount' => 3.31,
                'explanation' => 'e3.1',
            ],
        ]);
        self::fixtures()->clear();

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_DEPUTY_EXPENSES));

        $expense = self::fixtures()->getRepo(Expense::class)->find($expenseId);
        /* @var $expense Expense */
        $this->assertEquals(3.31, $expense->getAmount());
        $this->assertEquals('e3.1', $expense->getExplanation());

        // GET ALL
        $url = '/report/' . self::$report1->getId();
        $q = http_build_query(['groups' => ['expenses']]);
        // assert both groups (quick)
        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertCount(2, $data['expenses']);
        $this->assertTrue($data['expenses'][0]['id'] > 0);
        $this->assertEquals('e1', $data['expenses'][0]['explanation']);
        $this->assertEquals(1.1, $data['expenses'][0]['amount']);
        $this->assertTrue($data['expenses'][1]['id'] > 0);
        $this->assertEquals('e3.1', $data['expenses'][1]['explanation']);
        $this->assertEquals(3.31, $data['expenses'][1]['amount']);
    }

    public function testDeleteAuth(): void
    {
        $url = '/report/' . self::$report1->getId() . '/expense/' . self::$expense1->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);
    }

    public function testDeleteAcl()
    {
        $url2 = '/report/' . self::$report1->getId() . '/expense/' . self::$expense2->getId();
        $url3 = '/report/' . self::$report2->getId() . '/expense/' . self::$expense2->getId();

        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);
        $this->assertEndpointNotAllowedFor('DELETE', $url3, self::$tokenDeputy);
    }

    /**
     * @depends testPostPutGetAll
     */
    public function testDelete(): void
    {
        $url = '/report/' . self::$report1->getId() . '/expense/' . self::$expense1->getId();
        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $exp = self::fixtures()->clear()->getRepo(Expense::class)->find(self::$expense1->getId());
        $this->assertTrue($exp === null);
    }

    /**
     * @depends testDelete
     */
    public function testPaidAnything(): void
    {
        $report = self::fixtures()->getReportById(self::$report1->getId());
        $this->assertNotNull($report);
        $report->setPaidForAnything('yes');

        self::fixtures()->persist($report);
        self::fixtures()->flush();

        $this->assertCount(1, $report->getExpenses());
        $this->assertEquals('yes', $report->getPaidForAnything());

        $url = '/report/' . self::$report1->getId();
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'paid_for_anything' => 'no',
            ],
        ]);

        self::fixtures()->clear();
        $report = self::fixtures()->getReportById(self::$report1->getId());
        $this->assertEquals('no', $report->getPaidForAnything());
        $this->assertCount(0, $report->getExpenses());
    }
}
