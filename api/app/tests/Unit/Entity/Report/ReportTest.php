<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Entity\Report;

use Doctrine\Common\Collections\ArrayCollection;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Report\AssetOther;
use OPG\Digideps\Backend\Entity\Report\AssetProperty;
use OPG\Digideps\Backend\Entity\Report\BankAccount;
use OPG\Digideps\Backend\Entity\Report\ClientBenefitsCheck;
use OPG\Digideps\Backend\Entity\Report\Expense;
use OPG\Digideps\Backend\Entity\Report\Fee;
use OPG\Digideps\Backend\Entity\Report\Gift;
use OPG\Digideps\Backend\Entity\Report\MoneyTransaction;
use OPG\Digideps\Backend\Entity\Report\MoneyTransactionShortIn;
use OPG\Digideps\Backend\Entity\Report\MoneyTransactionShortOut;
use OPG\Digideps\Backend\Entity\Report\ProfDeputyInterimCost;
use OPG\Digideps\Backend\Entity\Report\ProfDeputyOtherCost;
use OPG\Digideps\Backend\Entity\Report\ProfDeputyPreviousCost;
use OPG\Digideps\Backend\Entity\Report\Report;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReportTest extends TestCase
{
    public function testDueDate(): void
    {
        $report = new Report(new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime('2017-01-01'), new \DateTime('2018-12-31'));
        self::assertEquals('2019-01-21', $report->getDueDate()->format('Y-m-d'));
    }

    public function testConstructorClientAlreadyHasUnsubmittedReport(): void
    {
        // client already has an unsubmitted report
        $startDate = new \DateTime('2017-01-01');
        $endDate = new \DateTime('2018-12-31');

        $this->expectExceptionMessage('already has an unsubmitted report');

        $client = new Client();
        $report1 = new Report($client, Report::LAY_PFA_HIGH_ASSETS_TYPE, $startDate, $endDate);
        $client->addReport($report1);

        // this throws the exception as the client already has an unsubmitted report
        new Report($client, Report::LAY_PFA_HIGH_ASSETS_TYPE, $startDate, $endDate);
    }

    public function testConstructorClientAlreadyHasReportCoveringPeriod(): void
    {
        // if the client already has a report and a new report is created within its reporting period,
        // an exception is thrown
        $startDate = new \DateTime('2017-01-01');
        $endDate = new \DateTime('2018-12-31');
        $badStartDate = $endDate->sub(new \DateInterval('P10D'));

        $this->expectExceptionMessage('Incorrect start date');

        $client = new Client();
        $report1 = new Report($client, Report::LAY_PFA_HIGH_ASSETS_TYPE, $startDate, $endDate)
            ->setSubmitted(true);
        $client->addReport($report1);

        // this throws the exception as the client already has a report whose reporting period overlaps the new report's
        new Report($client, Report::LAY_PFA_HIGH_ASSETS_TYPE, $badStartDate, $endDate);
    }

    public function testGetMoneyTotal(): void
    {
        // 102
        $report1 = new Report(new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime('2017-01-01'), new \DateTime('2018-12-31'));
        self::assertEquals(0, $report1->getMoneyInTotal());
        self::assertEquals(0, $report1->getMoneyOutTotal());
        $report1->setMoneyTransactions(new ArrayCollection([
            new MoneyTransaction($report1, 'account-interest')->setAmount(1),
            new MoneyTransaction($report1, 'dividends')->setAmount(2),
            new MoneyTransaction($report1, 'broadband')->setAmount(3),
            new MoneyTransaction($report1, 'food')->setAmount(4),
        ]));
        self::assertEquals(1 + 2, $report1->getMoneyInTotal());
        self::assertEquals(3 + 4, $report1->getMoneyOutTotal());

        // 103
        $report2 = new Report(new Client(), Report::LAY_PFA_LOW_ASSETS_TYPE, new \DateTime('2017-01-01'), new \DateTime('2018-12-31'));
        self::assertEquals(0, $report2->getMoneyInTotal());
        self::assertEquals(0, $report2->getMoneyOutTotal());
        $report2->getMoneyTransactionsShort()->clear();
        $report2->getMoneyTransactionsShort()->add(new MoneyTransactionShortIn($report2)->setAmount(10));
        $report2->getMoneyTransactionsShort()->add(new MoneyTransactionShortIn($report2)->setAmount(20));
        $report2->getMoneyTransactionsShort()->add(new MoneyTransactionShortOut($report2)->setAmount(30));
        $report2->getMoneyTransactionsShort()->add(new MoneyTransactionShortOut($report2)->setAmount(40));
        self::assertEquals(10 + 20, $report2->getMoneyInTotal());
        self::assertEquals(30 + 40, $report2->getMoneyOutTotal());
    }

    public function testGetAccountsOpeningBalanceTotal(): void
    {
        $report = new Report(new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime('2017-01-01'), new \DateTime('2018-12-31'));
        self::assertEquals(0, $report->getAccountsOpeningBalanceTotal());

        $report->addAccount(new BankAccount($report)->setBank('bank1')->setOpeningBalance('1'));
        $report->addAccount(new BankAccount($report)->setBank('bank2')->setOpeningBalance('3'));
        $report->addAccount(new BankAccount($report)->setBank('bank3')->setOpeningBalance('0'));

        self::assertEquals(4, $report->getAccountsOpeningBalanceTotal());
    }

    public function testGetAccountsClosingBalanceTotal(): void
    {
        $report = new Report(new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime('2017-01-01'), new \DateTime('2018-12-31'));

        self::assertEquals(0, $report->getAccountsClosingBalanceTotal());

        $report->addAccount(new BankAccount($report)->setBank('bank1')->setClosingBalance('1'));

        self::assertEquals(1, $report->getAccountsClosingBalanceTotal());

        $report->addAccount(new BankAccount($report)->setBank('bank2')->setClosingBalance('3'));
        $report->addAccount(new BankAccount($report)->setBank('bank3')->setClosingBalance('0'));

        self::assertEquals(4, $report->getAccountsClosingBalanceTotal());
    }

    public function testGetCalculatedBalance(): void
    {
        $report = new Report(new Client(), Report::PROF_PFA_HIGH_ASSETS_TYPE, new \DateTime('2017-06-23'), new \DateTime('2018-06-22'));
        self::assertFalse($report->has106Flag());

        self::assertEquals(0, $report->getCalculatedBalance());

        $report->addAccount(new BankAccount($report)->setBank('bank1')->setOpeningBalance('1'));

        self::assertEquals(1, $report->getCalculatedBalance());

        $report->addMoneyTransaction(new MoneyTransaction($report, 'account-interest')->setAmount(20)); // in
        $report->addMoneyTransaction(new MoneyTransaction($report, 'account-interest')->setAmount(20)); // in
        $report->addMoneyTransaction(new MoneyTransaction($report, 'rent')->setAmount(15)); // out
        $report->addMoneyTransaction(new MoneyTransaction($report, 'rent')->setAmount(15)); // out

        $gift1 = new Gift($report, 'present')->setAmount('2');
        $gift2 = new Gift($report, 'bonus')->setAmount('20');
        $report->setGifts(new ArrayCollection([$gift1, $gift2]));

        $expense1 = new Expense($report, 'car')->setAmount('1');
        $expense2 = new Expense($report, 'stationery')->setAmount('10');
        $report->setExpenses(new ArrayCollection([$expense1, $expense2]));

        $calculatedBalance = 1 + 20 + 20 - 15 - 15 - 22 - 11;

        self::assertEquals($calculatedBalance, $report->getCalculatedBalance());
    }

    public function testGetCalculatedBalanceProfDeputy(): void
    {
        $report = new Report(new Client(), Report::PROF_PFA_HIGH_ASSETS_TYPE, new \DateTime('2017-06-23'), new \DateTime('2018-06-22'));

        self::assertEquals(0, $report->getCalculatedBalance());

        $report->setProfDeputyCostsHowCharged('fixed');
        $report->setProfDeputyCostsHasPrevious('yes');
        $report->setProfDeputyPreviousCosts(new ArrayCollection([
            new ProfDeputyPreviousCost($report, 1),
            new ProfDeputyPreviousCost($report, 1),
        ]));
        $report->setProfDeputyCostsHasInterim('no');
        $report->setProfDeputyFixedCost(3);
        $report->setProfDeputyOtherCosts(new ArrayCollection([
            new ProfDeputyOtherCost($report, 'id1', false, '10'),
            new ProfDeputyOtherCost($report, 'id2', false, '10'),
        ]));

        self::assertEquals(-1 - 1 - 3 - 10 - 10, $report->getCalculatedBalance());

        // change interim yes->no
        $report->setProfDeputyCostsHasInterim('yes');
        $report->setProfDeputyInterimCosts(new ArrayCollection([
            new ProfDeputyInterimCost($report, new \DateTime('now'), '11'),
            new ProfDeputyInterimCost($report, new \DateTime('now'), '11'),
        ]));

        self::assertEquals(-1 - 1 - 11 - 11 - 10 - 10, $report->getCalculatedBalance());
    }

    /**
     * //TODO consider rewriting, unit testing methods composing the total
     * (see testgetExpensesTotal as an example) and using mocks here.
     */
    public function testGetTotalsOffsetAndMatch(): void
    {
        $report = new Report(new Client(), Report::PROF_PFA_HIGH_ASSETS_TYPE, new \DateTime('2017-06-23'), new \DateTime('2018-06-22'));

        self::assertEquals(0, $report->getTotalsOffset());
        self::assertTrue($report->getTotalsMatch());

        // account opened with 1000, closed with 2000. 1500 money in, 400 out. balance is 100
        $report->addAccount(new BankAccount($report)->setBank('bank1')->setOpeningBalance('1000')->setClosingBalance('2000'));
        $report->addMoneyTransaction(new MoneyTransaction($report, 'account-interest')->setAmount(1500)); // in
        $report->addMoneyTransaction(new MoneyTransaction($report, 'rent')->setAmount(400)); // out

        $gift1 = new Gift($report, 'present')->setAmount('2');
        $gift2 = new Gift($report, 'bonus')->setAmount('20');
        $report->setGifts(new ArrayCollection([$gift1, $gift2]));

        $expense1 = new Expense($report, 'car')->setAmount('1');
        $expense2 = new Expense($report, 'stationery')->setAmount('10');
        $report->setExpenses(new ArrayCollection([$expense1, $expense2]));

        $expectedTotalOffset = 67; // 1000 - 2000 + 1500 - 400 - 11 - 22

        self::assertEquals($expectedTotalOffset, $report->getTotalsOffset());
        self::assertFalse($report->getTotalsMatch());

        // add missing transaction that fix the balance
        $report->addMoneyTransaction(new MoneyTransaction($report, 'rent')->setAmount(67)); // in

        self::assertEquals(0, $report->getTotalsOffset());
        self::assertTrue($report->getTotalsMatch());
    }

    public function testGetFeesTotal(): void
    {
        $report = new Report(new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime('2017-06-23'), new \DateTime('2018-06-22'));
        self::assertEquals(0, $report->getFeesTotal());

        $fee1 = new Fee($report, 'annual-management-fee')->setAmount('2');
        $report->addFee($fee1);

        $fee2 = new Fee($report, 'travel-costs')->setAmount('4');
        $report->addFee($fee2);

        self::assertEquals(2.0 + 4.0, $report->getFeesTotal());
    }

    public function testGetExpensesTotal(): void
    {
        $report = new Report(new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime('2017-06-23'), new \DateTime('2018-06-22'));
        self::assertEquals(0, $report->getExpensesTotal());

        $expense1 = new Expense($report, 'car')->setAmount('1');
        $report->addExpense($expense1);

        $expense2 = new Expense($report, 'stationery')->setAmount('2');
        $report->addExpense($expense2);

        self::assertEquals(1.0 + 2.0, $report->getExpensesTotal());
    }

    public function testGetAssetsTotalValue(): void
    {
        $report = new Report(new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime('2017-06-23'), new \DateTime('2018-06-22'));
        self::assertEquals(0, $report->getAssetsTotalValue());

        $report->addAsset(new AssetOther($report)->setValue('1'));
        $report->addAsset(new AssetProperty($report)->setValue('2'));

        self::assertEquals(3, $report->getAssetsTotalValue());
    }

    public static function sectionsSettingsProvider(): array
    {
        return [
            [Report::LAY_PFA_HIGH_ASSETS_TYPE, ['bankAccounts', 'moneyIn', 'balance', 'clientBenefitsCheck'], ['moneyInShort', 'lifestyle']],
            [Report::LAY_PFA_LOW_ASSETS_TYPE, ['bankAccounts', 'moneyInShort', 'clientBenefitsCheck'], ['moneyIn', 'lifestyle', 'balance']],
            [Report::LAY_HW_TYPE, ['lifestyle'], ['bankAccounts', 'moneyIn', 'moneyInShort', 'gifts', 'balance', 'clientBenefitsCheck']],
        ];
    }

    /**
     * Some checks that the config array doesn't get messed up.
     */
    #[DataProvider('sectionsSettingsProvider')]
    public function testAvailableSectionsAndHasSection(string $type, array $expectedSections, array $unExpectedSections): void
    {
        $report = new Report(new Client(), $type, new \DateTime('2017-06-23'), new \DateTime('2018-06-22'))
            ->setBenefitsSectionReleaseDate(new \DateTime('2016-01-01'));

        foreach ($expectedSections as $section) {
            $this->assertContains($section, $report->getAvailableSections());
            $this->assertTrue($report->hasSection($section));
        }
        foreach ($unExpectedSections as $section) {
            $this->assertNotContains($section, $report->getAvailableSections(), "$type should NOT have $section section ");
            $this->assertFalse($report->hasSection($section));
        }
    }

    public function testGetPreviousReportData(): void
    {
        $client = new Client();
        $now = new \DateTime();

        $reportTwoYearsAgo = new Report($client, Report::PROF_COMBINED_LOW_ASSETS_TYPE, $now, $now);
        $reportTwoYearsAgo->setId(8);


        $reportLastYear = new Report($client, Report::LAY_PFA_HIGH_ASSETS_TYPE, $now, $now);
        $reportLastYear->setId(9);


        $reportLatest = new Report($client, Report::LAY_PFA_LOW_ASSETS_TYPE, $now, $now);
        $reportLatest->setId(10);

        $client->addReport($reportTwoYearsAgo);
        $client->addReport($reportLastYear);
        $client->addReport($reportLatest);

        $bankAccount0 = new BankAccount($reportTwoYearsAgo);
        $bankAccount0->setId(1);
        $bankAccount0->setBank('bank0');
        $bankAccount0->setAccountNumber('1111');
        $bankAccount0->setSortCode('111111');
        $bankAccount0->setOpeningBalance('600');
        $bankAccount0->setClosingBalance('600');
        $reportTwoYearsAgo->addAccount($bankAccount0);

        $bankAccount1 = new BankAccount($reportLastYear);
        $bankAccount1->setId(2);
        $bankAccount1->setBank('bank1');
        $bankAccount1->setAccountNumber('2222');
        $bankAccount1->setSortCode('222222');
        $bankAccount1->setOpeningBalance('200');
        $bankAccount1->setClosingBalance('300');
        $reportLastYear->addAccount($bankAccount1);

        $bankAccount2 = new BankAccount($reportLastYear);
        $bankAccount2->setId(3);
        $bankAccount2->setBank('bank2');
        $bankAccount2->setAccountNumber('3333');
        $bankAccount2->setSortCode('333333');
        $bankAccount2->setOpeningBalance('700');
        $bankAccount2->setClosingBalance('500');
        $reportLastYear->addAccount($bankAccount2);

        // assert empty as no report prior to the first report, completed two years ago
        $this->assertEmpty($reportTwoYearsAgo->getPreviousReportData());

        // this should be the report from two years ago
        $report1PreviousData = $reportLastYear->getPreviousReportData();

        $this->assertArrayHasKey('financial-summary', $report1PreviousData);
        $this->assertArrayHasKey('report-summary', $report1PreviousData);
        self::assertEquals(
            Report::PROF_COMBINED_LOW_ASSETS_TYPE,
            $report1PreviousData['report-summary']['type']
        );

        $this->assertCount(1, $report1PreviousData['financial-summary']['accounts']);
        $this->assertArrayHasKey('opening-balance-total', $report1PreviousData['financial-summary']);
        $this->assertArrayHasKey('closing-balance-total', $report1PreviousData['financial-summary']);
        self::assertEquals(
            $report1PreviousData['financial-summary']['opening-balance-total'],
            $report1PreviousData['financial-summary']['closing-balance-total']
        );

        self::assertEquals(
            'bank0',
            $report1PreviousData['financial-summary']['accounts'][$bankAccount0->getId()]['bank']
        );
        $this->assertArrayHasKey('nameOneLine', $report1PreviousData['financial-summary']['accounts'][$bankAccount0->getId()]);
        self::assertEquals($report1PreviousData['financial-summary']['closing-balance-total'], $bankAccount0->getClosingBalance());

        // assert current report contains report1 data
        $currentReportPreviousData = $reportLatest->getPreviousReportData();
        $this->assertArrayHasKey('financial-summary', $currentReportPreviousData);
        $this->assertArrayHasKey('report-summary', $report1PreviousData);

        $this->assertCount(2, $currentReportPreviousData['financial-summary']['accounts']);
        self::assertEquals(
            'bank1',
            $currentReportPreviousData['financial-summary']['accounts'][$bankAccount1->getId()]['bank']
        );
        self::assertEquals(
            'bank2',
            $currentReportPreviousData['financial-summary']['accounts'][$bankAccount2->getId()]['bank']
        );
        self::assertEquals(
            '102',
            $currentReportPreviousData['report-summary']['type']
        );
        $this->assertArrayHasKey('nameOneLine', $currentReportPreviousData['financial-summary']['accounts'][$bankAccount1->getId()]);
        self::assertEquals(
            $currentReportPreviousData['financial-summary']['closing-balance-total'],
            (float)$bankAccount1->getClosingBalance() + (float)$bankAccount2->getClosingBalance()
        );
    }

    public static function reportTypeTranslationKeyProvider(): array
    {
        return [
            // Lay deputies
            ['102', 'propertyAffairsGeneral'],
            ['103', 'propertyAffairsMinimal'],
            ['104', 'healthWelfare'],
            ['103-4', 'propertyAffairsMinimalHealthWelfare'],
            ['102-4', 'propertyAffairsGeneralHealthWelfare'],

            // PA
            ['102-6', 'propertyAffairsGeneral'],
            ['103-6', 'propertyAffairsMinimal'],
            ['104-6', 'healthWelfare'],
            ['103-4-6', 'propertyAffairsMinimalHealthWelfare'],
            ['102-4-6', 'propertyAffairsGeneralHealthWelfare'],

            // Professional
            ['102-5', 'propertyAffairsGeneral'],
            ['103-5', 'propertyAffairsMinimal'],
            ['104-5', 'healthWelfare'],
            ['103-4-5', 'propertyAffairsMinimalHealthWelfare'],
            ['102-4-5', 'propertyAffairsGeneralHealthWelfare'],
        ];
    }

    #[DataProvider('reportTypeTranslationKeyProvider')]
    public function testGetReportTitle(string $reportType, string $expected): void
    {
        $report = new Report(new Client(), $reportType, new \DateTime(), new \DateTime());
        self::assertEquals($expected, $report->getReportTitle());
    }

    public function testInvalidAgreedBehalfOption(): void
    {
        $report = new Report(new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime(), new \DateTime());
        $this->expectException(\InvalidArgumentException::class);
        $report->setAgreedBehalfDeputy('BAD_VALUE');
    }

    public function testValidAgreedBehalfOptions(): void
    {
        $report = new Report(new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime(), new \DateTime());
        $values = ['not_deputy', 'only_deputy', 'more_deputies_behalf', 'more_deputies_not_behalf'];
        foreach ($values as $value) {
            $report->setAgreedBehalfDeputy($value);
            self::assertEquals($report->getAgreedBehalfDeputy(), $value);
        }
    }

    public static function reportTypesWithEndDateProvider(): array
    {
        return [
            // lay post (21 days)
            ['102', '2019-11-13', '2019-12-04'],
            // non-lay (56 days)
            ['102-5', '2019-11-12', '2020-01-07'],
        ];
    }

    public static function layReportTypesProvider(): array
    {
        return [
            // Lay deputies
            ['102', true],
            ['103', true],
            ['104', true],
            ['103-4', true],
            ['102-4', true],
            // PA
            ['102-6', false],
            ['103-6', false],
            ['104-6', false],
            ['103-4-6', false],
            ['102-4-6', false],
            // Professional
            ['102-5', false],
            ['103-5', false],
            ['104-5', false],
            ['103-4-5', false],
            ['102-4-5', false],
        ];
    }

    #[DataProvider('layReportTypesProvider')]
    public function testIsLayReport(string $type, bool $expectedResult): void
    {
        $client = new Client();
        $endDate = new \DateTime();
        $startDate = clone $endDate;
        $startDate = $startDate->modify('-1 year');

        $report = new Report($client, $type, $startDate, $endDate);
        self::assertEquals($expectedResult, $report->isLayReport());
    }

    #[DataProvider('reportTypesWithEndDateProvider')]
    public function testUpdateDuetDateBasedOnEndDate(string $type, string $endDate, string $expectedDueDate): void
    {
        $client = new Client();
        $endDate = new \DateTime($endDate);
        $startDate = clone $endDate;
        $startDate = $startDate->modify('-1 year');

        $report = new Report($client, $type, $startDate, $endDate);

        $report->updateDueDateBasedOnEndDate();

        self::assertEquals($expectedDueDate, $report->getDueDate()->format('Y-m-d'));
        self::assertEquals($endDate, $report->getEndDate());
        self::assertEquals($startDate, $report->getStartDate());
    }

    #[DataProvider('benefitsCheckSectionRequiredProvider')]
    public function testRequiresBenefitsCheckSection(
        \DateTime $featureFlagDate,
        \DateTime $dueDate,
        ?ClientBenefitsCheck $clientBenefitSection,
        ?\DateTime $unsubmitDate,
        bool $expectedResult
    ): void {
        $report = new Report(new Client(), Report::LAY_PFA_LOW_ASSETS_TYPE, new \DateTime(), new \DateTime(), false)
            ->setDueDate($dueDate)
            ->setClientBenefitsCheck($clientBenefitSection)
            ->setUnSubmitDate($unsubmitDate)
            ->setBenefitsSectionReleaseDate($featureFlagDate);

        self::assertEquals(
            $expectedResult,
            $report->requiresBenefitsCheckSection()
        );
    }

    public static function benefitsCheckSectionRequiredProvider(): array
    {
        $featureFlagDate = new \DateTimeImmutable('01/01/2021');
        $unsubmitDate = new \DateTime();

        return [
            'Due date 61 days after feature launch date' => [
                \DateTime::createFromImmutable($featureFlagDate),
                \DateTime::createFromImmutable($featureFlagDate->modify('+61 days')),
                null,
                null,
                true,
            ],
            'Due date 60 days after feature launch date' => [
                \DateTime::createFromImmutable($featureFlagDate),
                \DateTime::createFromImmutable($featureFlagDate->modify('+60 days')),
                null,
                null,
                false,
            ],
            'Due date 1 day before feature launch date' => [
                \DateTime::createFromImmutable($featureFlagDate),
                \DateTime::createFromImmutable($featureFlagDate->modify('-1 day')),
                null,
                null,
                false,
            ],
            'Due date 61 days after feature launch date, report unsubmitted but section not previously completed' => [
                \DateTime::createFromImmutable($featureFlagDate),
                \DateTime::createFromImmutable($featureFlagDate->modify('+61 days')),
                null,
                $unsubmitDate,
                false,
            ],
            'Due date 61 days after feature launch date, report unsubmitted but section previously completed' => [
                \DateTime::createFromImmutable($featureFlagDate),
                \DateTime::createFromImmutable($featureFlagDate->modify('+61 days')),
                new ClientBenefitsCheck(),
                $unsubmitDate,
                true,
            ],
            'Due date 1 day before feature launch date, report unsubmitted but section previously completed' => [
                \DateTime::createFromImmutable($featureFlagDate),
                \DateTime::createFromImmutable($featureFlagDate->modify('-1 days')),
                new ClientBenefitsCheck(),
                $unsubmitDate,
                true,
            ],
        ];
    }
}
