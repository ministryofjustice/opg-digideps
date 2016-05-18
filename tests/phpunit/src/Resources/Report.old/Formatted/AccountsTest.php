<?php

namespace AppBundle\Resources\views\Report\Formatted;

use AppBundle\Resources\views\Report\AbstractReportTest;
use Symfony\Component\DomCrawler\Crawler;
use Mockery as m;

class AccountsTest extends AbstractReportTest
{
    private $templateName = 'AppBundle:Report:Formatted/_accounts.html.twig';

    public function setup()
    {
        $this->markTestSkipped();
        parent::setUp();
        $this->setupReport();
    }

    public function testShowAccountSummary()
    {
        $this->setupAccounts();

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report,
        ]);

        $crawler = new Crawler($html);

        $accountsSection = $crawler->filter('#accounts-section .account-details')->eq(0)->text();

        $this->assertContains('HSBC', $accountsSection);
        $this->assertContains('44-44-44', $accountsSection);
        $this->assertContains('7999', $accountsSection);
        $this->assertContains('HSBC', $accountsSection);
    }

    public function testShowsAllTheMoneyInTransactions()
    {
        $this->markTestIncomplete('reimplement');
        $this->setupAccounts();

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report,
        ]);

        $crawler = new Crawler($html);

        $transactions = $crawler->filter('#accounts-section .money-in .transaction');
        $this->assertEquals(20, $transactions->count());
    }

    public function testShowsAllTheMoneyOutTransactions()
    {
        $this->markTestIncomplete('reimplement');
        $this->setupAccounts();

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report,
        ]);

        $crawler = new Crawler($html);

        $transactions = $crawler->filter('#accounts-section .money-out .transaction');
        $this->assertEquals(21, $transactions->count());
    }

    public function testPlainTransactionDisplaysCorrectly()
    {
        $this->markTestIncomplete('reimplement');
        $this->setupAccounts();

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report,
        ]);

        $crawler = new Crawler($html);

        $transactions = $crawler->filter('#accounts-section .money-in .transaction');
        $firstTransaction = $transactions->eq(0);

        $title = $firstTransaction->filter('.transaction-label')->eq(0)->text();
        $value = $firstTransaction->filter('.transaction-value')->eq(0)->text();
        $more = $firstTransaction->filter('.transaction-detail');

        $this->assertContains('Disability Living Allowance or Personal Independence Payment', $title);
        $this->assertContains('1.00', $value);
        $this->assertContains('£', $value);
        $this->assertEquals(0, $more->count());
    }

    public function testExpandedTransactionsDisplaysCorrectly()
    {
        $this->markTestIncomplete('reimplement');
        $this->setupAccounts();

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report,
        ]);

        $crawler = new Crawler($html);

        $transactions = $crawler->filter('#accounts-section .money-in .transaction');
        $lastTransaction = $transactions->eq(19);

        $title = $lastTransaction->filter('.transaction-label')->eq(0)->text();
        $value = $lastTransaction->filter('.transaction-value')->eq(0)->text();
        $more = $lastTransaction->filter('.transaction-detail');

        $this->assertContains('Any other money paid in and not listed above', $title);
        $this->assertContains('10,000.01', $value);
        $this->assertContains('£', $value);
        $this->assertEquals(1, $more->count());
        $this->assertContains('more 4', $more->eq(0)->text());
    }

    public function testReportListsTotalInTotalOutExpectedDiffAndActualDiff()
    {
        $this->markTestIncomplete('reimplement');
        $this->setupAccounts();

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report,
        ]);

        $crawler = new Crawler($html);

        $accountTotals = $crawler->filter('#accounts-section .balancing')->eq(0);

        $openingBalance = $accountTotals->filter('.balancing-opening-balance')->eq(0)->text();
        $totalIn = $accountTotals->filter('.balancing-total-in')->eq(0)->text();
        $subTotal1 = $accountTotals->filter('.balancing-sub-total')->eq(0)->text();
        $totalOut = $accountTotals->filter('.balancing-total-out')->eq(0)->text();
        $subTotal2 = $accountTotals->filter('.balancing-sub-total-2')->eq(0)->text();
        $closingBalance = $accountTotals->filter('.balancing-closing-balance')->eq(0)->text();

        $this->assertContains('100.00', $openingBalance);
        $this->assertContains('10,000.00', $totalIn);
        $this->assertContains('10,100.00', $subTotal1);
        $this->assertContains('10,000.00', $totalOut);
        $this->assertContains('100.00', $subTotal2);
        $this->assertContains('100.00', $closingBalance);
    }

    public function testExplainWhyClosingBalanceDoesntMatch()
    {
        $this->markTestIncomplete('reimplement');
        $startDate = \DateTime::createFromFormat('j-M-Y', '1-Jan-2014');
        $endDate = \DateTime::createFromFormat('j-M-Y', '1-Jan-2015');

        $moneyIn = $this->getMoneyIn();
        $moneyOut = $this->getMoneyOut();

        $account1 = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getBank')->andReturn('HSBC')
            ->shouldReceive('getSortCode')->andReturn('444444')
            ->shouldReceive('getAccountNumber')->andReturn('7999')
            ->shouldReceive('getOpeningDate')->andReturn($startDate)
            ->shouldReceive('getOpeningBalance')->andReturn(100.00)
            ->shouldReceive('getClosingBalance')->andReturn(100.00)
            ->shouldReceive('getClosingDate')->andReturn($endDate)
            ->shouldReceive('getClosingBalanceExplanation')->andReturn('one two three five')
            ->shouldReceive('getMoneyInTotal')->andReturn(10000.00)
            ->shouldReceive('getMoneyOutTotal')->andReturn(10000.00)
            ->shouldReceive('getMoneyTotal')->andReturn(0.00)
            ->shouldReceive('getMoneyIn')->andReturn($moneyIn)
            ->shouldReceive('getMoneyOut')->andReturn($moneyOut)
            ->getMock();

        $this->report->shouldReceive('getAccounts')->andReturn([$account1]);

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report,
        ]);

        $crawler = new Crawler($html);

        // look for the closing explanation and start and end date
        $accountBalanceExplanation = $crawler->filter('#accounts-section .accountBalance_closingBalanceExplanation')->eq(0)->text();
        $this->assertContains('one two three five', $accountBalanceExplanation);
    }

    public function testExplainWhyOpeningDateDiffers()
    {
        $startDate = \DateTime::createFromFormat('j-M-Y', '1-Jan-2014');
        $endDate = \DateTime::createFromFormat('j-M-Y', '1-Jan-2015');

        $moneyIn = $this->getMoneyIn();
        $moneyOut = $this->getMoneyOut();

        $account1 = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getBank')->andReturn('HSBC')
            ->shouldReceive('getSortCode')->andReturn('444444')
            ->shouldReceive('getAccountNumber')->andReturn('7999')
            ->shouldReceive('getOpeningDate')->andReturn($startDate)
            ->shouldReceive('getOpeningDateMatchesReportDate')->andReturn(true)
            ->shouldReceive('getOpeningBalance')->andReturn(100.00)
            ->shouldReceive('getClosingBalance')->andReturn(100.00)
            ->shouldReceive('getClosingDate')->andReturn($endDate)
            ->shouldReceive('getMoneyInTotal')->andReturn(10000.00)
            ->shouldReceive('getMoneyOutTotal')->andReturn(10000.00)
            ->shouldReceive('getMoneyTotal')->andReturn(0.00)
            ->shouldReceive('getMoneyIn')->andReturn($moneyIn)
            ->shouldReceive('getMoneyOut')->andReturn($moneyOut)
            ->getMock();

        $this->report->shouldReceive('getAccounts')->andReturn([$account1]);

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report,
        ]);

        $crawler = new Crawler($html);

        // look for the closing explanation and start and end date
        $accountDateExplanation = $crawler->filter('#accounts-section .account-date-explanation')->eq(0)->text();

        $this->assertContains('01/01/2014', $accountDateExplanation);
        $this->assertContains('01/01/2015', $accountDateExplanation);
        $this->assertContains('one two three five', $accountDateExplanation);
    }

    public function testExplainWhyClosingDateDiffers()
    {
        $startDate = \DateTime::createFromFormat('j-M-Y', '1-Jan-2014');
        $endDate = \DateTime::createFromFormat('j-M-Y', '1-Jan-2015');

        $moneyIn = $this->getMoneyIn();
        $moneyOut = $this->getMoneyOut();

        $account1 = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getBank')->andReturn('HSBC')
            ->shouldReceive('getSortCode')->andReturn('444444')
            ->shouldReceive('getAccountNumber')->andReturn('7999')
            ->shouldReceive('getOpeningDate')->andReturn($startDate)
            ->shouldReceive('getOpeningBalance')->andReturn(100.00)
            ->shouldReceive('getClosingBalance')->andReturn(100.00)
            ->shouldReceive('getClosingDate')->andReturn($endDate)
            ->shouldReceive('getClosingDateExplanation')->andReturn('one two three five')
            ->shouldReceive('getMoneyInTotal')->andReturn(10000.00)
            ->shouldReceive('getMoneyOutTotal')->andReturn(10000.00)
            ->shouldReceive('getMoneyTotal')->andReturn(0.00)
            ->shouldReceive('getMoneyIn')->andReturn($moneyIn)
            ->shouldReceive('getMoneyOut')->andReturn($moneyOut)
            ->getMock();

        $this->report->shouldReceive('getAccounts')->andReturn([$account1]);

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report,
        ]);

        $crawler = new Crawler($html);

        // look for the closing explanation and start and end date
        $accountDateExplanation = $crawler->filter('#accounts-section .account-date-explanation')->eq(0)->text();

        $this->assertContains('01/01/2014', $accountDateExplanation);
        $this->assertContains('01/01/2015', $accountDateExplanation);
        $this->assertContains('one two three five', $accountDateExplanation);
    }

    public function testDontShowOpeningDateIfNoOpeningOrClosingDateExplanation()
    {
        $this->markTestIncomplete('to update');

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report,
        ]);

        $crawler = new Crawler($html);
        $accountDates = $crawler->filter('#accounts-section .account-date-explanation')->eq(0)->text();

        $this->assertNotContains('01/01/2014', $accountDates);
        $this->assertNotContains('01/01/2015', $accountDates);
    }

    public function testListMultipleAccounts()
    {
        $startDate = \DateTime::createFromFormat('j-M-Y', '1-Jan-2014');
        $endDate = \DateTime::createFromFormat('j-M-Y', '1-Jan-2015');

        $moneyIn = $this->getMoneyIn();
        $moneyOut = $this->getMoneyOut();

        $account1 = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getBank')->andReturn('HSBC')
            ->shouldReceive('getSortCode')->andReturn('444444')
            ->shouldReceive('getAccountNumber')->andReturn('7999')
            ->shouldReceive('getOpeningDate')->andReturn($startDate)
            ->shouldReceive('getOpeningBalance')->andReturn(100.00)
            ->shouldReceive('getClosingBalance')->andReturn(100.00)
            ->shouldReceive('getClosingDate')->andReturn($endDate)
            ->shouldReceive('getMoneyInTotal')->andReturn(10000.00)
            ->shouldReceive('getMoneyOutTotal')->andReturn(10000.00)
            ->shouldReceive('getMoneyTotal')->andReturn(0.00)
            ->shouldReceive('getMoneyIn')->andReturn($moneyIn)
            ->shouldReceive('getMoneyOut')->andReturn($moneyOut)
            ->getMock();

        $account2 = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getBank')->andReturn('Smile')
            ->shouldReceive('getSortCode')->andReturn('111111')
            ->shouldReceive('getAccountNumber')->andReturn('3333')
            ->shouldReceive('getOpeningDate')->andReturn($startDate)
            ->shouldReceive('getOpeningBalance')->andReturn(200.00)
            ->shouldReceive('getClosingBalance')->andReturn(200.00)
            ->shouldReceive('getClosingDate')->andReturn($endDate)
            ->shouldReceive('getMoneyInTotal')->andReturn(20000.00)
            ->shouldReceive('getMoneyOutTotal')->andReturn(20000.00)
            ->shouldReceive('getMoneyTotal')->andReturn(0.00)
            ->shouldReceive('getMoneyIn')->andReturn($moneyIn)
            ->shouldReceive('getMoneyOut')->andReturn($moneyOut)
            ->getMock();

        $this->report->shouldReceive('getAccounts')->andReturn([$account1, $account2]);

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report,
        ]);

        $crawler = new Crawler($html);

        $accountsSections = $crawler->filter('#accounts-section .account-details');

        $this->assertEquals(2, $accountsSections->count());
    }
}
