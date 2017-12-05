<?php

namespace AppBundle\Resources\views\Report;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Account;
use AppBundle\Entity\Report\Report as Report;
use AppBundle\Entity\Report\Status;
use AppBundle\Entity\User;
use AppBundle\Form\Report\ReasonForBalanceType;
use Mockery as m;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
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
    }

    /**
     * @param array $methods
     * @return Report
     */
    private function getMockedReport(array $methods)
    {
        $client = m::mock(Client::class)
            ->shouldReceive('getFirstname')->andReturn('Peter')
            ->getMock();

        return m::mock(Report::class, $methods + [
            'getId' => 1,
            'getClient' => $client,
            'getTotalsOffset' => null,
            'getMoneyInTotal' => 1,
            'getMoneyOutTotal' => 2,
            'getGiftsTotalValue' => 3,
            'getCalculatedBalance' => 999,
            'getAccountsClosingBalanceTotal' => 100,
//            'hasSection(deputyExpenses)' => true,
//            'hasSection(paDeputyExpenses)' => true,
//            'hasSection(decisions)' => true,
//            'hasSection(contacts)' => true,
//            'hasSection(visitsCare)' => true,
            'hasSection' => true, // tested with all the section, and potentally any report type
            'hasSection' => true,
            'getFeesTotal' => null,
            'getExpensesTotal' => null,
            'getType' => null, // irrelevant
            'getEndDate' => new \DateTime(),
            'getBalanceMismatchExplanation'=>'explanation-content'
        ]);
    }

    /**
     * @param array $methods
     * @return Status
     */
    private function getMockedStatus(array $methods)
    {
        return m::mock(Status::class, $methods + [
            'getBankAccountsState' => ['state'=>'not-started'],
            'getMoneyInState' => ['state'=>'not-started'],
            'getMoneyOutState' => ['state'=>'not-started'],
            'getExpensesState' => ['state'=>'not-started'],
            'getPaFeesExpensesState' => ['state'=>'not-started'],
            'getGiftsState' => ['state'=>'not-started'],

        ]);
    }


    public function testNotReadyNotDue()
    {
        $crawler = $this->renderTemplateAndGetCrawler(
            $this->getMockedReport(['isDue'=>false]),
            $this->getMockedStatus([
                'getBalanceState' => ['state'=>'not-started']
            ])
        );

        $this->assertCount(1, $crawler->filter('#alert-not-started'));
        $this->assertCount(0, $crawler->filter('#alert-balanced'));
        $this->assertCount(0, $crawler->filter('#alert-not-balanced'));
        $this->assertCount(0, $crawler->filter('#calculated-balance-foot'));
        $this->assertCount(0, $crawler->filter('#calculated-balance-table'));
    }

    public function testReadyNotBalanced()
    {
        $crawler = $this->renderTemplateAndGetCrawler(
            $this->getMockedReport([
                'isDue'=>false,
                'isTotalsMatch'=>false
            ]),
            $this->getMockedStatus([
                'getBalanceState' => ['state'=>'not-matching']
            ])
        );

        $html = $crawler->html();

        $this->assertCount(0, $crawler->filter('#alert-not-started'));
        $this->assertCount(0, $crawler->filter('#alert-balanced'));
        $this->assertCount(1, $crawler->filter('#alert-not-balanced'));

        $this->assertCount(1, $crawler->filter('#calculated_balance_foot'));
        $this->assertCount(1, $crawler->filter('#calculated_balance_table'));
    }

    public function testReadyBalanced()
    {
        $crawler = $this->renderTemplateAndGetCrawler(
            $this->getMockedReport([
                'isDue'=>false,
                'isTotalsMatch'=>true
            ]),
            $this->getMockedStatus([
                'getBalanceState' => ['state'=>'not-matching']
            ])
        );

        $html = $crawler->html();

        $this->assertCount(0, $crawler->filter('#alert-not-started'));
        $this->assertCount(1, $crawler->filter('#alert-balanced'));
        $this->assertCount(0, $crawler->filter('#alert-not-balanced'));

        $this->assertCount(1, $crawler->filter('#calculated_balance_foot'));
        $this->assertCount(1, $crawler->filter('#calculated_balance_table'));
    }

    public static function explanationFormProvider()
    {
        return [
            [true, true, false, 1],
            [false, true, false, 0],
            [true, false, false, 0],
            [true, true, true, 0],
        ];
    }

    /**
     * @dataProvider  explanationFormProvider
     */
    public function testExplanationForm($isDue, $readyBalance, $isTotalMatch, $expectedForm)
    {
        $crawler = $this->renderTemplateAndGetCrawler(
            $this->getMockedReport([
                'isDue'=>$isDue,
                'isTotalsMatch'=>$isTotalMatch
            ]),
            $this->getMockedStatus([
                'getBalanceState' => ['state'=>$readyBalance ? 'not-matching' : 'not-started']
            ])
        );
        $this->assertCount($expectedForm, $crawler->filter('#cantFindTheProblem'));
        $this->assertCount($expectedForm, $crawler->filter('#balance_balanceMismatchExplanation'));
    }

    /**
     * @return Crawler
     */
    private function renderTemplateAndGetCrawler(Report $report, Status $status)
    {
        $form = $this->container->get('form.factory')->create(ReasonForBalanceType::class, $report);

        $html = $this->twig->render('AppBundle:Report/Balance:balance.html.twig', [
            'report' => $report,
            'reportStatus' => $status,
            'form' => $form->createView(),
            'backLink' => '[backLinkUrl]',
            'app'=> [
                'session' => m::mock(Session::class)
                    ->shouldReceive('get')->andReturn(false)
                    ->shouldReceive('getFlashBag')->andReturn(
                        m::mock(FlashBagInterface::class)->shouldIgnoreMissing()
                    )
                    ->getMock(),
                'user' => m::mock(User::class)->shouldIgnoreMissing()
            ]
        ]);

        return new Crawler($html);
    }



    public function tearDown()
    {
        m::close();
        $this->container->leaveScope('request');
        unset($this->frameworkBundleClient);
    }

}
