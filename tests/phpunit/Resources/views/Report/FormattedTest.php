<?php

namespace AppBundle\Resources\views\Report;

use AppBundle\Entity\Account;
use AppBundle\Entity\Action;
use AppBundle\Entity\AssetOther;
use AppBundle\Entity\AssetProperty;
use AppBundle\Entity\Client;
use AppBundle\Entity\Debt;
use AppBundle\Entity\Decision;
use AppBundle\Entity\MoneyTransfer;
use AppBundle\Entity\Transaction;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Report as Report;
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
        $this->frameworkBundleClient = static::createClient(['environment' => 'test', 'debug' => false]);
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

        $this->account1 = new Account();
        $this->account1->setBank('barclays');
        $this->account2 = new Account();

        $this->transactionIn1 = new Transaction();
        $this->transactionOut1 = new Transaction();

        $this->transfer1 = new MoneyTransfer();

        $this->debt1 = new Debt('care-fees', 123, false, '');

        $this->action1 = new Action();

        $this->asset1= new AssetOther();
        $this->asset1->setId(1)->setTitle('Artwork')->setDescription('monna lisa');
        $this->asset2= new AssetOther();
        $this->asset2->setId(2)->setTitle('Antiques')->setDescription('chest of drawers');;
        $this->assetProp= new AssetProperty();
        $this->assetProp->setAddress('plat house');

        $this->decision1 = new Decision();

        $this->report = new Report();
        $this->report
            ->setClient($this->client)
            ->setStartDate(new \Datetime('2015-01-01'))
            ->setEndDate(new \Datetime('2015-12-31'))
            ->setAccounts([$this->account1, $this->account2])
            ->setMoneyTransfers([$this->transfer1])
            ->setTransactionsIn([$this->transactionIn1])
            ->setTransactionsOut([$this->transactionOut1])
            ->setDebts([$this->debt1])
            ->setAction($this->action1)
            ->setAssets([$this->asset1, $this->asset2,$this->assetProp])
            ->setDecisions([[$this->decision1]])
        ;

        $this->html = $this->twig->render('AppBundle:Report:formatted.html.twig', [
            'report' => $this->report,
            'app' => ['user'=>$this->user] //mock twig app.user from the view
        ]);

        $this->crawler = new Crawler($this->html);

//        file_put_contents('/app/tests/out.html', $this->html);
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
    }


    public function testDecisions()
    {
        $this->markTestIncomplete();
    }
    public function testMoneyTransfers()
    {
        $this->markTestIncomplete();
    }

    public function testTransactions()
    {
        $this->markTestIncomplete();
    }

    public function testDebts()
    {
        $this->markTestIncomplete();
    }

    public function testAction()
    {
        $this->markTestIncomplete();
    }


    public function tearDown()
    {
        m::close();
        $this->container->leaveScope('request');
        unset($this->frameworkBundleClient);
    }
}
