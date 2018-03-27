<?php

namespace AppBundle\Resources\views\Report;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Account;
use AppBundle\Entity\Report\Action;
use AppBundle\Entity\Report\AssetOther;
use AppBundle\Entity\Report\AssetProperty;
use AppBundle\Entity\Report\BankAccount;
use AppBundle\Entity\Report\Debt;
use AppBundle\Entity\Report\Decision;
use AppBundle\Entity\Report\MoneyTransaction;
use AppBundle\Entity\Report\MoneyTransfer;
use AppBundle\Entity\Report\Report as Report;
use AppBundle\Entity\Report\Status;
use AppBundle\Entity\User;
use Mockery as m;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;

class FormattedTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;

    /**
     * @var Report
     */
    protected $report;
    protected $reportClient;
    protected $deputy;
    protected $decisions;
    protected $contacts;
    protected $twig;

    public function setUp()
    {
        $this->frameworkBundleClient = static::createClient(['environment' => 'test', 'debug' => true]);
        $this->frameworkBundleClient->getContainer()->enterScope('request');
        $request = new Request();
        $request->create('/');
        $this->container = $this->frameworkBundleClient->getContainer();
        $this->container->set('request', $request, 'request');
        $this->twig = $this->frameworkBundleClient->getContainer()->get('templating');
        $this->container->get('request_stack')->push(Request::createFromGlobals());

        $this->user = new User();
        $this->user
            ->setFirstname('John')
            ->setLastname('White');

        $this->client = new Client();
        $this->client
            ->setFirstname('Peter')
            ->setLastname('Jones')
            ->setCaseNumber('1234567t');
        $this->client->addUser($this->user);

        $this->account1 = (new BankAccount())
            ->setBank('barclays')
            ->setOpeningBalance(89);
        $this->account2 = (new BankAccount())
            ->setBank('HSBC')
            ->setOpeningBalance(43);

        $this->transactionIn1 = (new MoneyTransaction())
            ->setCategory('council-tax')
            ->setAmount(1234)
            ->setId('gas');
        $this->transactionIn2 = (new MoneyTransaction())
            ->setCategory('council-tax')
            ->setAmount(45)
            ->setId('electricity');
        $this->transactionOut1 = (new MoneyTransaction())
            ->setCategory('anything-else-paid-out') //or accommodation
            ->setAmount(1233)
            ->setId('anything-else-paid-out');

        $this->transfer1 = (new MoneyTransfer())
            ->setAccountFrom($this->account1)
            ->setAccountTo($this->account2)
            ->setAmount(10500.60);
        ;
        $this->transfer2 = (new MoneyTransfer())
            ->setAccountFrom($this->account2)
            ->setAccountTo($this->account1)
            ->setAmount(45123.00)
        ;


        $this->debt1 = new Debt('care-fees', 123, false, '');

        $this->action1 = (new Action())
            ->setDoYouExpectFinancialDecisions('yes')
            ->setDoYouExpectFinancialDecisionsDetails('sell both flats')
            ->setDoYouHaveConcerns('yes')
            ->setDoYouHaveConcernsDetails('not able next year');

        $this->asset1 = new AssetOther();
        $this->asset1->setId(1)->setTitle('Artwork')->setDescription('monna lisa');
        $this->asset2 = new AssetOther();
        $this->asset2->setId(2)->setTitle('Antiques')->setDescription('chest of drawers');
        $this->assetProp = new AssetProperty();
        $this->assetProp->setAddress('plat house')->setPostcode('ha1')->setId(3)->setOwned(AssetProperty::OWNED_FULLY)->setValue(500000);
        $this->assetProp2 = new AssetProperty();
        $this->assetProp2->setAddress('victoria rd')->setPostcode('sw1')->setid(4)->setValue(100000)->setOwned(AssetProperty::OWNED_PARTLY)->setOwnedPercentage(60);

        $this->decision1 = (new Decision())
            ->setDescription('sold the flat in SW2')
            ->setClientInvolvedBoolean(true)
            ->setClientInvolvedDetails('he wanted to leave this area');
        $this->decision2 = (new Decision())
            ->setDescription('bought flat in E1')
            ->setClientInvolvedBoolean(true)
            ->setClientInvolvedDetails('he wanted to live here');

        $this->reportStatus = m::mock(Status::class);

        $this->report = new Report();
        $this->report
            ->setType(Report::TYPE_102)
            ->setAvailableSections([//102
            'decisions', 'contacts','visitsCare',
            'lifestyle','balance','bankAccounts',
            'moneyTransfers',
            'moneyIn', 'moneyOut',
            'moneyInShort', 'moneyOutShort',
            'assets', 'debts', 'gifts',
            'actions', 'otherInfo', 'deputyExpenses',
            'paDeputyExpenses', 'documents'
        ])
            ->setClient($this->client)
            ->setStartDate(new \Datetime('2015-01-01'))
            ->setEndDate(new \Datetime('2015-12-31'))
            ->setStatus($this->reportStatus)
        ;
    }

    /**
     * @param  array   $additionalVars
     * @return Crawler
     */
    private function renderTemplateAndGetCrawler($additionalVars = [])
    {
        $html = $this->twig->render('AppBundle:Report/Formatted:formatted.html.twig', $additionalVars + [
            'report' => $this->report,
            'app' => ['user' => $this->user], //mock twig app.user from the view
        ]);

        return new Crawler($html);
    }

    public function testReport()
    {
        $crawler = $this->renderTemplateAndGetCrawler();

        $this->assertEquals('1234567t', $this->html($crawler, '#caseNumber'));
        $this->assertContains('01 / 01 / 2015', $this->html($crawler, '#report-start-date'));
        $this->assertContains('31 / 12 / 2015', $this->html($crawler, '#report-end-date'));
    }

    public function testDeputy()
    {
        $crawler = $this->renderTemplateAndGetCrawler();

        $this->assertContains('John', $this->html($crawler, '#deputy-details-subsection'));
    }

    public function testClient()
    {
        $crawler = $this->renderTemplateAndGetCrawler();

        $this->assertContains('Jones', $this->html($crawler, '#client-details-subsection'));
    }

    public function testAssets()
    {
        $this->report->setAssets([]);
        $crawler = $this->renderTemplateAndGetCrawler();
        $this->assertCount(0, $crawler->filter('#assets-section'));


        $this->report->setAssets([$this->asset1, $this->asset2, $this->assetProp, $this->assetProp2]);
        $crawler = $this->renderTemplateAndGetCrawler();
        $this->assertContains('monna lisa', $this->html($crawler, '#assets-section'));
        $this->assertContains('chest of drawers', $this->html($crawler, '#assets-section'));
        $this->assertContains('plat house', $this->html($crawler, '#assets-section'));
        $this->assertContains('sw1', $this->html($crawler, '#assets-section'));
        //$this->assertContains('£560,000.00', $this->html($crawler, '#assetsTotal', 'asset total must be 500k + 60% of 100k'));
    }

    public function testDecisions()
    {
        $this->report->setDecisions([]);
        $crawler = $this->renderTemplateAndGetCrawler();
        $this->assertCount(0, $crawler->filter('#assets-section'));

        $this->report->setDecisions([$this->decision1, $this->decision2]);
        $crawler = $this->renderTemplateAndGetCrawler();
        $this->assertContains('sold the flat in SW2', $this->html($crawler, '#decisions-section'));
        $this->assertContains('he wanted to leave this area', $this->html($crawler, '#decisions-section'));
        $this->assertContains('bought flat in E1', $this->html($crawler, '#decisions-section'));
        $this->assertContains('he wanted to live here', $this->html($crawler, '#decisions-section'));
    }

    public function testMoneyTransfers()
    {
        // no accounts -> section not displaying
        $this->report->setBankAccounts([]);
        $this->assertCount(0, $this->renderTemplateAndGetCrawler()->filter('#money-transfers'));

        // 1 account => don't show the section (DDPB-1525)
        $this->report
            ->setBankAccounts([$this->account1])
            ->setNoTransfersToAdd(false)
            ->setMoneyTransfers([$this->transfer1, $this->transfer2]); //should not happen but enforce assertion
        $this->assertCount(0, $this->renderTemplateAndGetCrawler()->filter('#money-transfers'));

        // 2 accounts but no transfer -> still show the section
        $this->report
            ->setBankAccounts([$this->account1, $this->account2])
            ->setNoTransfersToAdd(null)
            ->setMoneyTransfers([]); //should not happen but enforce assertion
        $crawler = $this->renderTemplateAndGetCrawler();
        $this->assertCount(1, $crawler->filter('#money-transfers'));
        $this->assertNotContains('X', $this->html($crawler, '#money-transfers-no-transfers-add'));

        // no transfers
        $this->report
            ->setBankAccounts([$this->account1, $this->account2])
            ->setNoTransfersToAdd(true)
            ->setMoneyTransfers([]); //should not happen but enforce assertion
        $crawler = $this->renderTemplateAndGetCrawler();
        $this->assertContains('X', $this->html($crawler, '#money-transfers-no-transfers-add'));

        // 2 transfers should be rendered properly, and "no transfers hidden"
        $this->report
            ->setBankAccounts([$this->account1, $this->account2])
            ->setNoTransfersToAdd(false)
            ->setMoneyTransfers([$this->transfer1, $this->transfer2]);
        $crawler = $this->renderTemplateAndGetCrawler();
        $this->assertCount(0, $crawler->filter('#money-transfers-no-transfers-add'));
        $html = $this->html($crawler, '#money-transfers');
        $this->assertContains('10,500.60', $html);
        $this->assertContains('45,123.00', $html);
    }

    public function testAction()
    {
        $this->report->setAction($this->action1);
        $crawler = $this->renderTemplateAndGetCrawler();

        $this->assertContains('sell both flats', $this->html($crawler, '#action-section'));
        $this->assertContains('not able next year', $this->html($crawler, '#action-section'));
    }

    public function testDebts()
    {
        $this->markTestIncomplete('To implement using fixture below, incuding empty case');
        $this->report->setHasDebts(true);
        $this->report->setDebts([$this->debt1]);
    }

    public function testBankAccounts()
    {
        $this->markTestIncomplete('To implement using fixture below, incuding empty case');
        $this->report->setBankAccounts([$this->account1, $this->account2]);
    }

    public function testMoneyTransactions()
    {
        $this->markTestIncomplete('To implement using fixture below, incuding empty case');
        $this->report
            ->setMoneyTransactionsIn([$this->transactionIn1, $this->transactionIn2])
            ->setMoneyTransactionsOut([$this->transactionOut1])
            ->setMoneyInTotal(1234 + 45)
            ->setMoneyOutTotal(1233);
    }

    public function testGifts()
    {
        $this->markTestIncomplete('To implement using fixture below, incuding empty case');
        $this->report->setGifts([$this->debt1]);
    }

    public function testBalance()
    {
        $this->markTestIncomplete('To implement using fixture below, incuding empty case');
        $this->report
            ->setAccountsClosingBalanceTotal(
                $this->account1->getOpeningBalance()
                + $this->account2->getOpeningBalance()
            )->setCalculatedBalance(
                $this->account1->getOpeningBalance()
                + $this->account2->getOpeningBalance()
                + 1234 + 45 // money in
                - 1233 // money out
            )->setTotalsOffset(
                $this->account1->getOpeningBalance()
                + $this->account2->getOpeningBalance()
                - (
                    $this->account1->getOpeningBalance()
                    + $this->account2->getOpeningBalance()
                    + 1234 + 45 // money in
                    - 1233
                )
            )
            ->setBalanceMismatchExplanation('money lost');
    }

    public function testSummaryinfo()
    {
        $this->reportStatus->shouldReceive('getBalanceState')->andReturn(['state'=>'done']);

        // assert not displaying without "showSummary"
        $crawler = $this->renderTemplateAndGetCrawler();
        $this->assertCount(0, $crawler->filter('#report-summary'));

        // assert displaying for a 102
        $crawler = $this->renderTemplateAndGetCrawler(['showSummary'=>true]);
        $this->assertCount(1, $crawler->filter('#report-summary'));

        // assert NOT displaying when balacne section is not added
        $as = $this->report->getAvailableSections();
        unset($as[array_search('balance', $as)]);
        $this->report->setAvailableSections($as);
        $crawler = $this->renderTemplateAndGetCrawler(['showSummary'=>true]);
        $this->assertCount(0, $crawler->filter('#report-summary'));
    }

    public function tearDown()
    {
        m::close();
        $this->container->leaveScope('request');
        unset($this->frameworkBundleClient);
    }

    private function html(Crawler $crawler, $expr)
    {
        return $crawler->filter($expr)->eq(0)->html();
    }
}
