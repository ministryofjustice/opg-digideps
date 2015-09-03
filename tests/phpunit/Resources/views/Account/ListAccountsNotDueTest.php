<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity as EntityDir;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\EntityFactory;

class ListAccountsNotDueTest extends WebTestCase
{
    /**
     * @var Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function setUp()
    {
        $this->client = static::createClient([ 'environment' => 'test',
                                               'debug' => false ]);
        
        //$this->client->getContainer()->enterScope('request');
        //$this->client->getContainer()->set('request', new Request(), 'request');
        
        $this->twig = $this->client->getContainer()->get('templating');
        
        $report = EntityFactory::createReport(['id'=>1, 'due'=>false]);
        
        $html = $this->twig->render('AppBundle:Account:_listAccounts.html.twig', [
            'report' =>  $report,
            'accounts' => [
                EntityFactory::createAccount(['id'=>1, 'bank'=>'hsbc bank']), 
                EntityFactory::createAccount(['id'=>2, 'bank'=>'halifax bank'])
            ]
        ]);
        
        $this->crawler = new Crawler($html);
        
        $this->hsbcNode = $this->crawler->filter('ul.report-list li.report-list__item')->eq(0);
        $this->halifaxNode = $this->crawler->filter('ul.report-list li.report-list__item')->eq(1);
    }

    public function testBankNamesAreDisplayed()
    {
        $this->assertEquals('hsbc bank', trim($this->hsbcNode->filter('dd.report-list__item-fields-description')->text(), "\n "));
        $this->assertEquals('halifax bank', trim($this->halifaxNode->filter('dd.report-list__item-fields-description')->text(), "\n "));
    }
    
    public function testWarningsAreNotDisplayed()
    {
        $this->assertCount(0, $this->hsbcNode->filter('.page-section-warning'));
        $this->assertCount(0, $this->halifaxNode->filter('.page-section-warning'));
    }
    
}
