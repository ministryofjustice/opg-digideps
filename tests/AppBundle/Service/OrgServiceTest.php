<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity as EntityDir;
use AppBundle\Service\OrgService;
use Doctrine\ORM\EntityManager;
use Fixtures;
use Mockery as m;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrgServiceTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected static $frameworkBundleClient;

    /**
     * @var EntityManager
     */
    protected static $em;

    /**
     * @var Fixtures
     */
    protected static $fixtures;


    public static $deputy1 = [
        'Deputy No'    => '1', //will get padded
        //'Pat Create'   => '12-Dec-02',
        //'Dship Create' => '28-Sep-07',
        'Dep Postcode' => 'N1 ABC',
        'Dep Forename' => 'Dep1',
        'Dep Surname'  => 'Uty2',
        'Dep Type'     => 'SETME',
        'Dep Adrs1'    => 'ADD1',
        'Dep Adrs2'    => 'ADD2',
        'Dep Adrs3'    => 'ADD3',
        'Dep Adrs4'    => 'ADD4',
        'Dep Adrs5'    => 'ADD5',
        'Email'        => 'dep1@provider.com',
    ];

    public static $deputy2 = [
        'Deputy No'    => '00000002',
        'Dep Forename' => 'Dep2',
        'Dep Surname'  => 'Uty2',
        'Dep Type'     => 'SETME',
        'Email'        => 'dep2@provider.com',
    ];


    public static $deputy3 = [
        'Deputy No'    => '00000003',
        'Dep Forename' => 'Dep3',
        'Dep Surname'  => 'Uty3',
        'Dep Type'     => 'SETME',
        'Email'        => 'dep3@provider.com',
    ];

    public static $client1 = [
        'Case'       => '1111', //will get padded
        'Forename'   => 'Cly1',
        'Surname'    => 'Hent1',
        'Corref'     => 'L2',
        'Typeofrep'  => 'OPG102',
        'Last Report Day' => '16-Dec-2014',
        'Client Adrs1' => 'a1',
        'Client Adrs2' => 'a2',
        'Client Adrs3' => 'a3',
        'Client Postcode' => 'ap',
        'Client Phone' => 'caphone',
        'Client Email' => 'client@provider.com',
        'Client Date of Birth' => '05-Jan-47',
    ];


    public static $client2 = [
        'Case'       => '10002222',
        'Forename'   => 'Cly2',
        'Surname'    => 'Hent2',
        'Corref'     => 'L3',
        'Typeofrep'  => 'OPG103',
        'Last Report Day' => '04-Feb-2015',
    ];

    public static $client3 = [
        'Case'       => '1000000T',
        'Forename'   => 'Cly3',
        'Surname'    => 'Hent3',
        'Corref'     => 'L3G',
        'Typeofrep'  => 'OPG103',
        'Last Report Day' => '05-Feb-2015',
    ];

    public static $client4 = [
        'Case'       => '1000004T',
        'Forename'   => 'Cly4',
        'Surname'    => 'Hent4',
        'Corref'     => 'L3G',
        'Typeofrep'  => 'OPG103',
        'Last Report Day' => '06-Feb-2015',
    ];

    /**
     * @var OrgService
     */
    private $pa = null;

    public static function setUpBeforeClass()
    {
        self::$frameworkBundleClient = static::createClient(['environment' => 'test',
                                                             'debug'       => false,]);

        self::$em = self::$frameworkBundleClient->getContainer()->get('em');
        self::$fixtures = new Fixtures(self::$em);
    }

    public function setup()
    {
        $logger = m::mock(LoggerInterface::class)->shouldIgnoreMissing();
        $this->pa = new OrgService(self::$em, $logger);
        Fixtures::deleteReportsData(['dd_user', 'client']);
        self::$em->clear();
    }

    public function testPAAddFromCasrecRows()
    {
        $deputy1 = ['Dep Type'=>23] + self::$deputy1;
        $deputy2 = ['Dep Type'=>23] + self::$deputy2;
        $deputy3 = ['Dep Type'=>21] + self::$deputy3;

        // create two clients for the same deputy, each one with a report
        $data = [
            // deputy 1 with client 1 and client 2
            0 => $deputy1 + self::$client1,
            1 => $deputy1 + self::$client2,
            // deputy 2 with client 3
            2 => $deputy2 + self::$client3,
            // add one Professional
            3 => $deputy3 + self::$client4,
        ];

        $ret1 = $this->pa->addFromCasrecRows($data);
        $this->assertEmpty($ret1['errors'], implode(',', $ret1['errors']));
        $this->assertEquals([
            'pa_users'   => ['dep1@provider.com', 'dep2@provider.com'],
            'prof_users'   => ['dep3@provider.com'],
            'clients' => ['00001111', '1000000t', '1000004t', '10002222'],
            'reports' => ['00001111-2014-12-16', '1000000t-2015-02-05', '1000004t-2015-02-06', '10002222-2015-02-04']
        ], $ret1['added']);
        // add again and check no override
        $ret2 = $this->pa->addFromCasrecRows($data);
        $this->assertEquals([
            'pa_users'   => [],
            'prof_users'   => [],
            'clients' => [],
            'reports' => [],
        ], $ret2['added']);
        self::$em->clear();

        //assert 1st deputy
        $user1 = self::$fixtures->findUserByEmail('dep1@provider.com');
        $this->assertInstanceof(EntityDir\User::class, $user1, 'deputy not added');
        $this->assertEquals($user1->getRoleName(), EntityDir\User::ROLE_PA_NAMED);
        $clients = $user1->getClients();
        $this->assertCount(2, $clients);
        $this->assertCount(1, $user1->getTeams());
        $this->assertSame('00000001', $user1->getDeputyNo());

        // assert 1st client and report
        $client1 = $user1->getClientByCaseNumber('00001111');
        $this->assertSame('00001111', $client1->getCaseNumber());
        $this->assertEquals('Cly1', $client1->getFirstname());
        $this->assertEquals('Hent1', $client1->getLastname());
        $this->assertEquals('a1', $client1->getAddress());
        $this->assertEquals('a2', $client1->getAddress2());
        $this->assertEquals('a3', $client1->getCounty());
        $this->assertEquals('ap', $client1->getPostcode());
        $this->assertEquals('client@provider.com', $client1->getEmail());
        $this->assertEquals('1947-01-05', $client1->getDateOfBirth()->format('Y-m-d'));
        $this->assertCount(1, $client1->getReports());
        $client1Report1 = $client1->getReports()->first();
        /* @var $client1Report1 EntityDir\Report\Report */
        $this->assertEquals('2013-12-17', $client1Report1->getStartDate()->format('Y-m-d'));
        $this->assertEquals('2014-12-16', $client1Report1->getEndDate()->format('Y-m-d'));
        $this->assertEquals(EntityDir\Report\Report::TYPE_102_6, $client1Report1->getType());

        // assert 2nd client and report
        $client2 = $user1->getClientByCaseNumber('10002222');
        $this->assertEquals('Cly2', $client2->getFirstname());
        $this->assertEquals('Hent2', $client2->getLastname());
        $this->assertCount(1, $client2->getReports());
        $client2Report1 = $client2->getReports()->first();

        /* @var $client2Report1 EntityDir\Report\Report */
        $this->assertEquals(EntityDir\Report\Report::TYPE_103_6, $client2Report1->getType());

        // assert 2nd deputy
        $user2 = self::$fixtures->findUserByEmail('dep2@provider.com');
        $this->assertEquals($user2->getRoleName(), EntityDir\User::ROLE_PA_NAMED);
        $clients = $user2->getClients();
        $this->assertCount(1, $clients);
        $this->assertCount(1, $user2->getTeams());

        // assert 3rd deputy
        $user3 = self::$fixtures->findUserByEmail('dep3@provider.com');
        $this->assertEquals($user3->getRoleName(), EntityDir\User::ROLE_PROF_NAMED);
        $clients = $user3->getClients();
        $this->assertCount(1, $clients);
        $this->assertCount(1, $user3->getTeams());

        // assert 1st client and report
        $client1 = $user2->getClientByCaseNumber('1000000t');
        $this->assertEquals('Cly3', $client1->getFirstname());
        $this->assertEquals('Hent3', $client1->getLastname());
        $this->assertCount(1, $client1->getReports());
        $this->assertEquals(EntityDir\Report\Report::TYPE_103_6, $client1->getReports()->first()->getType());

        // check client 3 is associated with deputy2
        $this->assertCount(2, self::$fixtures->findUserByEmail('dep1@provider.com')->getClients());
        $this->assertCount(1, self::$fixtures->findUserByEmail('dep2@provider.com')->getClients());

        // assert prof client and report
        $client4 = $user3->getClientByCaseNumber('1000004t');
        $this->assertEquals('Cly4', $client4->getFirstname());
        $this->assertEquals('Hent4', $client4->getLastname());
        $this->assertCount(1, $client4->getReports());
        $this->assertEquals(EntityDir\Report\Report::TYPE_103_5, $client4->getReports()->first()->getType());

        $client3 = $user1->getClientByCaseNumber('10002222');
        $this->assertEquals('Cly2', $client2->getFirstname());
        $this->assertEquals('Hent2', $client2->getLastname());
        $this->assertCount(1, $client2->getReports());
        $client2Report1 = $client2->getReports()->first();

        // move client2 from deputy1 -> deputy2
        $dataMove = [
            $deputy2 + self::$client2,
        ];
        $this->pa->addFromCasrecRows($dataMove);
        self::$em->clear();

        // check client 3 is now associated with deputy1
        $this->assertCount(1, self::$fixtures->findUserByEmail('dep1@provider.com')->getClients());
        $this->assertCount(2, self::$fixtures->findUserByEmail('dep2@provider.com')->getClients());
        $this->assertCount(1, self::$fixtures->findUserByEmail('dep3@provider.com')->getClients());

        // check that report type changes are applied
        $data[0]['Corref'] = 'L3G';
        $data[0]['Typeofrep'] = 'OPG103';
        $this->pa->addFromCasrecRows($data);
        $this->assertEquals([
            'pa_users'   => [],
            'prof_users' => [],
            'clients' => [],
            'reports' => [],
        ], $ret2['added']);
        self::$em->clear();
        self::$em->clear();

        $user1 = self::$fixtures->findUserByEmail('dep1@provider.com');
        $this->assertInstanceof(EntityDir\User::class, $user1, 'deputy not added');
        $client1 = $user1->getClientByCaseNumber('00001111');
        $this->assertCount(1, $client1->getReports());
        $report = $client1->getReports()->first();
        $this->assertEquals(EntityDir\Report\Report::TYPE_103_6, $report->getType());
    }

    public function testProfDepAddFromCasrecRows()
    {
        $deputy1 = ['Dep Type'=>21] + self::$deputy1;
        $deputy2 = ['Dep Type'=>21] + self::$deputy2;

        // create two clients for the same deputy, each one with a report
        $data = [
            // deputy 1 with client 1 and client 2
            0 => $deputy1 + self::$client1,
            1 => $deputy1 + self::$client2,
            // deputy 2 with client 3
            2 => $deputy2 + self::$client3,
        ];

        $ret1 = $this->pa->addFromCasrecRows($data);
        $this->assertEmpty($ret1['errors'], implode(',', $ret1['errors']));
        $this->assertEquals([
            'pa_users'   => [],
            'prof_users' => ['dep1@provider.com', 'dep2@provider.com'],
            'clients' => ['00001111', '1000000t', '10002222'],
            'reports' => ['00001111-2014-12-16',  '1000000t-2015-02-05', '10002222-2015-02-04'],
        ], $ret1['added']);
        // add again and check no override
        $ret2 = $this->pa->addFromCasrecRows($data);
        $this->assertEquals([
            'pa_users'   => [],
            'prof_users' => [],
            'clients' => [],
            'reports' => [],
        ], $ret2['added']);
        self::$em->clear();

        //assert 1st deputy
        $user1 = self::$fixtures->findUserByEmail('dep1@provider.com');
        $this->assertInstanceof(EntityDir\User::class, $user1, 'deputy not added');
        $this->assertEquals($user1->getRoleName(), EntityDir\User::ROLE_PROF_NAMED);
        $clients = $user1->getClients();
        $this->assertCount(2, $clients);
        $this->assertCount(1, $user1->getTeams());
        $this->assertSame('00000001', $user1->getDeputyNo());

        // assert 1st client and report
        $client1 = $user1->getClientByCaseNumber('00001111');
        $this->assertSame('00001111', $client1->getCaseNumber());
        $this->assertEquals('Cly1', $client1->getFirstname());
        $this->assertEquals('Hent1', $client1->getLastname());
        $this->assertEquals('a1', $client1->getAddress());
        $this->assertEquals('a2', $client1->getAddress2());
        $this->assertEquals('a3', $client1->getCounty());
        $this->assertEquals('ap', $client1->getPostcode());
        $this->assertEquals('client@provider.com', $client1->getEmail());
        $this->assertEquals('1947-01-05', $client1->getDateOfBirth()->format('Y-m-d'));
        $this->assertCount(1, $client1->getReports());
        $client1Report1 = $client1->getReports()->first();
        /* @var $client1Report1 EntityDir\Report\Report */
        $this->assertEquals('2013-12-17', $client1Report1->getStartDate()->format('Y-m-d'));
        $this->assertEquals('2014-12-16', $client1Report1->getEndDate()->format('Y-m-d'));
        $this->assertEquals(EntityDir\Report\Report::TYPE_102_5, $client1Report1->getType());

        // assert 2nd client and report
        $client2 = $user1->getClientByCaseNumber('10002222');
        $this->assertEquals('Cly2', $client2->getFirstname());
        $this->assertEquals('Hent2', $client2->getLastname());
        $this->assertCount(1, $client2->getReports());
        $client2Report1 = $client2->getReports()->first();
        /* @var $client2Report1 EntityDir\Report\Report */
        $this->assertEquals(EntityDir\Report\Report::TYPE_103_5, $client2Report1->getType());

        // assert 2nd deputy
        $user2 = self::$fixtures->findUserByEmail('dep2@provider.com');
        $this->assertEquals($user2->getRoleName(), EntityDir\User::ROLE_PROF_NAMED);
        $clients = $user2->getClients();
        $this->assertCount(1, $clients);
        $this->assertCount(1, $user2->getTeams());

        // assert 1st client and report
        $client1 = $user2->getClientByCaseNumber('1000000t');
        $this->assertEquals('Cly3', $client1->getFirstname());
        $this->assertEquals('Hent3', $client1->getLastname());
        $this->assertCount(1, $client1->getReports());
        $this->assertEquals(EntityDir\Report\Report::TYPE_103_5, $client1->getReports()->first()->getType());


        // check client 3 is associated with deputy2
        $this->assertCount(2, self::$fixtures->findUserByEmail('dep1@provider.com')->getClients());
        $this->assertCount(1, self::$fixtures->findUserByEmail('dep2@provider.com')->getClients());

        // move client2 from deputy1 -> deputy2
        $dataMove = [
            $deputy2 + self::$client2,
        ];
        $this->pa->addFromCasrecRows($dataMove);
        self::$em->clear();

        // check client 3 is now associated with deputy1
        $this->assertCount(1, self::$fixtures->findUserByEmail('dep1@provider.com')->getClients());
        $this->assertCount(2, self::$fixtures->findUserByEmail('dep2@provider.com')->getClients());

        // check that report type changes are applied
        $data[0]['Corref'] = 'L3G';
        $data[0]['Typeofrep'] = 'OPG103';
        $this->pa->addFromCasrecRows($data);
        $this->assertEquals([
            'pa_users'   => [],
            'prof_users'   => [],
            'clients' => [],
            'reports' => [],
        ], $ret2['added']);
        self::$em->clear();
        self::$em->clear();

        $user1 = self::$fixtures->findUserByEmail('dep1@provider.com');
        $this->assertInstanceof(EntityDir\User::class, $user1, 'deputy not added');
        $client1 = $user1->getClientByCaseNumber('00001111');
        $this->assertCount(1, $client1->getReports());
        $report = $client1->getReports()->first();
        $this->assertEquals(EntityDir\Report\Report::TYPE_103_5, $report->getType());
    }

    public function tearDown()
    {
        m::close();
    }
}
