<?php

namespace App\Tests\Unit\Entity\Report;

use App\Entity\Client;
use App\Entity\Ndr\Ndr;
use App\Entity\Report\AssetOther;
use App\Entity\Report\AssetProperty;
use App\Entity\Report\BankAccount;
use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\Expense;
use App\Entity\Report\Fee;
use App\Entity\Report\Gift;
use App\Entity\Report\MoneyTransaction;
use App\Entity\Report\MoneyTransactionShortIn;
use App\Entity\Report\MoneyTransactionShortOut;
use App\Entity\Report\ProfDeputyInterimCost;
use App\Entity\Report\ProfDeputyOtherCost;
use App\Entity\Report\ProfDeputyPreviousCost;
use App\Entity\Report\Report;
use App\TestHelpers\ReportTestHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\MockInterface;
use MockeryStub as m;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ReportTest extends KernelTestCase
{
    private MockInterface&Client $client;
    private array $validReportCtorArgs;
    private MockInterface|Report $report;
    private MockInterface&Gift $gift1;
    private MockInterface&Gift $gift2;
    private MockInterface&Expense $expense1;
    private MockInterface&Expense $expense2;
    private EntityManagerInterface $em;

    public function setUp(): void
    {
        $this->client = m::mock(Client::class, ['getUnsubmittedReports' => new ArrayCollection(), 'getSubmittedReports' => new ArrayCollection()]);
        $this->validReportCtorArgs = [$this->client, Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime('2017-06-23'), new \DateTime('2018-06-22')];
        $this->report = m::mock(Report::class.'[has106Flag]', $this->validReportCtorArgs);

        $this->gift1 = m::mock(Gift::class, ['getAmount' => 1]);
        $this->gift2 = m::mock(Gift::class, ['getAmount' => 10]);
        $this->expense1 = m::mock(Expense::class, ['getAmount' => 2]);
        $this->expense2 = m::mock(Expense::class, ['getAmount' => 20]);

        $kernel = self::bootKernel();
        $this->em = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testDueDate()
    {
        $startDate = new \DateTime('2017-01-01');
        $endDate = new \DateTime('2018-12-31');

        $report = new Report($this->client, Report::LAY_PFA_HIGH_ASSETS_TYPE, $startDate, $endDate, false);
        $this->assertEquals('2019-01-21', $report->getDueDate()->format('Y-m-d'));
    }

    public static function constructorProvider()
    {
        return [
            // start date, end date, submitted (true/false)
            ['2017-06-23', '2018-06-22', [['2016-06-23', '2017-06-22', false]], 'unsubmitted report'],
            // ['2017-06-23', '2018-06-24', [['2016-06-23', '2017-06-22', true]], 'cannot cover more than one year'],
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
            $report = (new Report($this->client, Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime($rep[0]), new \DateTime($rep[1])))->setSubmitted($rep[2]);
            $client->addReport($report);
        }

        $this->expectException(\RuntimeException::class);

        new Report($client, Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime($startDate), new \DateTime($endDate));
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
        $this->assertEquals(1 + 2, $this->report->getMoneyInTotal());
        $this->assertEquals(3 + 4, $this->report->getMoneyOutTotal());

        // 103
        $this->report->setType(Report::LAY_PFA_LOW_ASSETS_TYPE);
        $this->assertEquals(0, $this->report->getMoneyInTotal());
        $this->assertEquals(0, $this->report->getMoneyOutTotal());
        $this->report->setMoneyTransactionsShort(new ArrayCollection([
            (new MoneyTransactionShortIn($this->report))->setAmount(10),
            (new MoneyTransactionShortIn($this->report))->setAmount(20),
            (new MoneyTransactionShortOut($this->report))->setAmount(30),
            (new MoneyTransactionShortOut($this->report))->setAmount(40),
        ]));
        $this->assertEquals(10 + 20, $this->report->getMoneyInTotal());
        $this->assertEquals(30 + 40, $this->report->getMoneyOutTotal());
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
        $this->validReportCtorArgs = [$this->client, Report::PROF_PFA_HIGH_ASSETS_TYPE, new \DateTime('2017-06-23'), new \DateTime('2018-06-22')];
        $this->report = m::mock(Report::class.'[has106Flag]', $this->validReportCtorArgs);

        $this->report->shouldReceive('has106Flag')->andReturn(false);

        $this->assertEquals(0, $this->report->getCalculatedBalance());

        $this->report->addAccount((new BankAccount())->setBank('bank1')->setOpeningBalance(1));

        $this->assertEquals(1, $this->report->getCalculatedBalance());

        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('account-interest')->setAmount(20)); // in
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('account-interest')->setAmount(20)); // in
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('rent')->setAmount(15)); // out
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('rent')->setAmount(15)); // out
        $this->report->setGifts([$this->gift1, $this->gift2]);
        $this->report->setExpenses([$this->expense1, $this->expense2]);

        $calculatedBalance = 1 + 20 + 20 - 15 - 15 - 11 - 22;

        $this->assertEquals($calculatedBalance, $this->report->getCalculatedBalance());
    }

    public function testGetCalculatedBalanceProfDeputy()
    {
        $this->validReportCtorArgs = [$this->client, Report::PROF_PFA_HIGH_ASSETS_TYPE, new \DateTime('2017-06-23'), new \DateTime('2018-06-22')];
        $this->report = m::mock(Report::class.'[has106Flag]', $this->validReportCtorArgs);

        $this->report->shouldReceive('has106Flag')->andReturn(false);

        $this->assertEquals(0, $this->report->getCalculatedBalance());

        $this->report->setProfDeputyCostsHowCharged('fixed');
        $this->report->setProfDeputyCostsHasPrevious('yes');
        $this->report->setProfDeputyPreviousCosts(new ArrayCollection([
            new ProfDeputyPreviousCost($this->report, 1),
            new ProfDeputyPreviousCost($this->report, 1),
        ]));
        $this->report->setProfDeputyCostsHasInterim('no');
        $this->report->setProfDeputyFixedCost(3);
        $this->report->setProfDeputyOtherCosts(new ArrayCollection([
            new ProfDeputyOtherCost($this->report, 'id1', false, 10),
            new ProfDeputyOtherCost($this->report, 'id2', false, 10),
        ]));

        $this->assertEquals(-1 - 1 - 3 - 10 - 10, $this->report->getCalculatedBalance());

        // change interim yes->no
        $this->report->setProfDeputyCostsHasInterim('yes');
        $this->report->setProfDeputyInterimCosts(new ArrayCollection([
            new ProfDeputyInterimCost($this->report, new \DateTime('now'), 11),
            new ProfDeputyInterimCost($this->report, new \DateTime('now'), 11),
        ]));
        $this->assertEquals(-1 - 1 - 11 - 11 - 10 - 10, $this->report->getCalculatedBalance());
    }

    /**
     * //TODO consider rewriting, unit testing methods composing the total
     * (see testgetExpensesTotal as an example) and using mocks here.
     */
    public function testGetTotalsOffsetAndMatch()
    {
        $this->report->shouldReceive('has106Flag')->andReturn(false);

        $this->assertEquals(0, $this->report->getTotalsOffset());
        $this->assertEquals(true, $this->report->getTotalsMatch());

        // account opened with 1000, closed with 2000. 1500 money in, 400 out. balance is 100
        $this->report->addAccount((new BankAccount())->setBank('bank1')->setOpeningBalance(1000)->setClosingBalance(2000));
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('account-interest')->setAmount(1500)); // in
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('rent')->setAmount(400)); // out
        $this->report->setGifts([$this->gift1, $this->gift2]);
        $this->report->setExpenses([$this->expense1, $this->expense2]);

        $exepectedTotalOffset = 67; // 1000 - 2000 + 1500 - 400 - 11 - 22
        $this->assertEquals($exepectedTotalOffset, $this->report->getTotalsOffset());
        $this->assertEquals(false, $this->report->getTotalsMatch());

        // add missing transaction that fix the balance
        $this->report->addMoneyTransaction((new MoneyTransaction($this->report))->setCategory('rent')->setAmount(67)); // in

        $this->assertEquals(0, $this->report->getTotalsOffset());
        $this->assertEquals(true, $this->report->getTotalsMatch());
    }

    public function testgetFeesTotal()
    {
        $fee1 = m::mock(Fee::class, ['getAmount' => 2]);
        $reportWith = function ($fees) {
            return m::mock(Report::class.'[getFees]', $this->validReportCtorArgs)
                ->shouldReceive('getFees')->andReturn($fees)
                ->getMock();
        };

        $this->assertEquals(0, $reportWith([])->getFeesTotal());
        $this->assertEquals(2 + 2, $reportWith([$fee1, $fee1])->getFeesTotal());
    }

    public function testgetExpensesTotal()
    {
        $exp1 = m::mock(Expense::class, ['getAmount' => 1]);

        $reportWith = function ($expenses) {
            return m::mock(Report::class.'[getExpenses]', $this->validReportCtorArgs)
                ->shouldReceive('getExpenses')->andReturn($expenses)
                ->getMock();
        };

        $this->assertEquals(0, $reportWith([])->getExpensesTotal());
        $this->assertEquals(1 + 1, $reportWith([$exp1, $exp1])->getExpensesTotal());
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
            [Report::LAY_PFA_HIGH_ASSETS_TYPE, ['bankAccounts', 'moneyIn', 'balance', 'clientBenefitsCheck'], ['moneyInShort', 'lifestyle']],
            [Report::LAY_PFA_LOW_ASSETS_TYPE, ['bankAccounts', 'moneyInShort', 'clientBenefitsCheck'], ['moneyIn', 'lifestyle', 'balance']],
            [Report::LAY_HW_TYPE, ['lifestyle'], ['bankAccounts', 'moneyIn', 'moneyInShort', 'gifts', 'balance', 'clientBenefitsCheck']],
        ];
    }

    /**
     * Some checks that the config array doesn't get messed up.
     *
     * @dataProvider sectionsSettingsProvider
     */
    public function testAvailableSectionsAndHasSection($type, array $expectedSections, array $unExpectedSections)
    {
        $this->report = (new Report($this->client, $type, new \DateTime('2017-06-23'), new \DateTime('2018-06-22')))
            ->setBenefitsSectionReleaseDate(new \DateTime('2016-01-01'));

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
        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('getUnsubmittedReports')->andReturn(new ArrayCollection());
        $mockClient->shouldReceive('getSubmittedReports')->andReturn(new ArrayCollection());

        $reportCurrent = m::mock(Report::class)->makePartial();
        $reportCurrent->shouldReceive('getId')->andReturn(10);
        $reportCurrent->shouldReceive('getClient')->andReturn($mockClient);

        $ndrBankAccount1 = new \App\Entity\Ndr\BankAccount();
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
            $bankAccount1->getClosingBalance() + $bankAccount2->getClosingBalance()
        );
    }

    public function reportTypeTranslationKeyProvider()
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

    /**
     * @dataProvider reportTypeTranslationKeyProvider
     */
    public function testGetReportTitle($reportType, $expected)
    {
        $this->report->setType($reportType);

        $this->assertEquals($expected, $this->report->getReportTitle());
    }

    public function testInvalidAgreedBehalfOption()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->report->setAgreedBehalfDeputy('BAD_VALUE');
    }

    public function testValidAgreedBehalfOptions()
    {
        $values = ['not_deputy', 'only_deputy', 'more_deputies_behalf', 'more_deputies_not_behalf'];
        foreach ($values as $value) {
            $this->report->setAgreedBehalfDeputy($value);

            $this->assertEquals($this->report->getAgreedBehalfDeputy(), $value);
        }
    }

    public function reportTypesWithEndDateProvider()
    {
        return [
            // lay post (21 days)
            ['102', '2019-11-13', '2019-12-04'],
            // non-lay (56 days)
            ['102-5', '2019-11-12', '2020-01-07'],
        ];
    }

    public function layReportTypesProvider()
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

    /**
     * @dataProvider layReportTypesProvider
     */
    public function testIsLayReport($type, $expectedResult)
    {
        $client = new Client();
        $endDate = new \DateTime();
        $startDate = clone $endDate;
        $startDate = $startDate->modify('-1 year');

        $report = new Report($client, $type, $startDate, $endDate);
        $this->assertEquals($expectedResult, $report->isLayReport());
    }

    /**
     * @dataProvider reportTypesWithEndDateProvider
     * */
    public function testUpdateDuetDateBasedOnEndDate($type, $endDate, $expectedDueDate)
    {
        $client = new Client();
        $endDate = new \DateTime($endDate);
        $startDate = clone $endDate;
        $startDate = $startDate->modify('-1 year');

        $report = new Report($client, $type, $startDate, $endDate);

        $report->updateDueDateBasedOnEndDate();

        $this->assertEquals($expectedDueDate, $report->getDueDate()->format('Y-m-d'));
        $this->assertEquals($endDate, $report->getEndDate());
        $this->assertEquals($startDate, $report->getStartDate());
    }

    /**
     * @test
     *
     * @dataProvider benefitsCheckSectionRequiredProvider
     */
    public function requiresBenefitsCheckSection(
        \DateTime $featureFlagDate,
        \DateTime $dueDate,
        ?ClientBenefitsCheck $clientBenefitSection,
        ?\DateTime $unsubmitDate,
        bool $expectedResult
    ) {
        $reportTestHelper = new ReportTestHelper();

        $report = $reportTestHelper->generateReport($this->em)
            ->setDueDate($dueDate)
            ->setClientBenefitsCheck($clientBenefitSection)
            ->setUnSubmitDate($unsubmitDate)
            ->setBenefitsSectionReleaseDate($featureFlagDate);

        self::assertEquals(
            $expectedResult,
            $report->requiresBenefitsCheckSection()
        );
    }

    public function benefitsCheckSectionRequiredProvider(): array
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
