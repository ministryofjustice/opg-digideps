<?php
namespace AppBundle\Entity;

//use AppBundle\Entity\Transaction;
//use AppBundle\Entity\Account;
use Mockery as m;

class ReportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Report
     */
    private $report;

    public function setUp()
    {
        $this->report = new Report();
    }

    public function testGetMoneyInTotal()
    {
        $this->assertEquals(0, $this->report->getMoneyInTotal());

        $this->report->addTransaction((new Transaction($this->report, new TransactionTypeIn(), 1)));
        $this->report->addTransaction((new Transaction($this->report, new TransactionTypeIn(), 2)));

        $this->assertEquals(3, $this->report->getMoneyInTotal());
    }


    public function getMoneyOutTotalProvider($expected, array $data)
    {
        $this->assertEquals(0, $this->report->getMoneyOutTotal());

        $this->report->addTransaction((new Transaction($this->report, new TransactionTypeOut(), 1)));
        $this->report->addTransaction((new Transaction($this->report, new TransactionTypeOut(), 2)));

        $this->assertEquals(3, $this->report->getMoneyOutTotal());
    }


    public function testGetAccountsOpeningBalanceTotal()
    {
        $this->assertEquals(0, $this->report->getAccountsOpeningBalanceTotal());

        $this->report->addAccount((new Account())->setBank('bank1')->setOpeningBalance(1));
        $this->report->addAccount((new Account())->setBank('bank2')->setOpeningBalance(3));
        $this->report->addAccount((new Account())->setBank('bank3')->setOpeningBalance(0));

        $this->assertEquals(4, $this->report->getAccountsOpeningBalanceTotal());
    }

    public function testGetAccountsClosingBalanceTotal()
    {
        $this->assertEquals(0, $this->report->getAccountsClosingBalanceTotal());

        $this->report->addAccount((new Account())->setBank('bank1')->setClosingBalance(1));

        $this->assertEquals(1, $this->report->getAccountsClosingBalanceTotal());

        $this->report->addAccount((new Account())->setBank('bank2')->setClosingBalance(3));
        $this->report->addAccount((new Account())->setBank('bank3')->setClosingBalance(0));

        $this->assertEquals(4, $this->report->getAccountsClosingBalanceTotal());
    }


    public function testGetCalculatedBalance()
    {
        $this->assertEquals(0, $this->report->getCalculatedBalance());

        $this->report->addAccount((new Account())->setBank('bank1')->setOpeningBalance(1));

        $this->assertEquals(1, $this->report->getCalculatedBalance());

        $this->report->addTransaction((new Transaction($this->report, new TransactionTypeIn(), 20)));
        $this->report->addTransaction((new Transaction($this->report, new TransactionTypeIn(), 20)));
        $this->report->addTransaction((new Transaction($this->report, new TransactionTypeOut(), 15)));
        $this->report->addTransaction((new Transaction($this->report, new TransactionTypeOut(), 15)));
        $calculatedBalance = 1 + 20 + 20 - 15 - 15;

        $this->assertEquals($calculatedBalance, $this->report->getCalculatedBalance());

    }

    public function testGetTotalsOffsetAndMatch()
    {
        $this->assertEquals(0, $this->report->getTotalsOffset());
        $this->assertEquals(true, $this->report->getTotalsMatch());

        $this->report->addAccount((new Account())->setBank('bank1')->setOpeningBalance(1000)->setClosingBalance(2000));
        $this->report->addTransaction((new Transaction($this->report, new TransactionTypeIn(), 1500)));
        $this->report->addTransaction((new Transaction($this->report, new TransactionTypeOut(), 400)));
        $calculatedBalance = 1000 + 1500 - 400 - 2000;


        $this->assertEquals($calculatedBalance, $this->report->getTotalsOffset());
        $this->assertEquals(false, $this->report->getTotalsMatch());

        // add missing transaction that fix the balance
        $this->report->addTransaction((new Transaction($this->report, new TransactionTypeOut(), 400)));

        $this->assertEquals(0, $this->report->getTotalsOffset());
        $this->assertEquals(true, $this->report->getTotalsMatch());
    }
}
