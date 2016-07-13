<?php

namespace AppBundle\Resources\views\Odr;

use AppBundle\Entity\Account;
use AppBundle\Entity\Action;
use AppBundle\Entity\AssetOther;
use AppBundle\Entity\AssetProperty;
use AppBundle\Entity\Client;
use AppBundle\Entity\Debt;
use AppBundle\Entity\Decision;
use AppBundle\Entity\MoneyTransfer;
use AppBundle\Entity\Odr\BankAccount;
use AppBundle\Entity\Odr\Odr;
use AppBundle\Entity\Odr\VisitsCare;
use AppBundle\Entity\Transaction;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DomCrawler\Crawler;
use Mockery as m;

class OdrFormattedTest extends WebTestCase
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

        $this->visitsCare = new VisitsCare();

        $this->account1 = new BankAccount();
        $this->account1->setBank('barclays');
        $this->account2 = new BankAccount();

        $this->odr = new Odr();
        $this->odr
            ->setClient($this->client)
            ->setVisitsCare($this->visitsCare)
            ->setBankAccounts([$this->account1, $this->account2])
        ;

        $this->html = $this->twig->render('AppBundle:Odr/Formatted:formatted.html.twig', [
            'odr' => $this->odr,
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
        $this->assertEquals('Opening Deputyship Report', $this->html($this->crawler, 'h1'));
    }

    public function testOdr()
    {
        $this->assertEquals('1234567t', $this->html($this->crawler, '#caseNumber'));
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


    public function tearDown()
    {
        m::close();
        $this->container->leaveScope('request');
        unset($this->frameworkBundleClient);
    }
}
