<?php

namespace App\Tests\Unit\ControllerReport;

use App\Entity\Report\MoneyTransfer;
use App\Entity\Report\Report;
use App\Tests\Unit\Controller\AbstractTestController;

class MoneyTransferControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $report1;
    private static $account1;
    private static $deputy2;
    private static $report2;
    private static $account2;
    private static $account3;
    private static $transfer1;
    private static $tokenAdmin;
    private static $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');

        $client1 = self::fixtures()->createClient(self::$deputy1);
        self::fixtures()->flush();

        self::$report1 = self::fixtures()->createReport($client1);
        self::$account1 = self::fixtures()->createAccount(self::$report1, ['setBank' => 'bank1']);
        self::$account2 = self::fixtures()->createAccount(self::$report1, ['setBank' => 'bank2']);

        // add two transfer to report 1 between accounts
        self::$transfer1 = new MoneyTransfer();
        self::$transfer1->setReport(self::$report1)
            ->setAmount(1001)
            ->setFrom(self::$account2)
            ->setTo(self::$account1);
        self::fixtures()->persist(self::$transfer1);

        $transfer2 = new MoneyTransfer();
        $transfer2->setReport(self::$report1)
            ->setAmount(52)
            ->setFrom(self::$account1)
            ->setTo(self::$account2);
        self::fixtures()->persist($transfer2);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        $client2 = self::fixtures()->createClient(self::$deputy2);
        self::$report2 = self::fixtures()->createReport($client2);
        self::$account3 = self::fixtures()->createAccount(self::$report2, ['setBank' => 'bank3']);

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

    public function testGetTransfers()
    {
        $url = '/report/'.self::$report1->getId()
            .'?'.http_build_query(['groups' => ['money-transfer', 'account']]);

        // assert data is retrieved
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data']['money_transfers'];

        $this->assertEquals(1001, $data[0]['amount']);
        $this->assertEquals('bank2', $data[0]['accountFrom']['bank']);
        $this->assertEquals('bank1', $data[0]['accountTo']['bank']);

        $this->assertEquals(52, $data[1]['amount']);
        $this->assertEquals('bank1', $data[1]['accountFrom']['bank']);
        $this->assertEquals('bank2', $data[1]['accountTo']['bank']);
    }

    public function testAddTransfer()
    {
        $url = '/report/'.self::$report1->getId().'/money-transfers';
        $url2 = '/report/'.self::$report2->getId().'/money-transfers';

        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('POST', $url2, self::$tokenDeputy);

        $data = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'account_from_id' => self::$account1->getId(),
                'account_to_id' => self::$account2->getId(),
                'amount' => ' 123,345.56 ',
            ],
        ])['data'];

        $this->assertTrue($data > 0);
        self::fixtures()->clear();

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_MONEY_TRANSFERS));

        // assert account created with transactions
        $report = self::fixtures()->getReportById(self::$report1->getId()); /* @var $report \App\Entity\Report\Report */

        // test last transaction
        $t = $report->getMoneyTransfers()->get(2);
        $this->assertEquals(123345.56, $t->getAmount());
        $this->assertEquals(self::$account1->getId(), $t->getFrom()->getId());
        $this->assertEquals(self::$account2->getId(), $t->getTo()->getId());
    }

    public function testEditTransfer()
    {
        $url = '/report/'.self::$report1->getId().'/money-transfers/'.self::$transfer1->getId();
        $url2 = '/report/'.self::$report2->getId().'/money-transfers/'.self::$transfer1->getId();

        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);

        $data = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'account_from_id' => self::$account2->getId(),
                'account_to_id' => self::$account1->getId(),
                'amount' => 124,
            ],
        ])['data'];

        $this->assertTrue($data > 0);

        self::fixtures()->clear();

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_MONEY_TRANSFERS));

        $t = self::fixtures()->getRepo('Report\MoneyTransfer')->find(self::$transfer1->getId());
        $this->assertEquals(124, $t->getAmount());
        $this->assertEquals(self::$account2->getId(), $t->getFrom()->getId());
        $this->assertEquals(self::$account1->getId(), $t->getTo()->getId());
        $this->assertEquals(self::$report1->getId(), $t->getReport()->getId());
    }

    /**
     * @depends testGetTransfers
     * @depends testEditTransfer
     */
    public function testdeleteTransfer()
    {
        $url = '/report/'.self::$report1->getId().'/money-transfers/'.self::$transfer1->getId();
        $url2 = '/report/'.self::$report2->getId().'/money-transfers/99';

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);

        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        self::fixtures()->clear();

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_MONEY_TRANSFERS));

        $t = self::fixtures()->getRepo('Report\MoneyTransfer')->find(self::$transfer1->getId());
        $this->assertTrue(null === $t);
    }

    /**
     * @depends testdeleteTransfer
     */
    public function testNoTransfers()
    {
        /* @var $report Report */
        $report = self::fixtures()->getRepo('Report\Report')->find(self::$report1->getId());
        $this->assertTrue(count($report->getMoneyTransfers()) > 0);
        self::fixtures()->clear();

        $url = '/report/'.self::$report1->getId();
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'no_transfers_to_add' => true,
            ],
        ]);

        $report = self::fixtures()->getRepo('Report\Report')->find(self::$report1->getId());
        $this->assertCount(0, $report->getMoneyTransfers());
    }
}
