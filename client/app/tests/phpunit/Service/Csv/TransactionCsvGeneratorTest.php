<?php

declare(strict_types=1);

namespace App\Service\Csv;

use App\Entity\Client;
use App\Entity\Report\BankAccount;
use App\Entity\Report\Expense;
use App\Entity\Report\Gift;
use App\Entity\Report\MoneyTransaction;
use App\Entity\ReportInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use MockeryStub as m;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class TransactionCsvGeneratorTest extends MockeryTestCase
{
    /** @var TransactionsCsvGenerator */
    protected $sut;

    private $mockTranslator;
    private $mockReport;

    /**
     * Set up the mockservies.
     */
    public function setUp(): void
    {
        $this->mockTranslator = m::mock(Translator::class);

        $this->mockTranslator->shouldReceive('trans')->with(m::type('string'), [], 'report-money-transaction')
            ->andReturn('SomeCategory');
        $this->sut = new TransactionsCsvGenerator($this->mockTranslator, new CsvBuilder());
    }

    public function testGenerateTransactionsCsvNoTransactions()
    {
        $this->mockReport = $this->generateMockReport(99);

        $csvString = $this->sut->generateTransactionsCsv($this->mockReport);
        $this->assertStringContainsString('Type,Category,Amount,"Bank name","Account details",Description', $csvString);
    }

    public function testGenerateTransactionsCsvWtihTransactions()
    {
        $this->mockReport = $this->generateMockReport(
            99,
            20, // gifts
            20, // expenses
            50, // money out
            10 // money in
        );

        $csvString = $this->sut->generateTransactionsCsv($this->mockReport);
        $this->assertStringContainsString('Type,Category,Amount,"Bank name","Account details",Description', $csvString);
        $this->assertEquals(20, preg_match_all('/Gift/', $csvString));
        $this->assertEquals(20, preg_match_all('/Expense/', $csvString));
        $this->assertEquals(50, preg_match_all('/Money out/', $csvString));
        $this->assertEquals(10, preg_match_all('/Money in/', $csvString));

        $this->assertEquals(35, preg_match_all('/Custom bank name/', $csvString));
        $this->assertEquals(35, preg_match_all('/\(\*\*\*\* 1234\) 12-34-56\)/', $csvString));
    }

    /**
     * Generates a mock Report with id and associated transactions.
     */
    private function generateMockReport(
        $reportId,
        $numGifts = 0,
        $numExpenses = 0,
        $numMoneyOut = 0,
        $numMoneyIn = 0,
        $dueDate = '2/5/2018',
        $submitDate = '4/28/2018',
    ) {
        $mockReport = m::mock(ReportInterface::class);

        $mockReport->shouldReceive('getId')->andReturn($reportId);
        $mockReport->shouldReceive('getGifts')->andReturn(
            $this->generateMockTransactions(Gift::class, $numGifts)
        );
        $mockReport->shouldReceive('getExpenses')->andReturn(
            $this->generateMockTransactions(Expense::class, $numExpenses)
        );
        $mockReport->shouldReceive('getMoneyTransactionsOut')->andReturn(
            $this->generateMockTransactions(MoneyTransaction::class, $numMoneyOut)
        );
        $mockReport->shouldReceive('getMoneyTransactionsIn')->andReturn(
            $this->generateMockTransactions(MoneyTransaction::class, $numMoneyIn)
        );
        $mockReport->shouldReceive('getType')->andReturn(102);

        $mockReport->shouldReceive('getClient')->andReturn(
            $this->generateMockClient(32)
        );

        if (!empty($dueDate)) {
            $mockReport->shouldReceive('getDueDate')->andReturn(new \DateTime($dueDate));
        } else {
            $mockReport->shouldReceive('getDueDate')->andReturnNull();
        }

        if (!empty($submitDate)) {
            $mockReport->shouldReceive('getSubmitDate')->andReturn(new \DateTime($submitDate));
        } else {
            $mockReport->shouldReceive('getSubmitDate')->andReturnNull();
        }

        return $mockReport;
    }

    /**
     * Generates $qty of Mock entited of class $class.
     *
     * @return array
     */
    private function generateMockTransactions($class, $qty)
    {
        $mocks = [];
        for ($i = 0; $i < $qty; ++$i) {
            array_push($mocks, $this->generateMockTransactionEntity($class, $i));
        }

        return $mocks;
    }

    /**
     * Generates instance of mock Entity $class. Counter used to differentiate properties only.
     */
    private function generateMockTransactionEntity($class, $counter)
    {
        $mock = new $class();
        $mock->setAmount('10.00');

        switch ($class) {
            case Gift::class:
                $mock->setExplanation('explanation for gift '.$counter);
                break;
            case Expense::class:
                $mock->setExplanation('explanation for expense '.$counter);
                break;
            case MoneyTransaction::class:
                // Assign Category based on counter
                $mock->setCategory(MoneyTransaction::$categories[min($counter, count(MoneyTransaction::$categories) - 1)][0]);
                $mock->setDescription('description for transaction '.$counter);

                break;
        }

        // set all even numbers to have a bank account
        $bankAccount = (0 == $counter % 3) ? $this->generateBankAccount($counter) : null;
        $mock->setBankAccount($bankAccount);

        return $mock;
    }

    /**
     * Generates instance of mock bank account. $counter used to differentiate.
     *
     * @return \Mockery\LegacyMockInterface
     */
    private function generateBankAccount($counter)
    {
        $mockBankAccount = m::mock(BankAccount::class)->makePartial();
        $mockBankAccount->shouldReceive('getDisplayName')->andReturn('(**** 1234) 12-34-56)');
        $mockBankAccount->shouldReceive('getBank')->andReturn('Custom bank name '.$counter);

        return $mockBankAccount;
    }

    /**
     * Generates instance of mock user.
     *
     * @return \Mockery\LegacyMockInterface
     */
    private function generateMockClient($counter)
    {
        $mock = m::mock(Client::class)->makePartial();
        $mock->shouldReceive('getFirstname')->andReturn('Firstname'.$counter);
        $mock->shouldReceive('getLastname')->andReturn('Lastname'.$counter);
        $mock->shouldReceive('getCaseNumber')->andReturn($counter.$counter.$counter.$counter);
        $mock->shouldReceive('getTotalReportCount')->andReturn($counter * 2);
        $mock->shouldReceive('getUnsubmittedReportsCount')->andReturn(1);
        $mock->shouldReceive('getCourtDate')->andReturn(new \DateTime('11/8/2011'));

        return $mock;
    }
}
