<?php

namespace App\Tests\Unit\Controller\Ndr;

use App\Entity\Ndr\Expense;
use App\Entity\Ndr\Ndr;
use App\Tests\Unit\Controller\AbstractTestController;

class ExpenseControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $ndr1;
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
    private static $ndr2;
    private static $tokenAdmin;
    private static $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }

        // deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);
        self::$ndr1 = self::fixtures()->createNdr(self::$client1);
        self::$expense1 = self::fixtures()->createNdrExpense('other', self::$ndr1, ['setExplanation' => 'e1', 'setAmount' => 1.1]);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2);
        self::$ndr2 = self::fixtures()->createNdr(self::$client2);
        self::$expense2 = self::fixtures()->createNdrExpense('other', self::$ndr2, ['setExplanation' => 'e2', 'setAmount' => 2.2]);

        self::fixtures()->flush()->clear();
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
        $url = '/ndr/'.self::$ndr1->getId().'/expense/'.self::$expense1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testgetOneByIdAcl()
    {
        $url2 = '/ndr/'.self::$ndr1->getId().'/expense/'.self::$expense2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testgetOneById()
    {
        $url = '/ndr/'.self::$ndr1->getId().'/expense/'.self::$expense1->getId();

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
        $url = '/ndr/'.self::$ndr1->getId().'/expense';
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);

        $url = '/ndr/'.self::$ndr1->getId().'/expense/'.self::$expense1->getId();
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    public function testPostPutAcl()
    {
        $url2 = '/ndr/'.self::$ndr2->getId().'/expense';
        $this->assertEndpointNotAllowedFor('POST', $url2, self::$tokenDeputy);

        $url2 = '/ndr/'.self::$ndr2->getId().'/expense/'.self::$expense1->getId();
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);

        $url3 = '/ndr/'.self::$ndr2->getId().'/expense/'.self::$expense2->getId();
        $this->assertEndpointNotAllowedFor('PUT', $url3, self::$tokenDeputy);
    }

    public function testPostPutGetAll()
    {
        // POST
        $url = '/ndr/'.self::$ndr1->getId().'/expense';
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

        $expense = self::fixtures()->getRepo('Ndr\Expense')->find($expenseId);
        /* @var $expense \App\Entity\Ndr\Expense */
        $this->assertEquals(3.3, $expense->getAmount());
        $this->assertEquals('e3', $expense->getExplanation());

        // UPDATE
        $url = '/ndr/'.self::$ndr1->getId().'/expense/'.$expenseId;
        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'amount' => 3.31,
                'explanation' => 'e3.1',
            ],
        ]);
        self::fixtures()->clear();

        $expense = self::fixtures()->getRepo('Ndr\Expense')->find($expenseId);
        /* @var $expense \App\Entity\Ndr\Expense */
        $this->assertEquals(3.31, $expense->getAmount());
        $this->assertEquals('e3.1', $expense->getExplanation());

        // GET ALL
        $url = '/ndr/'.self::$ndr1->getId();
        $q = http_build_query(['groups' => ['ndr-expenses']]);
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
        $url = '/ndr/'.self::$ndr1->getId().'/expense/'.self::$expense1->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);
    }

    public function testDeleteAcl()
    {
        $url2 = '/ndr/'.self::$ndr1->getId().'/expense/'.self::$expense2->getId();
        $url3 = '/ndr/'.self::$ndr2->getId().'/expense/'.self::$expense2->getId();

        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);
        $this->assertEndpointNotAllowedFor('DELETE', $url3, self::$tokenDeputy);
    }

    /**
     * @depends testPostPutGetAll
     */
    public function testDelete()
    {
        $url = '/ndr/'.self::$ndr1->getId().'/expense/'.self::$expense1->getId();
        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $this->assertTrue(null === self::fixtures()->getRepo('Ndr\Expense')->find(self::$expense1->getId()));
    }

    public function testPaidAnything()
    {
        $url = '/ndr/'.self::$ndr1->getId().'/expense/'.self::$expense1->getId();
        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $url = '/ndr/'.self::$ndr1->getId().'/expense';
        $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'amount' => 3.3,
                'explanation' => 'e3',
            ],
        ]);

        /** @var Ndr $ndr */
        $ndr = self::fixtures()->getRepo('Ndr\Ndr')->find(self::$ndr1->getId());

        $this->assertCount(1, $ndr->getExpenses());
        $this->assertEquals('yes', $ndr->getPaidForAnything());

        $url = '/ndr/'.self::$ndr1->getId();
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'paid_for_anything' => 'no',
            ],
        ]);

        self::fixtures()->clear();
        $ndr = self::fixtures()->getRepo('Ndr\Ndr')->find(self::$ndr1->getId());
        $this->assertEquals('no', $ndr->getPaidForAnything());
        $this->assertCount(0, $ndr->getExpenses());
    }
}
