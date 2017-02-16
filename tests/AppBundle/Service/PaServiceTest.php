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
        self::$frameworkBundleClient = static::createClient(['environment' => 'test',
                                                             'debug'       => true,]);

        self::$em = self::$frameworkBundleClient->getContainer()->get('em');
    }

    /**
     * @var PaService
     */
    private $pa;

    public static $deputy1 = [
        'Deputy No'    => '00000001',
        'Pat Create'   => '12-Dec-02',
        'Dship Create' => '28-Sep-07',
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

    public static $deputy2 = [
        'Deputy No'    => '00000002',
        'Pat Create'   => '16-Dec-14',
        'Dship Create' => '07-Apr-15',
        'Dep Postcode' => 'SW1',
        'Dep Forename' => 'Dep2',
        'Dep Surname'  => 'Uty2',
        'Dep Type'     => 23,
        'Dep Adrs1'    => 'ADD1',
        'Dep Adrs2'    => 'ADD2',
        'Dep Adrs3'    => 'ADD3',
        'Dep Adrs4'    => 'ADD4',
        'Dep Adrs5'    => 'ADD5',
        'Email'        => 'dep2@provider.com',
    ];

    public static $client1 = [
        'Case'       => '10000001',
        'Forename'   => 'Cly1',
        'Surname'    => 'Hent1',
        'Corref'     => 'A2',
        'Report Due' => '16-Dec-14',
    ];


    public static $client2 = [
        'Case'       => '10000002',
        'Forename'   => 'Cly2',
        'Surname'    => 'Hent2',
        'Corref'     => 'A3',
        'Report Due' => '04-Feb-15',
    ];

    public static $client3 = [
        'Case'       => '10000003',
        'Forename'   => 'Cly3',
        'Surname'    => 'Hent3',
        'Corref'     => 'A3',
        'Report Due' => '05-Feb-15',
    ];

    public function setup()
    {
        $this->pa = new PaService(self::$em);
        Fixtures::deleteReportsData(['dd_user']);
        self::$em->clear();
    }

    public function testAddFromCasrecRows()
    {
        // create two clients for the same deputy, each one with a report
        $data = [
            self::$deputy1 + self::$client1,
            self::$deputy1 + self::$client2,
            self::$deputy2 + self::$client3,

        ];

        // add twice to check duplicates are not added
        $ret1 = $this->pa->addFromCasrecRows($data);
        $ret2 = $this->pa->addFromCasrecRows($data);
        // check return values
        $this->assertEquals([
            'users'   => ['dep1@provider.com', 'dep2@provider.com'],
            'clients' => ['10000001', '10000002', '10000003'],
            'reports' => ['10000001-2014-12-16', '10000002-2015-02-04', '10000003-2015-02-05'],
        ], $ret1);
        $this->assertEquals([
            'users'   => [],
            'clients' => [],
            'reports' => [],
        ], $ret2);

        self::$em->clear();

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
        $this->assertEquals('2014-12-16', $client1Report1->getEndDate()->format('Y-m-d'));

        // assert 2nd client and report
        $client2 = $user1->getClientByCaseNumber('10000002');
        $this->assertEquals('Cly2', $client2->getFirstname());
        $this->assertEquals('Hent2', $client2->getLastname());
        $this->assertCount(1, $client2->getReports());
        $client2Report1 = $client2->getReports()->first();
        /* @var $client2Report1 EntityDir\Report\Report */
        $this->assertEquals('2015-02-04', $client2Report1->getEndDate()->format('Y-m-d'));
        $this->assertEquals(EntityDir\Report\Report::TYPE_102, $client2Report1->getType());

        // assert 2nd deputy
        $user2 = self::$em->getRepository(EntityDir\User::class)->findOneBy(['email' => 'dep2@provider.com']);
        $clients = $user2->getClients();
        $this->assertCount(1, $clients);

        // assert 1st client and report
        $client1 = $user2->getClientByCaseNumber('10000003');
        $this->assertEquals('Cly3', $client1->getFirstname());
        $this->assertEquals('Hent3', $client1->getLastname());
        $this->assertCount(1, $client1->getReports());
        $client1Report1 = $client1->getReports()->first();
        /* @var $client1Report1 EntityDir\Report\Report */
        $this->assertEquals('2015-02-05', $client1Report1->getEndDate()->format('Y-m-d'));
    }

    public function tearDown()
    {
        m::close();
    }


}
