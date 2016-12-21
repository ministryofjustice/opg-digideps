<?php

namespace Tests\AppBundle\Controller\Odr;

use AppBundle\Entity\Odr\Expense;
use Tests\AppBundle\Controller\AbstractTestController;

class ExpenseControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $odr1;
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
    private static $odr2;
    private static $tokenAdmin = null;
    private static $tokenDeputy = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        //deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);
        self::$odr1 = self::fixtures()->createOdr(self::$client1);
        self::$expense1 = self::fixtures()->createOdrExpense('other', self::$odr1, ['setExplanation' => 'e1', 'setAmount' => 1.1]);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2);
        self::$odr2 = self::fixtures()->createOdr(self::$client2);
        self::$expense2 = self::fixtures()->createOdrExpense('other', self::$odr2, ['setExplanation' => 'e2', 'setAmount' => 2.2]);

        self::fixtures()->flush()->clear();
    }

    /**
     * clear fixtures.
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

    public function testgetOneByIdAuth()
    {
        $url = '/odr/' . self::$odr1->getId() . '/expense/' . self::$expense1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testgetOneByIdAcl()
    {
        $url2 = '/odr/' . self::$odr1->getId() . '/expense/' . self::$expense2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testgetOneById()
    {
        $url = '/odr/' . self::$odr1->getId() . '/expense/' . self::$expense1->getId();

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
        ])['data'];

        $this->assertEquals(self::$expense1->getId(), $data['id']);
        $this->assertEquals(self::$expense1->getExplanation(), $data['explanation']);
        $this->assertEquals(self::$expense1->getAmount(), $data['amount']);
    }

    public function testPostPutAuth()
    {
        $url = '/odr/' . self::$odr1->getId() . '/expense';
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);

        $url = '/odr/' . self::$odr1->getId() . '/expense/' . self::$expense1->getId();
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    public function testPostPutAcl()
    {
        $url2 = '/odr/' . self::$odr2->getId() . '/expense';
        $this->assertEndpointNotAllowedFor('POST', $url2, self::$tokenDeputy);

        $url2 = '/odr/' . self::$odr2->getId() . '/expense/' . self::$expense1->getId();
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);

        $url3 = '/odr/' . self::$odr2->getId() . '/expense/' . self::$expense2->getId();
        $this->assertEndpointNotAllowedFor('PUT', $url3, self::$tokenDeputy);
    }

    public function testPostPutGetAll()
    {
        //POST
        $url = '/odr/' . self::$odr1->getId() . '/expense';
        $return = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
            'data'        => [
                'amount'          => 3.3,
                'explanation'    => 'e3',
            ],
        ]);
        $expenseId = $return['data']['id'];
        $this->assertTrue($expenseId > 0);

        self::fixtures()->clear();

        $expense = self::fixtures()->getRepo('Odr\Expense')->find($expenseId);
        /* @var $expense \AppBundle\Entity\Odr\Expense */
        $this->assertEquals(3.3, $expense->getAmount());
        $this->assertEquals('e3', $expense->getExplanation());

        // UPDATE
        $url = '/odr/' . self::$odr1->getId() . '/expense/' . $expenseId;
        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
            'data'        => [
                'amount'          => 3.31,
                'explanation'    => 'e3.1',
            ],
        ]);
        self::fixtures()->clear();

        $expense = self::fixtures()->getRepo('Odr\Expense')->find($expenseId);
        /* @var $expense \AppBundle\Entity\Odr\Expense */
        $this->assertEquals(3.31, $expense->getAmount());
        $this->assertEquals('e3.1', $expense->getExplanation());

        // GET ALL
        $url = '/odr/' . self::$odr1->getId();
        $q = http_build_query(['groups' => ['odr-expenses']]);
        //assert both groups (quick)
        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertCount(2, $data['expenses']);
        $this->assertTrue($data['expenses'][0]['id']>0);
        $this->assertEquals('e1', $data['expenses'][0]['explanation']);
        $this->assertEquals(1.1, $data['expenses'][0]['amount']);
        $this->assertTrue($data['expenses'][1]['id']>0);
        $this->assertEquals('e3.1', $data['expenses'][1]['explanation']);
        $this->assertEquals(3.31, $data['expenses'][1]['amount']);
    }


    public function testDeleteAuth()
    {
        $url = '/odr/' . self::$odr1->getId() . '/expense/' . self::$expense1->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);
    }

    public function testDeleteAcl()
    {
        $url2 = '/odr/' . self::$odr1->getId() . '/expense/' . self::$expense2->getId();
        $url3 = '/odr/' . self::$odr2->getId() . '/expense/' . self::$expense2->getId();

        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);
        $this->assertEndpointNotAllowedFor('DELETE', $url3, self::$tokenDeputy);
    }

    /**
     * @depends testPostPutGetAll
     */
    public function testDelete()
    {
        $url = '/odr/' . self::$odr1->getId() . '/expense/' . self::$expense1->getId();
        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
        ]);

        $this->assertTrue(null === self::fixtures()->getRepo('Odr\Expense')->find(self::$expense1->getId()));
    }


    /**
     * @depends testDelete
     */
    public function testPaidAnything()
    {
        $odr = self::fixtures()->getRepo('Odr\Odr')->find(self::$odr1->getId());
        $this->assertCount(1, $odr->getExpenses());
        $this->assertEquals('yes', $odr->getPaidForAnything());

        $url = '/odr/' . self::$odr1->getId() ;
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
            'data' => [
                'paid_for_anything' => 'no'
            ]
        ]);

        self::fixtures()->clear();
        $odr = self::fixtures()->getRepo('Odr\Odr')->find(self::$odr1->getId());
        $this->assertEquals('no', $odr->getPaidForAnything());
        $this->assertCount(0, $odr->getExpenses());
    }
}
