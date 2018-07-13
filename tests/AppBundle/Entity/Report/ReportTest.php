<?php

namespace Tests\AppBundle\Entity\Report;

use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\AssetOther;
use AppBundle\Entity\Report\AssetProperty;
use AppBundle\Entity\Report\BankAccount;
use AppBundle\Entity\Report\Expense;
use AppBundle\Entity\Report\Fee;
use AppBundle\Entity\Report\Gift;
use AppBundle\Entity\Report\MoneyTransaction;
use AppBundle\Entity\Report\MoneyTransactionShortIn;
use AppBundle\Entity\Report\MoneyTransactionShortOut;
use AppBundle\Entity\Report\Report;
use AppBundle\Service\ReportStatusService;
use Doctrine\Common\Collections\ArrayCollection;
use MockeryStub as m;

class ReportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Report
     */
    private $report;

    public function setUp()
    {
        $this->client = m::mock(Client::class, ['getUnsubmittedReports'=>new ArrayCollection(), 'getSubmittedReports'=>new ArrayCollection()]);
        $this->validReportCtorArgs = [$this->client, Report::TYPE_102, new \DateTime('2017-06-23'), new \DateTime('2018-06-22')];
        $this->report = m::mock(Report::class . '[has106Flag]', $this->validReportCtorArgs);

        $this->gift1 = m::mock(Gift::class, ['getAmount' => 1]);
        $this->gift2 = m::mock(Gift::class, ['getAmount' => 10]);
        $this->expense1 = m::mock(Expense::class, ['getAmount' => 2]);
        $this->expense2 = m::mock(Expense::class, ['getAmount' => 20]);
    }

    public function testDueDate()
    {
        $startDate = new \Datetime('2017-01-01');
        $endDate = new \Datetime('2018-12-31');

        $report = new Report($this->client, Report::TYPE_102, $startDate, $endDate, false);
        $this->assertEquals('2019-02-25', $report->getDueDate()->format('Y-m-d'));
    }

    public static function constructorProvider()
    {
        return [
            // start date, end date, submitted (true/false)
            ['2017-06-23', '2018-06-22', [['2016-06-23', '2017-06-22', false]], 'unsubmitted report'],
            //['2017-06-23', '2018-06-24', [['2016-06-23', '2017-06-22', true]], 'cannot cover more than one year'],
            ['2017-06-24', '2018-06-23', [['2016-06-23', '2017-06-22', true], ['2015-06-23', '2016-06-22', true]], 'new report is expected to start on'],
        ];
    }

    /**
     * @dataProvider constructorProvider
     * */
    public function testConstructorExceptions($startDate, $endDate, array $clientReports, $expectedTextInException)
    {
        $client = new Client();
        foreach ($clientReports as $rep) {
            $report = (new Report($this->client, Report::TYPE_102, new \DateTime($rep[0]), new \DateTime($rep[1])))->setSubmitted(($rep[2]));
            $client->addReport($report);
        }

        $this->setExpectedException(\RuntimeException::class, $expectedTextInException);

        new Report($client, Report::TYPE_102, new \DateTime($startDate), new \DateTime($endDate));
    }

    public function testGetMoneyTotal()
    {
        // 102
        $this->assertEquals(0, $this->report->getMoneyInTotal());
        $this->assertEquals(0, $this->report->getMoneyOutTotal());
        $this->report->setMoneyTransactions(new ArrayCollection([
            (new MoneyTransaction($this->report))->setCategory('account-interest')->setAmount(1),
            (new MoneyTransaction($this->report))->setCategory('dividends')->setAmount(2),
            (new MoneyTransaction($this->report))->setCategory('broadband')->setAmount(3),
            (new MoneyTransaction($this->report))->setCategory('food')->setAmount(4),
        ]));
        $this->assertEquals(1+2, $this->report->getMoneyInTotal());
        $this->assertEquals(3+4, $this->report->getMoneyOutTotal());

        // 103
        $this->report->setType(Report::TYPE_103);
        $this->assertEquals(0, $this->report->getMoneyInTotal());
        $this->assertEquals(0, $this->report->getMoneyOutTotal());
        $this->report->setMoneyTransactionsShort(new ArrayCollection([
            (new MoneyTransactionShortIn($this->report))->setAmount(10),
            (new MoneyTransactionShortIn($this->report))->setAmount(20),
            (new MoneyTransactionShortOut($this->report))->setAmount(30),
            (new MoneyTransactionShortOut($this->report))->setAmount(40),
        ]));
        $this->assertEquals(10+20, $this->report->getMoneyInTotal());
        $this->assertEquals(30+40, $this->report->getMoneyOutTotal());
    }

    public function testGetAccountsOpeningBalanceTotal()
    {
        $this->assertEquals(0, $this->report->getAccountsOpeningBalanceTotal());

        $this->report->addAccount((new BankAccount())->setBank('bank1')->setOpeningBalance(1));
        $this->report->addAccount((new BankAccount())->setBank('bank2')->setOpeningBalance(3));
        $this->report->addAccount((new BankAccount())->setBank('bank3')->setOpeningBalance(0));

        $this->assertEquals(4, $this->report->getAccountsOpeningBalanceTotal());
    }

    public function testGetAccountsClosingBalanceTotal()
    {
        $this->assertEquals(0, $this->report->getAccountsClosingBalanceTotal());

        $this->report->addAccount((new BankAccount())->setBank('bank1')->setClosingBalance(1));

        $this->assertEquals(1, $this->report->getAccountsClosingBalanceTotal());

        $this->report->addAccount((new BankAccount())->setBank('bank2')->setClosingBalance(3));
        $this->report->addAccount((new BankAccount())->setBank('bank3')->setClosingBalance(0));

        $this->assertEquals(4, $this->report->getAccountsClosingBalanceTotal());
    }

    public function testGetCalculatedBalance()
    {
        $this->report->shouldReceive('has106Flag')->andReturn(false);

        $this->assertEquals(0, $this->report->getCalculatedBalance());

        $this->report->addAccount((new BankAccount())->setBank('bank1')->setOpeningBalance(1));

        $this->assertEquals(1, $this->report->getCalculatedBalance());

        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('account-interest')->setAmount(20)); //in
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('account-interest')->setAmount(20));//in
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('rent')->setAmount(15));//out
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('rent')->setAmount(15));//out
        $this->report->setGifts([$this->gift1, $this->gift2]);
        $this->report->setExpenses([$this->expense1, $this->expense2]);
        $calculatedBalance = 1 + 20 + 20 - 15 - 15 - 11 - 22;

        $this->assertEquals($calculatedBalance, $this->report->getCalculatedBalance());
    }

    /**
     * //TODO consider rewriting, unit testing methods composing the total
     * (see testgetExpensesTotal as an example) and using mocks here
     */
    public function testGetTotalsOffsetAndMatch()
    {
        $this->report->shouldReceive('has106Flag')->andReturn(false);

        $this->assertEquals(0, $this->report->getTotalsOffset());
        $this->assertEquals(true, $this->report->getTotalsMatch());

        // account opened with 1000, closed with 2000. 1500 money in, 400 out. balance is 100
        $this->report->addAccount((new BankAccount())->setBank('bank1')->setOpeningBalance(1000)->setClosingBalance(2000));
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('account-interest')->setAmount(1500));//in
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('rent')->setAmount(400));//out
        $this->report->setGifts([$this->gift1, $this->gift2]);
        $this->report->setExpenses([$this->expense1, $this->expense2]);

        $exepectedTotalOffset = 67; // 1000 - 2000 + 1500 - 400 - 11 - 22
        $this->assertEquals($exepectedTotalOffset, $this->report->getTotalsOffset());
        $this->assertEquals(false, $this->report->getTotalsMatch());

        // add missing transaction that fix the balance
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('rent')->setAmount(67));//in

        $this->assertEquals(0, $this->report->getTotalsOffset());
        $this->assertEquals(true, $this->report->getTotalsMatch());
    }

    public function testgetFeesTotal()
    {
        $fee1 = m::mock(Fee::class, ['getAmount'=>2]);
        $reportWith = function ($fees) {
            return m::mock(Report::class . '[getFees]', $this->validReportCtorArgs)
                ->shouldReceive('getFees')->andReturn($fees)
                ->getMock();
        };

        $this->assertEquals(0, $reportWith([])->getFeesTotal());
        $this->assertEquals(2+2, $reportWith([$fee1, $fee1])->getFeesTotal());
    }

    public function testgetExpensesTotal()
    {
        $exp1 = m::mock(Expense::class, ['getAmount'=>1]);

        $reportWith = function ($expenses) {
            return m::mock(Report::class . '[getExpenses]', $this->validReportCtorArgs)
                ->shouldReceive('getExpenses')->andReturn($expenses)
                ->getMock();
        };

        $this->assertEquals(0, $reportWith([])->getExpensesTotal());
        $this->assertEquals(1+1, $reportWith([$exp1, $exp1])->getExpensesTotal());
    }

    public function testgetAssetsTotalValue()
    {
        $this->assertEquals(0, $this->report->getAssetsTotalValue());

        $this->report->addAsset(m::mock(AssetOther::class, ['getValueTotal' => 1]));
        $this->report->addAsset(m::mock(AssetProperty::class, ['getValueTotal' => 1]));

        $this->assertEquals(2, $this->report->getAssetsTotalValue());
    }


    public static function sectionsSettingsProvider()
    {
        return [
            [Report::TYPE_102, ['bankAccounts', 'moneyIn', 'balance'], ['moneyInShort', 'lifestyle']],
            [Report::TYPE_103, ['bankAccounts', 'moneyInShort'], ['moneyIn', 'lifestyle', 'balance']],
            [Report::TYPE_104, ['lifestyle'], ['bankAccounts', 'moneyIn', 'moneyInShort', 'gifts', 'balance']],
        ];
    }

    /**
     * Some checks that the config array doesn't get messed up
     *
     * @dataProvider sectionsSettingsProvider
     */
    public function testAvailableSectionsAndHasSection($type, array $expectedSections, array $unExpectedSections)
    {
        $this->report = new Report($this->client, $type, new \DateTime('2017-06-23'), new \DateTime('2018-06-22'));

        foreach ($expectedSections as $section) {
            $this->assertContains($section, $this->report->getAvailableSections());
            $this->assertTrue($this->report->hasSection($section));
        }
        foreach ($unExpectedSections as $section) {
            $this->assertNotContains($section, $this->report->getAvailableSections(), "$type should NOT have $section section ");
            $this->assertFalse($this->report->hasSection($section));
        }
    }

    public function testGetPreviousReportData()
    {
        $mockClient =  m::mock(Client::class);
        $mockClient->shouldReceive('getUnsubmittedReports')->andReturn(new ArrayCollection());
        $mockClient->shouldReceive('getSubmittedReports')->andReturn(new ArrayCollection());

        $reportCurrent = m::mock(Report::class)->makePartial();
        $reportCurrent->shouldReceive('getId')->andReturn(10);
        $reportCurrent->shouldReceive('getClient')->andReturn($mockClient);

        $ndrBankAccount1 = new \AppBundle\Entity\Ndr\BankAccount();
        $ndrBankAccount1->setId(1);
        $ndrBankAccount1->setBank('ndrBank1');
        $ndrBankAccount1->setAccountNumber('1111');
        $ndrBankAccount1->setSortCode('111111');
        $ndrBankAccount1->setBalanceOnCourtOrderDate(600);

        $bankAccount1 = new BankAccount();
        $bankAccount1->setId(2);
        $bankAccount1->setBank('bank1');
        $bankAccount1->setAccountNumber('2222');
        $bankAccount1->setSortCode('222222');
        $bankAccount1->setOpeningBalance(200);
        $bankAccount1->setClosingBalance(300);

        $bankAccount2 = new BankAccount();
        $bankAccount2->setId(3);
        $bankAccount2->setBank('bank2');
        $bankAccount2->setAccountNumber('3333');
        $bankAccount2->setSortCode('333333');
        $bankAccount2->setOpeningBalance(700);
        $bankAccount2->setClosingBalance(500);

        $mockReport1 = m::mock(Report::class)->makePartial();
        $mockReport1->shouldReceive('getId')->andReturn(9);
        $mockReport1->shouldReceive('getClient')->andReturn($mockClient);
        $mockReport1->shouldReceive('getType')->andReturn('102');
        $mockReport1->shouldReceive('getBankAccounts')->andReturn(new ArrayCollection([$bankAccount1, $bankAccount2]));

        $mockNdr1 = m::mock(Ndr::class)->makePartial();
        $mockNdr1->shouldReceive('getId')->andReturn(8);
        $mockNdr1->shouldReceive('getClient')->andReturn($mockClient);
        $mockNdr1->shouldReceive('getBankAccounts')->andReturn(new ArrayCollection([$ndrBankAccount1]));

        $clientReports = new ArrayCollection([$reportCurrent, $mockReport1, $mockNdr1]);
        $mockClient->shouldReceive('getReports')->andReturn($clientReports);

        // assert false as no previous reports set yet
        $this->assertFalse($mockNdr1->getPreviousReportData());

        // assert report 1 contains NDR data
        $report1PreviousData = $mockReport1->getPreviousReportData();
        $this->assertArrayHasKey('financial-summary', $report1PreviousData);
        $this->assertArrayHasKey('report-summary', $report1PreviousData);
        $this->assertEquals(
            $report1PreviousData['report-summary']['type'],
            'ndr'
        );
        $this->assertCount(1, $report1PreviousData['financial-summary']['accounts']);
        $this->assertArrayHasKey('opening-balance-total', $report1PreviousData['financial-summary']);
        $this->assertArrayHasKey('closing-balance-total', $report1PreviousData['financial-summary']);
        $this->assertEquals(
            $report1PreviousData['financial-summary']['opening-balance-total'],
            $report1PreviousData['financial-summary']['closing-balance-total']
        );

        $this->assertEquals(
            'ndrBank1',
            $report1PreviousData['financial-summary']['accounts'][$ndrBankAccount1->getId()]['bank']
        );
        $this->assertArrayHasKey('nameOneLine', $report1PreviousData['financial-summary']['accounts'][$ndrBankAccount1->getId()]);
        $this->assertEquals($report1PreviousData['financial-summary']['closing-balance-total'], $ndrBankAccount1->getBalanceOnCourtOrderDate());

        // assert current report contains report1 data
        $currentReportPreviousData = $reportCurrent->getPreviousReportData();
        $this->assertArrayHasKey('financial-summary', $currentReportPreviousData);
        $this->assertArrayHasKey('report-summary', $report1PreviousData);

        $this->assertCount(2, $currentReportPreviousData['financial-summary']['accounts']);
        $this->assertEquals(
            'bank1',
            $currentReportPreviousData['financial-summary']['accounts'][$bankAccount1->getId()]['bank']
        );
        $this->assertEquals(
            'bank2',
            $currentReportPreviousData['financial-summary']['accounts'][$bankAccount2->getId()]['bank']
        );
        $this->assertEquals(
            $currentReportPreviousData['report-summary']['type'],
            '102'
        );
        $this->assertArrayHasKey('nameOneLine', $currentReportPreviousData['financial-summary']['accounts'][$bankAccount1->getId()]);
        $this->assertEquals(
            $currentReportPreviousData['financial-summary']['closing-balance-total'],
            ($bankAccount1->getClosingBalance() + $bankAccount2->getClosingBalance())
        );

    }
}
