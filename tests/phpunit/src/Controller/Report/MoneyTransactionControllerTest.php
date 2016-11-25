<?php

namespace AppBundle\Controller\Report;

use AppBundle\Entity\Report\MoneyTransfer;
use AppBundle\Controller\AbstractTestController;
use AppBundle\Entity\Report\Transaction;
use AppBundle\Entity\Report\TransactionTypeIn;

class MoneyTransactionControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $report1;
    private static $account1;
    private static $deputy2;
    private static $report2;
    private static $account2;
    private static $account3;
    private static $transfer1;
    private static $tokenAdmin = null;
    private static $tokenDeputy = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');

        $client1 = self::fixtures()->createClient(self::$deputy1);
        self::fixtures()->flush();

        self::$report1 = self::fixtures()->createReport($client1);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        $client2 = self::fixtures()->createClient(self::$deputy2);
        self::$report2 = self::fixtures()->createReport($client2);

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

    public static function getTransactionsProvider()
    {
        return [
            ['transactionsIn', 'transactions_in', 'in', 27],
            ['transactionsOut', 'transactions_out', 'out', 45],
        ];
    }

    /**
     * @dataProvider getTransactionsProvider
     */
    public function testGetTransactions($group, $groupKey, $type, $count)
    {
        $url = '/report/'.self::$report1->getId()
            .'?'.http_build_query(['groups' => [$group]]);

        // assert data is retrieved
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'][$groupKey];

        $this->assertCount($count, $data);

        $transaction = array_shift($data);

        $this->assertArrayHasKey('id', $transaction);
        $this->assertEquals($type, $transaction['type']);
        $this->assertArrayHasKey('category', $transaction);
        $this->assertArrayHasKey('amounts', $transaction);
        $this->assertArrayHasKey( 'amounts_total', $transaction);
        $this->assertArrayHasKey('more_details', $transaction);
        $this->assertArrayHasKey('has_more_details', $transaction);
    }

    public function testEditTransaction()
    {
        $url = '/report/'.self::$report1->getId().'/money-transaction';
        $url2 = '/report/'.self::$report2->getId().'/money-transaction';

        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);

        $data = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'id' => 'dividends',
                'more_details' => 'md',
                'amounts' => [123, 456.78],
            ],
        ])['data'];

        self::fixtures()->clear();

        $t = self::fixtures()->getRepo('Report\Transaction')->find($data);
        $this->assertEquals([123, 456.78], $t->getAmounts());
        $this->assertEquals('md', $t->getMoreDetails());
    }
}
