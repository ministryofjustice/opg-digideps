<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\BankAccount;
use AppBundle\Entity\Report\Expense;
use AppBundle\Entity\Report\Gift;
use AppBundle\Entity\Report\MoneyTransaction;
use AppBundle\Entity\ReportInterface;
use Common\Form\Elements\Validators\Money;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use MockeryStub as m;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class CsvGeneratorServiceTest extends MockeryTestCase
{
    /**
     * @var CsvGeneratorService
     */
    protected $sut;

    private $mockTranslator;
    private $mockLogger;
    private $mockReport;

    /**
     * Set up the mockservies
     */
    public function setUp()
    {
        $this->mockTranslator = m::mock(Translator::class);
        $this->mockLogger = m::mock(Logger::class);
        $this->mockLogger->shouldReceive('info')->with(m::type('String'));

        $this->mockTranslator->shouldReceive('trans')->with(m::type('string'), [], 'report-money-transaction')
            ->andReturn('SomeCategory');
        $this->sut = new CsvGeneratorService($this->mockTranslator, $this->mockLogger);
    }

    public function testGenerateTransactionsCsvNoTransactions()
    {
        $this->generateMockReport(99, 0, 0, 0, 0);

        $csvString = $this->sut->generateTransactionsCsv($this->mockReport);
        $this->assertContains('Type,Category,Amount,Account,Description', $csvString);
    }

    public function testGenerateTransactionsCsvWtihTransactions()
    {
        $this->generateMockReport(
            99,
            10, // gifts
            100, // expenses
            200, // money out
            50 // money in
        );

        $csvString = $this->sut->generateTransactionsCsv($this->mockReport);
        $this->assertContains('Type,Category,Amount,Account,Description', $csvString);
        $this->assertEquals(10, preg_match_all('/Gift/', $csvString));
        $this->assertEquals(100, preg_match_all('/Expenses/', $csvString));
        $this->assertEquals(200, preg_match_all('/Money out/', $csvString));
        $this->assertEquals(50, preg_match_all('/Money in/', $csvString));
    }

    /**
     * Generates a mock Report with id and associated transactions
     *
     * @param $reportId
     * @param $numGifts
     * @param $numExpenses
     * @param $numMoneyOut
     * @param $numMoneyIn
     */
    private function generateMockReport($reportId, $numGifts, $numExpenses, $numMoneyOut, $numMoneyIn)
    {
        $this->mockReport = m::mock(ReportInterface::class);

        $this->mockReport->shouldReceive('getId')->andReturn($reportId);
        $this->mockReport->shouldReceive('getGifts')->andReturn(
            $this->generateMockTransactions(Gift::class, $numGifts)
        );
        $this->mockReport->shouldReceive('getExpenses')->andReturn(
            $this->generateMockTransactions(Expense::class, $numExpenses)
        );
        $this->mockReport->shouldReceive('getMoneyTransactionsOut')->andReturn(
            $this->generateMockTransactions(MoneyTransaction::class, $numMoneyOut)
        );
        $this->mockReport->shouldReceive('getMoneyTransactionsIn')->andReturn(
            $this->generateMockTransactions(MoneyTransaction::class, $numMoneyIn)
        );

    }

    /**
     * Generates $qty of Mock entited of class $class
     *
     * @param $class
     * @param $qty
     * @return array
     */
    private function generateMockTransactions($class, $qty)
    {
        $mocks = [];
        for($i=0; $i < $qty; $i++) {
            array_push($mocks, $this->generateMockTransactionEntity($class, $i));
        }
        return $mocks;
    }

    /**
     * Generates instance of mock Entity $class. Counter used to differentiate properties only.
     *
     * @param $class
     * @param $counter
     * @return mixed
     */
    private function generateMockTransactionEntity($class, $counter)
    {
        $mock = new $class();
        $mock->setAmount(10.00);
        //$mock->setIsJointAccount(true);
        switch($class) {
            case Gift::class:
                $mock->setExplanation('explanation for gift ' . $counter);
                break;
            case MoneyTransaction::class:

                // Assign Category based on counter
                $mock->setCategory(MoneyTransaction::$categories[min($counter, count(MoneyTransaction::$categories)-1)][0]);

                break;
        }


        // set all even numbers to have a bank account
        $bankAccount = ($counter%2 == 0) ? $this->generateBankAccount($counter) : null;
        $mock->setBankAccount($bankAccount);

        return $mock;

    }

    /**
     * Generates instance of mock bank account. $counter used to differentiate.
     *
     * @param $counter
     * @return \Mockery\Mock
     */
    private function generateBankAccount($counter)
    {
        $mockBankAccount = m::mock(BankAccount::class)->makePartial();
        $mockBankAccount->shouldReceive('getDisplayName')->andReturn('Bank' . $counter);

        return $mockBankAccount;
    }
}
