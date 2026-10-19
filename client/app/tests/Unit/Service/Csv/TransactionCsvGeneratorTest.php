<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\Csv;

use OPG\Digideps\Frontend\Entity\Client;
use OPG\Digideps\Frontend\Entity\Report\BankAccount;
use OPG\Digideps\Frontend\Entity\Report\Expense;
use OPG\Digideps\Frontend\Entity\Report\Gift;
use OPG\Digideps\Frontend\Entity\Report\MoneyTransaction;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Service\Csv\CsvBuilder;
use OPG\Digideps\Frontend\Service\Csv\TransactionsCsvGenerator;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class TransactionCsvGeneratorTest extends TestCase
{
    protected TransactionsCsvGenerator $sut;

    public function setUp(): void
    {
        $mockTranslator = self::createMock(TranslatorInterface::class);
        $mockTranslator->method('trans')
            ->with(new IsType(IsType::TYPE_STRING), [], 'report-money-transaction')
            ->willReturn('SomeCategory');

        $this->sut = new TransactionsCsvGenerator($mockTranslator, new CsvBuilder());
    }

    public function testGenerateTransactionsCsvNoTransactions(): void
    {
        $csvString = $this->sut->generateTransactionsCsv($this->generateMockReport());

        self::assertStringContainsString('Type,Category,Amount,"Bank name","Account details",Description', $csvString);
    }

    public function testGenerateTransactionsCsvWithTransactions(): void
    {
        $mockReport = $this->generateMockReport(
            20, // gifts
            20, // expenses
            50, // money out
            10 // money in
        );

        $csvString = $this->sut->generateTransactionsCsv($mockReport);

        self::assertStringContainsString('Type,Category,Amount,"Bank name","Account details",Description', $csvString);
        self::assertEquals(20, preg_match_all('/Gift/', $csvString));
        self::assertEquals(20, preg_match_all('/Expense/', $csvString));
        self::assertEquals(50, preg_match_all('/Money out/', $csvString));
        self::assertEquals(10, preg_match_all('/Money in/', $csvString));

        self::assertEquals(35, preg_match_all('/Custom bank name/', $csvString));
        self::assertEquals(35, preg_match_all('/\(\*\*\*\* 1234\) 12-34-56\)/', $csvString));
    }

    private function generateMockReport(
        int $numGifts = 0,
        int $numExpenses = 0,
        int $numMoneyOut = 0,
        int $numMoneyIn = 0
    ): Report&MockObject {
        $mockReport = self::createMock(Report::class);

        $mockReport->method('getId')->willReturn(99);
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
        $mockReport->method('getClient')->willReturn($this->generateMockClient());
        $mockReport->method('getDueDate')->willReturn(new \DateTime('2/5/2018'));
        $mockReport->method('getSubmitDate')->willReturn(new \DateTime('4/28/2018'));

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
        $mockTransactions = [];
        for ($i = 0; $i < $qty; $i++) {
            $mockTransactions[] = $this->generateMockTransactionEntity($class, $i);
        }
        return $mockTransactions;
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
            MoneyTransaction::class => new MoneyTransaction()->setAmount('10.00')
                ->setDescription('description for transaction ' . $counter)
                ->setCategory(MoneyTransaction::$categories[min($counter, count(MoneyTransaction::$categories) - 1)][0]),
            default => throw new \DomainException("Unsupported type: {$class}"),
        })->setBankAccount(($counter % 3 == 0) ? $this->generateBankAccount($counter) : null);
    }

    /**
     * Generates instance of mock bank account. $counter used to differentiate.
     */
    private function generateBankAccount(int $counter): BankAccount&MockObject
    {
        $mockBankAccount = self::createMock(BankAccount::class);
        $mockBankAccount->method('getDisplayName')->willReturn('(**** 1234) 12-34-56)');
        $mockBankAccount->method('getBank')->willReturn('Custom bank name ' . $counter);

        return $mockBankAccount;
    }

    private function generateMockClient(): Client&MockObject
    {
        $mockClient = self::createMock(Client::class);
        $mockClient->method('getFirstname')->willReturn('Firstname' . 32);
        $mockClient->method('getLastname')->willReturn('Lastname' . 32);
        $mockClient->method('getCaseNumber')->willReturn("32323232");
        $mockClient->method('getTotalReportCount')->willReturn(32);
        $mockClient->method('getUnsubmittedReportsCount')->willReturn(1);
        $mockClient->method('getCourtDate')->willReturn(new \DateTime('11/8/2011'));

        return $mockClient;
    }
}
