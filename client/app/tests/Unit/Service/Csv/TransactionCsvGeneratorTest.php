<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\Csv;

use OPG\Digideps\Frontend\Entity\Client;
use OPG\Digideps\Frontend\Entity\Report\BankAccount;
use OPG\Digideps\Frontend\Entity\Report\Expense;
use OPG\Digideps\Frontend\Entity\Report\Gift;
use OPG\Digideps\Frontend\Entity\Report\MoneyTransaction;
use OPG\Digideps\Frontend\Entity\Report\Report;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use OPG\Digideps\Frontend\Service\Csv\CsvBuilder;
use OPG\Digideps\Frontend\Service\Csv\TransactionsCsvGenerator;
use Symfony\Contracts\Translation\TranslatorInterface;

class TransactionCsvGeneratorTest extends TestCase
{
    protected TransactionsCsvGenerator $sut;

    private MockObject&Report $mockReport;

    public function setUp(): void
    {
        $mockTranslator = $this->createMock(TranslatorInterface::class);
        $mockTranslator->method('trans')->with(new IsType(IsType::TYPE_STRING), [], 'report-money-transaction')
            ->willReturn('SomeCategory');
        $this->sut = new TransactionsCsvGenerator($mockTranslator, new CsvBuilder());
    }

    public function testGenerateTransactionsCsvNoTransactions(): void
    {
        $this->mockReport = $this->generateMockReport(99, 0, 0, 0, 0);

        $csvString = $this->sut->generateTransactionsCsv($this->mockReport);
        $this->assertStringContainsString('Type,Category,Amount,"Bank name","Account details",Description', $csvString);
    }

    public function testGenerateTransactionsCsvWithTransactions(): void
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
     * Generates a mock Report with id and associated transactions
     */
    private function generateMockReport(
        int $reportId,
        int $numGifts = 0,
        int $numExpenses = 0,
        int $numMoneyOut = 0,
        int $numMoneyIn = 0,
        string $dueDate = '2/5/2018',
        string $submitDate = '4/28/2018'
    ): MockObject&Report {
        $mockReport = $this->createMock(Report::class);

        $mockReport->method('getId')->willReturn($reportId);
        $mockReport->method('getGifts')->willReturn(
            $this->generateMockTransactions(Gift::class, $numGifts)
        );
        $mockReport->method('getExpenses')->willReturn(
            $this->generateMockTransactions(Expense::class, $numExpenses)
        );
        $mockReport->method('getMoneyTransactionsOut')->willReturn(
            $this->generateMockTransactions(MoneyTransaction::class, $numMoneyOut)
        );
        $mockReport->method('getMoneyTransactionsIn')->willReturn(
            $this->generateMockTransactions(MoneyTransaction::class, $numMoneyIn)
        );
        $mockReport->method('getType')->willReturn(102);

        $mockReport->method('getClient')->willReturn(
            $this->generateMockClient(32)
        );

        if (!empty($dueDate)) {
            $mockReport->method('getDueDate')->willReturn(new \DateTime($dueDate));
        } else {
            $mockReport->method('getDueDate')->willReturn(null);
        }

        if (!empty($submitDate)) {
            $mockReport->method('getSubmitDate')->willReturn(new \DateTime($submitDate));
        } else {
            $mockReport->method('getSubmitDate')->willReturn(null);
        }

        return $mockReport;
    }

    /**
     * Generates $qty of Mocks of class $class
     *
     * @param class-string<Gift|Expense|MoneyTransaction> $class
     * @return array<Gift|Expense|MoneyTransaction>
     */
    private function generateMockTransactions(string $class, int $qty): array
    {
        $mocks = [];
        for ($i = 0; $i < $qty; $i++) {
            $mocks[] = $this->generateMockTransactionEntity($class, $i);
        }
        return $mocks;
    }

    /**
     * Generates instance of mock Entity $class. Counter used to differentiate properties only.
     *
     * @param class-string<Gift|Expense|MoneyTransaction> $class
     */
    private function generateMockTransactionEntity(string $class, int $counter): Gift|Expense|MoneyTransaction
    {
        return (match ($class) {
            Gift::class => new Gift()->setAmount('10.00')->setExplanation('explanation for gift ' . $counter),
            Expense::class => new Expense()->setAmount('10.00')->setExplanation('explanation for expense ' . $counter),
            MoneyTransaction::class => new MoneyTransaction()->setAmount('10.00')->setDescription('description for transaction ' . $counter)
                ->setCategory(MoneyTransaction::$categories[min($counter, count(MoneyTransaction::$categories) - 1)][0]),
            default => throw new \DomainException("Unsupported type: {$class}"),
        })->setBankAccount(($counter % 3 == 0) ? $this->generateBankAccount($counter) : null);
    }

    /**
     * Generates instance of mock bank account. $counter used to differentiate.
     */
    private function generateBankAccount(int $counter): BankAccount&MockObject
    {
        $mockBankAccount = $this->createMock(BankAccount::class);
        $mockBankAccount->method('getDisplayName')->willReturn('(**** 1234) 12-34-56)');
        $mockBankAccount->method('getBank')->willReturn('Custom bank name ' . $counter);

        return $mockBankAccount;
    }

    private function generateMockClient(int $counter): Client&MockObject
    {
        $mock = $this->createMock(Client::class);
        $mock->method('getFirstname')->willReturn('Firstname' . $counter);
        $mock->method('getLastname')->willReturn('Lastname' . $counter);
        $mock->method('getCaseNumber')->willReturn($counter . $counter . $counter . $counter);
        $mock->method('getTotalReportCount')->willReturn($counter * 2);
        $mock->method('getUnsubmittedReportsCount')->willReturn(1);
        $mock->method('getCourtDate')->willReturn(new \DateTime('11/8/2011'));

        return $mock;
    }
}
