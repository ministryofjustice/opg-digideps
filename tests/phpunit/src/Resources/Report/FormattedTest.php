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
        $this->frameworkBundleClient = static::createClient([ 'environment' => 'test', 'debug' => false]);
        $this->frameworkBundleClient->getContainer()->enterScope('request');
        $request = new Request();
        $request->create('/');
        $this->frameworkBundleClient->getContainer()->set('request', $request, 'request');
        $this->twig = $this->frameworkBundleClient->getContainer()->get('templating');

        $this->report = new Report;
    }

    private function html($crawler, $expr)
    {
        return $crawler->filter($expr)->eq(0)->html();
    }

    public function tearDown()
    {
        m::close();
    }

    public function testConcernNo()
    {
        $concern = new \AppBundle\Entity\Concern($this->report);
        $concern->setDoYouExpectFinancialDecisions('no');
        $concern->setDoYouExpectFinancialDecisionsDetails('user-financial-details');
        $concern->setDoYouHaveConcerns('no');
        $concern->setDoYouHaveConcernsDetails('user-concerns-details');

        $html = $this->twig->render('AppBundle:Report:formatted.html.twig', [
            'report' => $this->report
        ]);
        $crawler = new Crawler($html);

        // decisions
        $this->assertEquals('X', $this->html($crawler, '#concern-section-decisions [data-checkbox="do-you-live-with-the-client--no"]'));
        $this->assertCount(0, $crawler->filter('#concern-section-decisions [class="value textarea"]'));

        // concerns
        $this->assertEquals('X', $this->html($crawler, '#concern-section-concerns [data-checkbox="do-you-live-with-the-client--no"]'));
        $this->assertCount(0, $crawler->filter('#concern-section-concerns [class="value textarea"]'));
    }

    public function testConcernYes()
    {
        $concern = new \AppBundle\Entity\Concern($this->report);
        $concern->setDoYouExpectFinancialDecisions('yes');
        $concern->setDoYouExpectFinancialDecisionsDetails('user-financial-details');
        $concern->setDoYouHaveConcerns('yes');
        $concern->setDoYouHaveConcernsDetails('user-concerns-details');

        $html = $this->twig->render('AppBundle:Report:formatted.html.twig', [
            'report' => $this->report
        ]);
        $crawler = new Crawler($html);

        // decisions
        $this->assertEquals('X', $this->html($crawler, '#concern-section-decisions [data-checkbox="do-you-live-with-the-client--yes"]'));
        $this->assertContains('user-financial-details', $this->html($crawler, '#concern-section-decisions [class="value textarea"]'));

        // concerns
        $this->assertEquals('X', $this->html($crawler, '#concern-section-concerns [data-checkbox="do-you-live-with-the-client--yes"]'));
        $this->assertContains('user-concerns-details', $this->html($crawler, '#concern-section-concerns [class="value textarea"]'));
    }

}
