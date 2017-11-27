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
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class BalanceTest extends WebTestCase
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


        $this->client = m::mock(Client::class)
            ->shouldReceive('getFirstname')->andReturn('Peter')
            ->getMock();

        $this->report = m::mock(Report::class)
            ->shouldReceive('hasSection')->andReturn(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('getType')->andReturn(Report::TYPE_102)
            ->shouldReceive('getAvailableSections')->andReturn([//102
                'decisions', 'contacts','visitsCare',
                'lifestyle','balance','bankAccounts',
                'moneyTransfers',
                'moneyIn', 'moneyOut',
                'moneyInShort', 'moneyOutShort',
                'assets', 'debts', 'gifts',
                'actions', 'otherInfo', 'deputyExpenses',
                'paDeputyExpenses', 'documents'
            ])
            ->shouldReceive('totalsOffset')->andReturn(0)
            ->shouldReceive('getClient')->andReturn($this->client)
            ->getMock()
        ;

        $this->reportStatus = m::mock(Status::class);

    }

    public function testNotReadyToBalance()
    {
        $this->report->shouldReceive('getTotalsOffset')->andReturn(0);
        $this->report->shouldReceive('isDue')->andReturn(false);

        $this->reportStatus->shouldReceive(
            'getGiftsState',
            'getExpensesState',
            'getBankAccountsState',
            'getMoneyInState',
            'getMoneyOutState'
        )->andReturn(['state'=>'not-started']);

        $crawler = $this->renderTemplateAndGetCrawler();

        $this->assertCount(1, $crawler->filter('#alert-not-started'));
        $this->assertCount(0, $crawler->filter('#alert-balanced'));
        $this->assertCount(0, $crawler->filter('#alert-not-balanced'));
    }

    public function testReadyToBalanceTotalMatch()
    {
        $this->report
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getTotalsOffset')->andReturn(0)
            ->shouldReceive('getStartDate')->andReturn(new \DateTime('2017-12-31'))
            ->shouldReceive('getEndDate')->andReturn(new \DateTime('2018-12-31'))
            ->shouldReceive('getMoneyInTotal')->andReturn(100)
            ->shouldReceive('getMoneyOutTotal')->andReturn(100)
            ->shouldReceive('getExpensesTotal')->andReturn(100)
            ->shouldReceive('getGiftsTotalValue')->andReturn(100)
            ->shouldReceive('getAccountsClosingBalanceTotal')->andReturn(100)
            ->shouldReceive('getAccountsOpeningBalanceTotal')->andReturn(100)
            ->shouldReceive('isTotalsMatch')->andReturn(true);

        $this->reportStatus->shouldReceive(
            'getGiftsState',
            'getExpensesState',
            'getBankAccountsState',
            'getMoneyInState',
            'getMoneyOutState'
        )->andReturn(['state'=>'done']);

        $crawler = $this->renderTemplateAndGetCrawler();

        $this->assertCount(0, $crawler->filter('#alert-not-started'));
        $this->assertCount(1, $crawler->filter('#alert-balanced'));
        $this->assertCount(0, $crawler->filter('#alert-not-balanced'));

        //TODO assert more here
    }

    public function testReadyToBalanceTotalNotMatch()
    {
        $this->report
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getTotalsOffset')->andReturn(0)
            ->shouldReceive('getStartDate')->andReturn(new \DateTime('2017-12-31'))
            ->shouldReceive('getEndDate')->andReturn(new \DateTime('2018-12-31'))
            ->shouldReceive('getMoneyInTotal')->andReturn(100)
            ->shouldReceive('getMoneyOutTotal')->andReturn(100)
            ->shouldReceive('getExpensesTotal')->andReturn(100)
            ->shouldReceive('getGiftsTotalValue')->andReturn(100)
            ->shouldReceive('getAccountsClosingBalanceTotal')->andReturn(100)
            ->shouldReceive('getAccountsOpeningBalanceTotal')->andReturn(100)
            ->shouldReceive('isTotalsMatch')->andReturn(false);

        $this->reportStatus->shouldReceive(
            'getGiftsState',
            'getExpensesState',
            'getBankAccountsState',
            'getMoneyInState',
            'getMoneyOutState'
        )->andReturn(['state'=>'done']);

        $crawler = $this->renderTemplateAndGetCrawler();

        $this->assertCount(0, $crawler->filter('#alert-not-started'));
        $this->assertCount(0, $crawler->filter('#alert-balanced'));
        $this->assertCount(1, $crawler->filter('#alert-not-balanced'));


        //TODO assert more here
    }


    /**
     * Get 'app' var for template rendering
     * //TODO move to outer class if needed elsewhere
     */
    private static function getTemplateAppMocked()
    {
        $emptyFlashBag = m::mock(FlashBagInterface::class)->shouldIgnoreMissing();
        $emptySession = m::mock(Session::class)
            ->shouldReceive('get')->andReturn(false)
            ->shouldReceive('getFlashBag')->andReturn($emptyFlashBag)
            ->getMock();

        return [
            'session' => $emptySession,
            'user' => m::mock(User::class)->shouldIgnoreMissing()
        ];
    }

    /**
     * @return Crawler
     */
    private function renderTemplateAndGetCrawler()
    {
        $html = $this->twig->render('AppBundle:Report/Balance:balance.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
            'backLink' => '[backLinkUrl]',
            'app'=> self::getTemplateAppMocked()
        ]);

        return new Crawler($html);
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
