<?php

namespace Tests\AppBundle\Service;

use Doctrine\ORM\EntityManager;
use Fixtures;
use AppBundle\Entity as EntityDir;
use AppBundle\Service\PaService;
use Mockery as m;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class PaServiceTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected static $frameworkBundleClient;

    /**
     * @var EntityManager
     */
    protected static $em;

    public static function setUpBeforeClass()
    {
        Fixtures::deleteReportsData();

        self::$frameworkBundleClient = static::createClient(['environment' => 'test',
                                                             'debug'       => true,]);

        self::$em = self::$frameworkBundleClient->getContainer()->get('em');
    }

    /**
     * @var PaService
     */
    private $pa;

    public function setup()
    {
        $this->pa = new PaService(self::$em);
    }

    public function testAddFromCasrecRows()
    {
        $deputy1 = [
            'Deputy No'    => '00000001',
            'Dep Postcode' => 'N1 ABC',
            'Dep Forename' => 'Dep1',
            'Dep Surname'  => 'Uty2',
            'Dep Type'     => 23,
            'Dep Adrs1'    => 'ADD1',
            'Dep Adrs2'    => 'ADD2',
            'Dep Adrs3'    => 'ADD3',
            'Dep Adrs4'    => 'ADD4',
            'Dep Adrs5'    => 'ADD5',
            'Email'        => 'dep1@provider.com',
        ];

        $deputy2 = [
            'Deputy No'    => '00000002',
            'Dep Postcode' => 'SW1',
            'Dep Forename' => 'Dep2',
            'Dep Surname'  => 'Uty2',
            'Dep Type'     => 23,
            'Email'        => 'dep2@provider.com',
        ];

        // create two clients for the same deputy, each one with a report
        $data = [
            $deputy1 + [
                'Case'       => '10000001',
                'Forename'   => 'Cly1',
                'Surname'    => 'Hent1',
                'Corref'     => 'A2',
                'Report Due' => '16-Dec-14',
            ],
            $deputy1 + [
                'Case'       => '10000002',
                'Forename'   => 'Cly2',
                'Surname'    => 'Hent2',
                'Corref'     => 'A3',
                'Report Due' => '04-Feb-15',
            ],
            $deputy2 + [
                'Case'       => '10000003',
                'Forename'   => 'Cly3',
                'Surname'    => 'Hent3',
                'Corref'     => 'A3',
                'Report Due' => '05-Feb-15',
            ],

        ];

        // add twice to check duplicates are not added
        $this->pa->addFromCasrecRows($data);
        $this->pa->addFromCasrecRows($data);

        //assert 1st deputy
        $user1 = self::$em->getRepository(EntityDir\User::class)->findOneBy(['email' => 'dep1@provider.com']);
        $this->assertInstanceof(EntityDir\User::class, $user1, 'deputy not added');
        $clients = $user1->getClients();
        $this->assertCount(2, $clients);

        // assert 1st client and report
        $client1 = $user1->getClientByCaseNumber('10000001');
        $this->assertEquals('Cly1', $client1->getFirstname());
        $this->assertEquals('Hent1', $client1->getLastname());
        $this->assertCount(1, $client1->getReports());
        $client1Report1 = $client1->getReports()->first();
        /* @var $client1Report1 EntityDir\Report\Report */
        $this->assertEquals('2014-12-16', $client1Report1->getDueDate()->format('Y-m-d'));

        // assert 2nd client and report
        $client2 = $user1->getClientByCaseNumber('10000002');
        $this->assertEquals('Cly2', $client2->getFirstname());
        $this->assertEquals('Hent2', $client2->getLastname());
        $this->assertCount(1, $client2->getReports());
        $client2Report1 = $client2->getReports()->first();
        /* @var $client2Report1 EntityDir\Report\Report */
        $this->assertEquals('2015-02-04', $client2Report1->getDueDate()->format('Y-m-d'));

        // assert 2nd deputy
        $user2 = self::$em->getRepository(EntityDir\User::class)->findOneBy(['email' => 'dep2@provider.com']);
        $clients = $user2->getClients();
        $this->assertCount(1, $clients);

        // assert 1st client and report
        $client1 = $user1->getClientByCaseNumber('10000003');
        $this->assertEquals('Cly3', $client1->getFirstname());
        $this->assertEquals('Hent3', $client1->getLastname());
        $this->assertCount(1, $client1->getReports());
        $client1Report1 = $client1->getReports()->first();
        /* @var $client1Report1 EntityDir\Report\Report */
        $this->assertEquals('2015-02-05', $client1Report1->getDueDate()->format('Y-m-d'));
    }

    public function tearDown()
    {
        m::close();
    }


}
