<?php

namespace App\Tests\Unit\ControllerReport;

use App\Entity\Report\Expense;
use App\Entity\Report\Report;
use App\Tests\Unit\Controller\AbstractTestController;

class ExpenseControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $report1;

    /**
     * @var Expense
     */
    private static $expense1;
    /**
     * @var Expense
     */
    private static $expense2;
    private static $deputy2;
    private static $client2;
    private static $report2;
    private static $tokenAdmin;
    private static $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        // deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);
        self::$report1 = self::fixtures()->createReport(self::$client1);
        self::$expense1 = self::fixtures()->createReportExpense('other', self::$report1, ['setExplanation' => 'e1', 'setAmount' => 1.1]);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2);
        self::$report2 = self::fixtures()->createReport(self::$client2);
        self::$expense2 = self::fixtures()->createReportExpense('other', self::$report2, ['setExplanation' => 'e2', 'setAmount' => 2.2]);

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

    public function testgetOneByIdAuth()
    {
        $url = '/report/'.self::$report1->getId().'/expense/'.self::$expense1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testgetOneByIdAcl()
    {
        $url2 = '/report/'.self::$report1->getId().'/expense/'.self::$expense2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testgetOneById()
    {
        $url = '/report/'.self::$report1->getId().'/expense/'.self::$expense1->getId();

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertEquals(self::$expense1->getId(), $data['id']);
        $this->assertEquals(self::$expense1->getExplanation(), $data['explanation']);
        $this->assertEquals(self::$expense1->getAmount(), $data['amount']);
    }

    public function testPostPutAuth()
    {
        $url = '/report/'.self::$report1->getId().'/expense';
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);

        $url = '/report/'.self::$report1->getId().'/expense/'.self::$expense1->getId();
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    public function testPostPutAcl()
    {
        $url2 = '/report/'.self::$report2->getId().'/expense';
        $this->assertEndpointNotAllowedFor('POST', $url2, self::$tokenDeputy);

        $url2 = '/report/'.self::$report2->getId().'/expense/'.self::$expense1->getId();
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);

        $url3 = '/report/'.self::$report2->getId().'/expense/'.self::$expense2->getId();
        $this->assertEndpointNotAllowedFor('PUT', $url3, self::$tokenDeputy);
    }

    public function testPostPutGetAll()
    {
        // POST
        $url = '/report/'.self::$report1->getId().'/expense';
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

        $expense = self::fixtures()->getRepo('Report\Expense')->find($expenseId);
        /* @var $expense \App\Entity\Report\Expense */
        $this->assertEquals(3.3, $expense->getAmount());
        $this->assertEquals('e3', $expense->getExplanation());

        // UPDATE
        $url = '/report/'.self::$report1->getId().'/expense/'.$expenseId;
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

        $expense = self::fixtures()->getRepo('Report\Expense')->find($expenseId);
        /* @var $expense \App\Entity\Report\Expense */
        $this->assertEquals(3.31, $expense->getAmount());
        $this->assertEquals('e3.1', $expense->getExplanation());

        // GET ALL
        $url = '/report/'.self::$report1->getId();
        $q = http_build_query(['groups' => ['expenses']]);
        // assert both groups (quick)
        $data = $this->assertJsonRequest('GET', $url.'?'.$q, [
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

    public function testDeleteAuth()
    {
        $url = '/report/'.self::$report1->getId().'/expense/'.self::$expense1->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);
    }

    public function testDeleteAcl()
    {
        $url2 = '/report/'.self::$report1->getId().'/expense/'.self::$expense2->getId();
        $url3 = '/report/'.self::$report2->getId().'/expense/'.self::$expense2->getId();

        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);
        $this->assertEndpointNotAllowedFor('DELETE', $url3, self::$tokenDeputy);
    }

    /**
     * @depends testPostPutGetAll
     */
    public function testDelete()
    {
        $url = '/report/'.self::$report1->getId().'/expense/'.self::$expense1->getId();
        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $exp = self::fixtures()->clear()->getRepo('Report\Expense')->find(self::$expense1->getId());
        $this->assertTrue(null === $exp);
    }

    /**
     * @depends testDelete
     */
    public function testPaidAnything()
    {
        $report = self::fixtures()->getReportById(self::$report1->getId());
        $report->setPaidForAnything('yes');

        self::fixtures()->persist($report);
        self::fixtures()->flush();

        $this->assertCount(1, $report->getExpenses());
        $this->assertEquals('yes', $report->getPaidForAnything());

        $url = '/report/'.self::$report1->getId();
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
