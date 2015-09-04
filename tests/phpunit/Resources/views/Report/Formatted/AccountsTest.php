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
        parent::setUp();
        $this->setupReport();
    }

    public function testShowAccountSummary()
    {
        $this->setupAccounts();

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report
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
        $this->setupAccounts();

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report
        ]);

        $crawler = new Crawler($html);

        $transactions = $crawler->filter('#accounts-section .money-in .transaction');
        $this->assertEquals(20, $transactions->count());

    }

    public function testShowsAllTheMoneyOutTransactions()
    {
        $this->setupAccounts();

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report
        ]);

        $crawler = new Crawler($html);

        $transactions = $crawler->filter('#accounts-section .money-out .transaction');
        $this->assertEquals(21, $transactions->count());
    }

    public function testPlainTransactionDisplaysCorrectly()
    {
        $this->setupAccounts();

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report
        ]);

        $crawler = new Crawler($html);

        $transactions = $crawler->filter('#accounts-section .money-in .transaction');
        $firstTransaction = $transactions->eq(0);

        $title = $firstTransaction->filter('.transaction-label')->eq(0)->text();
        $value = $firstTransaction->filter('.transaction-value')->eq(0)->text();
        $more = $firstTransaction->filter('.transaction-detail');


        $this->assertContains("Disability Living Allowance or Personal Independence Payment", $title);
        $this->assertContains("1.00", $value);
        $this->assertContains("£", $value);
        $this->assertEquals(0, $more->count());

    }

    public function testExpandedTransactionsDisplaysCorrectly()
    {
        $this->setupAccounts();

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report
        ]);

        $crawler = new Crawler($html);

        $transactions = $crawler->filter('#accounts-section .money-in .transaction');
        $lastTransaction = $transactions->eq(19);

        $title = $lastTransaction->filter('.transaction-label')->eq(0)->text();
        $value = $lastTransaction->filter('.transaction-value')->eq(0)->text();
        $more = $lastTransaction->filter('.transaction-detail');


        $this->assertContains("Any other money paid in and not listed above", $title);
        $this->assertContains("10,000.01", $value);
        $this->assertContains("£", $value);
        $this->assertEquals(1, $more->count());
        $this->assertContains("more 4", $more->eq(0)->text());
    }

    public function testReportListsTotalInTotalOutExpectedDiffAndActualDiff()
    {
        $this->setupAccounts();

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report
        ]);

        $crawler = new Crawler($html);
    }

    public function testExplainWhyClosingBalanceDoesntMatch()
    {
        $account = $this->getAccountMock();
        $account->shouldReceive('getClosingBalanceExplanation')->andReturn("I lost some of the money");

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report
        ]);

        $crawler = new Crawler($html);

        // look for the closing explanation

    }

    public function testExplainWhyOpeningDateDiffers()
    {
        $account = $this->getAccountMock();
        $account
            ->shouldReceive('getOpeningDateMatchesReportDate')->andReturn(false)
            ->shouldReceive('getOpeningDateExplanation')->andReturn("ipsum lorem");

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report
        ]);

        $crawler = new Crawler($html);

        // look for the closing explanation
    }

    public function testExplainWhyClosingDateDiffers()
    {
        $account = $this->getAccountMock();
        $account->shouldReceive('getClosingDateExplanation')->andReturn("one two three five");

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report
        ]);

        $crawler = new Crawler($html);

        // look for the closing explanation
    }


}
