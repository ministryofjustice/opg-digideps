<?php

namespace AppBundle\Resources\views\Report;

use AppBundle\Entity\Report\Account;
use AppBundle\Entity\Report\Action;
use AppBundle\Entity\Report\AssetOther;
use AppBundle\Entity\Report\AssetProperty;
use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Debt;
use AppBundle\Entity\Report\Decision;
use AppBundle\Entity\Report\MoneyTransfer;
use AppBundle\Entity\Report\MoneyTransaction;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Report\Report as Report;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DomCrawler\Crawler;
use Mockery as m;

class FormattedTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;
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

        $this->account1 = (new Account())
            ->setBank('barclays')
            ->setOpeningBalance(89);
        $this->account2 = (new Account())
            ->setBank('HSBC')
            ->setOpeningBalance(43);

        $this->transactionIn1 = (new Transaction())
            ->setCategory('household-bills')
            ->setAmountsTotal(1234)
            ->setId('gas');
        $this->transactionIn2 = (new Transaction())
            ->setCategory('household-bills')
            ->setAmountsTotal(45)
            ->setId('electricity');
        $this->transactionOut1 = (new Transaction())
            ->setCategory('moneyout-other') //or accommodation
            ->setAmountsTotal(1233)
            ->setId('anything-else-paid-out');

        $this->transfer1 = (new MoneyTransfer())
            ->setAccountFrom($this->account1)
            ->setAccountTo($this->account2)
            ->setAmount(12345)
        ;
        $this->transfer2 = (new MoneyTransfer())
            ->setAccountFrom($this->account2)
            ->setAccountTo($this->account1)
            ->setAmount(98765)
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

        $this->report = new Report();
        $this->report
            ->setClient($this->client)
            ->setStartDate(new \Datetime('2015-01-01'))
            ->setEndDate(new \Datetime('2015-12-31'))
            ->setAccounts([$this->account1, $this->account2])
            ->setMoneyTransfers([$this->transfer1, $this->transfer2])
            ->setMoneyTransactionsIn([$this->transactionIn1, $this->transactionIn2])
            ->setMoneyTransactionsOut([$this->transactionOut1])
            ->setMoneyInTotal(1234 + 45)
            ->setMoneyOutTotal(1233)
            ->setAction($this->action1)
            ->setAssets([$this->asset1, $this->asset2, $this->assetProp, $this->assetProp2])
            ->setDecisions([$this->decision1, $this->decision2])
            ->setHasDebts(true)
            ->setDebts([$this->debt1])
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
            ->setBalanceMismatchExplanation('money lost')
        ;

        $this->html = $this->twig->render('AppBundle:Report/Formatted:formatted.html.twig', [
            'report' => $this->report,
            'app' => ['user' => $this->user], //mock twig app.user from the view
        ]);

        $this->crawler = new Crawler($this->html);

        file_put_contents('/app/tests/report.html', $this->html);
    }

    private function html($crawler, $expr)
    {
        return $crawler->filter($expr)->eq(0)->html();
    }

    public function testLayout()
    {
        $this->assertEquals('Deputy report for property and financial decisions', $this->html($this->crawler, 'h1'));
    }

    public function testReport()
    {
        $this->assertEquals('1234567t', $this->html($this->crawler, '#caseNumber'));
        $this->assertContains('01 / 01 / 2015', $this->html($this->crawler, '#report-start-date'));
        $this->assertContains('31 / 12 / 2015', $this->html($this->crawler, '#report-end-date'));
    }

    public function testDeputy()
    {
        $this->assertContains('John', $this->html($this->crawler, '#deputy-details-subsection'));
    }

    public function testClient()
    {
        $this->assertContains('Jones', $this->html($this->crawler, '#client-details-subsection'));
    }

    public function testAccount()
    {
        $this->assertContains('barclays', $this->html($this->crawler, '#account-summary'));
    }

    public function testAssets()
    {
        $this->assertContains('monna lisa', $this->html($this->crawler, '#assets-section'));
        $this->assertContains('chest of drawers', $this->html($this->crawler, '#assets-section'));
        $this->assertContains('plat house', $this->html($this->crawler, '#assets-section'));
        $this->assertContains('sw1', $this->html($this->crawler, '#assets-section'));
        $this->assertContains('£560,000.00', $this->html($this->crawler, '#assetsTotal', 'asset total must be 500k + 60% of 100k'));
    }

    public function testDecisions()
    {
        $this->assertContains('sold the flat in SW2', $this->html($this->crawler, '#decisions-list'));
        $this->assertContains('he wanted to leave this area', $this->html($this->crawler, '#decisions-list'));
        $this->assertContains('bought flat in E1', $this->html($this->crawler, '#decisions-list'));
        $this->assertContains('he wanted to live here', $this->html($this->crawler, '#decisions-list'));
    }
    public function testMoneyTransfers()
    {
        $this->assertContains('12,345.00', $this->html($this->crawler, '#money-transfers-table'));
        $this->assertContains('98,765.00', $this->html($this->crawler, '#money-transfers-table'));
    }

    public function testTransactions()
    {
        $this->assertContains('Gas', $this->html($this->crawler, '#moneyIn-transactions'));
        $this->assertContains('1,234.00', $this->html($this->crawler, '#moneyIn-transactions'));
        $this->assertContains('Electricity', $this->html($this->crawler, '#moneyIn-transactions'));
        $this->assertContains('45.00', $this->html($this->crawler, '#moneyIn-transactions'));
        $this->assertContains('1,279.00', $this->html($this->crawler, '#moneyIn-transactions'));

        $this->assertContains('Anything else paid out', $this->html($this->crawler, '#moneyOut-transactions'));
        $this->assertContains('1,233.00', $this->html($this->crawler, '#moneyOut-transactions'));
    }

    public function testDebts()
    {
        $this->assertContains('Care fees', $this->html($this->crawler, '#debts-section'));
        $this->assertContains('123.00', $this->html($this->crawler, '#debts-section'));
    }

    public function testAction()
    {
        $this->assertContains('sell both flats', $this->html($this->crawler, '#action-section'));
        $this->assertContains('not able next year', $this->html($this->crawler, '#action-section'));
    }

    public function testBalance()
    {
        $this->assertContains('Accounts not balanced', $this->html($this->crawler, '#accounts-section'));
        $this->assertContains('46.00', $this->html($this->crawler, '#accounts-section'));
        $this->assertContains('money lost', $this->html($this->crawler, '#accounts-section'));
    }

    public function tearDown()
    {
        m::close();
        $this->container->leaveScope('request');
        unset($this->frameworkBundleClient);
    }
}
