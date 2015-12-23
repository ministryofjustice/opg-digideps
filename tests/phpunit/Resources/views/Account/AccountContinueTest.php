<?php
namespace AppBundle\Resources\views\Account;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Account;
use AppBundle\Entity\Report;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Fixtures;
use Mockery as m;

class AccountContinueTest extends WebTestCase
{
    public function setUp() {
        $this->markTestSkipped('deprecated');
        $client = static::createClient([ 'environment' => 'test',
            'debug' => false]);
        $this->twig = $client->getContainer()->get('templating');
    }

    


    
}
