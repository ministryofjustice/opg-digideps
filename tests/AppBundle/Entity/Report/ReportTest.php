<?php

namespace Tests\AppBundle\Entity\Report;

use AppBundle\Entity\CasRec;
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
use Mockery as m;

class ReportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Report
     */
    private $report;

    public function setUp()
    {
        $this->report = m::mock(Report::class . '[has106Flag]');

        $this->gift1 = m::mock(Gift::class, ['getAmount' => 1]);
        $this->gift2 = m::mock(Gift::class, ['getAmount' => 10]);
        $this->expense1 = m::mock(Expense::class, ['getAmount' => 2]);
        $this->expense2 = m::mock(Expense::class, ['getAmount' => 20]);
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

    public function testgetExpensesTotal()
    {
        $reportWith = function($has106Flag, $expenses, $fees) {
            return m::mock(Report::class . '[has106Flag,getExpenses,getFees]')
                ->shouldReceive('has106Flag')->andReturn($has106Flag)
                ->shouldReceive('getExpenses')->andReturn($expenses)
                ->shouldReceive('getFees')->andReturn($fees)
                ->getMock();
        };

        $exp1 = m::mock(Expense::class, ['getAmount'=>1]);
        $fee1 = m::mock(Fee::class, ['getAmount'=>2]);

        $this->assertEquals(0, $reportWith(false, [], [])->getExpensesTotal());
        $this->assertEquals(1+1, $reportWith(false, [$exp1, $exp1], [$fee1, $fee1])->getExpensesTotal());
        $this->assertEquals(1+1+2+2, $reportWith(true, [$exp1, $exp1], [$fee1, $fee1])->getExpensesTotal());
    }

    public function testDueDate()
    {
        $endDate = new \DateTime();
        $dueDate = new \DateTime();
        $dueDate->add(new \DateInterval('P56D'));
        $this->report->setEndDate($endDate);
        $reportDueDate = $this->report->getDueDate();

        $this->assertEquals($dueDate->format('Y-m-d'), $reportDueDate->format('Y-m-d'));
    }

    public function testgetAssetsTotalValue()
    {
        $this->assertEquals(0, $this->report->getAssetsTotalValue());

        $this->report->addAsset(m::mock(AssetOther::class, ['getValueTotal' => 1]));
        $this->report->addAsset(m::mock(AssetProperty::class, ['getValueTotal' => 1]));

        $this->assertEquals(2, $this->report->getAssetsTotalValue());
    }

    public function setTypeBasedOnCasrecRecordPRovider()
    {
        return [
            //corref, type of rep, expected created report

            // 103 created with L3(G) - OPG103
            ['l3', 'opg103', Report::ENABLE_103 ? Report::TYPE_103 : Report::TYPE_102],
            ['l3g', 'opg103', Report::ENABLE_103 ? Report::TYPE_103 : Report::TYPE_102],

            // 104 create with
            ['hw', '', Report::ENABLE_104 ? Report::TYPE_104 : Report::TYPE_102],

            // all the rest is a 102 (default)
            [null, null, Report::TYPE_102],
            [null, 'opg103', Report::TYPE_102],
            [null, 'opg103', Report::TYPE_102],
            ['l2', 'opg103', Report::TYPE_102],
            ['hw', 'opg103', Report::TYPE_102],
            ['hw', 'opg102', Report::TYPE_102],
        ];
    }

    /**
     * @dataProvider  setTypeBasedOnCasrecRecordPRovider
     */
    public function testsetTypeBasedOnCasrecRecord($corref, $typeOfRep, $expectedType)
    {
        $casRec = m::mock(CasRec::class, ['getCorref' => $corref, 'getTypeOfReport' => $typeOfRep]);

        $this->report->setTypeBasedOnCasrecRecord($casRec);

        $this->report->setTypeBasedOnCasrecRecord($casRec);
        $this->assertEquals($expectedType, $this->report->getType());
    }

    public function testStatus()
    {
        $status = $this->report->getStatus();
        $this->assertInstanceOf(ReportStatusService::class, $status);
    }
}
