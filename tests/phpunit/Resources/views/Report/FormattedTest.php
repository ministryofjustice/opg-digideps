<?php

namespace AppBundle\Resources\views\Report;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Report as Report;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DomCrawler\Crawler;
use Mockery as m;

class FormattedTest extends WebTestCase
{
    /**
     * @var Symfony\Bundle\FrameworkBundle\Client
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
        $this->report = new Report();
    }

    private function html($crawler, $expr)
    {
        return $crawler->filter($expr)->eq(0)->html();
    }

    public function tearDown()
    {
        m::close();
        $this->container->leaveScope('request');
    }

    public function testActionNo()
    {
        $this->markTestIncomplete();
        $action = new \AppBundle\Entity\Action($this->report);
        $action->setDoYouExpectFinancialDecisions('no');
        $action->setDoYouExpectFinancialDecisionsDetails('user-financial-details');
        $action->setDoYouHaveConcerns('no');
        $action->setDoYouHaveConcernsDetails('user-actions-details');

        $html = $this->twig->render('AppBundle:Report:formatted.html.twig', [
            'report' => $this->report,
        ]);
        $crawler = new Crawler($html);

        // decisions
        $this->assertEquals('X', $this->html($crawler, '#action-section-decisions [data-checkbox="do-you-live-with-the-client--no"]'));
        $this->assertCount(0, $crawler->filter('#action-section-decisions [class="value textarea"]'));

        // actions
        $this->assertEquals('X', $this->html($crawler, '#action-section-concerns [data-checkbox="do-you-live-with-the-client--no"]'));
        $this->assertCount(0, $crawler->filter('#action-section-concerns [class="value textarea"]'));
    }

    public function testActionYes()
    {
        $this->markTestIncomplete();
        $action = new \AppBundle\Entity\Action($this->report);
        $action->setDoYouExpectFinancialDecisions('yes');
        $action->setDoYouExpectFinancialDecisionsDetails('user-financial-details');
        $action->setDoYouHaveConcerns('yes');
        $action->setDoYouHaveConcernsDetails('user-actions-details');

        $html = $this->twig->render('AppBundle:Report:formatted.html.twig', [
            'report' => $this->report,
        ]);
        $crawler = new Crawler($html);

        // decisions
        $this->assertEquals('X', $this->html($crawler, '#action-section-decisions [data-checkbox="do-you-live-with-the-client--yes"]'));
        $this->assertContains('user-financial-details', $this->html($crawler, '#action-section-decisions [class="value textarea"]'));

        // actions
        $this->assertEquals('X', $this->html($crawler, '#action-section-concerns [data-checkbox="do-you-live-with-the-client--yes"]'));
        $this->assertContains('user-actions-details', $this->html($crawler, '#action-section-concerns [class="value textarea"]'));
    }
}
