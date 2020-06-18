<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity as EntityDir;
use AppBundle\Factory\NamedDeputyFactory;
use AppBundle\Entity\Organisation;
use AppBundle\Entity\Repository\ClientRepository;
use AppBundle\Entity\Repository\OrganisationRepository;
use AppBundle\Entity\Repository\ReportRepository;
use AppBundle\Entity\Repository\TeamRepository;
use AppBundle\Entity\Repository\UserRepository;
use AppBundle\Entity\Repository\NamedDeputyRepository;
use AppBundle\Factory\OrganisationFactory;
use AppBundle\Service\CourtOrderCreator;
use AppBundle\Service\OrgService;
use AppBundle\v2\Assembler\CourtOrder\OrgCsvToCourtOrderDtoAssembler;
use AppBundle\v2\Assembler\CourtOrderDeputy\OrgCsvToCourtOrderDeputyDtoAssembler;
use AppBundle\v2\DTO\CourtOrderDeputyDto;
use AppBundle\v2\DTO\CourtOrderDto;
use DateTime;
use Doctrine\ORM\EntityManager;
use Mockery as m;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Fixtures;

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
        'Dep Adrs1'    => '',
        'Dep Postcode' => '',
    ];

    public static $deputy3 = [
        'Deputy No'    => '00000003',
        'Dep Forename' => 'Dep3',
        'Dep Surname'  => 'Uty3',
        'Dep Type'     => 'SETME',
        'Email'        => 'dep3@provider.com',
        'Dep Adrs1'    => '',
        'Dep Postcode' => '',
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
        'Made Date' => '01-Jan-2015'
    ];


    public static $client2 = [
        'Case'       => '10002222',
        'Forename'   => 'Cly2',
        'Surname'    => 'Hent2',
        'Corref'     => 'L3',
        'Typeofrep'  => 'OPG103',
        'Last Report Day' => '04-Feb-2015',
        'Made Date' => '01-Jan-2015'
    ];

    public static $client3 = [
        'Case'       => '1000000T',
        'Forename'   => 'Cly3',
        'Surname'    => 'Hent3',
        'Corref'     => 'L3G',
        'Typeofrep'  => 'OPG103',
        'Last Report Day' => '05-Feb-2015',
        'Made Date' => '01-Jan-2015'
    ];

    public static $client4 = [
        'Case'       => '1000004T',
        'Forename'   => 'Cly4',
        'Surname'    => 'Hent4',
        'Corref'     => 'L3G',
        'Typeofrep'  => 'OPG103',
        'Last Report Day' => '06-Feb-2015',
        'Made Date' => '01-Jan-2015'
    ];

    /**
     * @var OrgService
     */
    private $pa = null;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var ReportRepository
     */
    private $reportRepository;

    /**
     * @var ClientRepository
     */
    private $clientRepository;

    /**
     * @var OrganisationRepository
     */
    private $organisationRepository;

    /**
     * @var NamedDeputyRepository
     */
    private $namedDeputyRepository;
    /**
     * @var TeamRepository
     */
    private $teamRepository;

    /** @var OrgCsvToCourtOrderDtoAssembler */
    private $courtOrderAssembler;

    /** @var OrgCsvToCourtOrderDeputyDtoAssembler */
    private $courtOrderDeputyAssembler;

    /** @var CourtOrderCreator|ObjectProphecy */
    private $courtOrderCreator;

    public static function setUpBeforeClass(): void
    {
        self::$frameworkBundleClient = static::createClient(['environment' => 'test',
                                                             'debug'       => false,]);

        /** @var ContainerInterface $container */
        $container = self::$frameworkBundleClient->getContainer();
        self::$em = $container->get('em');
        self::$fixtures = new Fixtures(self::$em);
    }

    public function setUp(): void
    {
        /** @var ContainerInterface $container */
        $container = self::$frameworkBundleClient->getContainer();

        $this->logger = m::mock(LoggerInterface::class);
        $this->logger->shouldIgnoreMissing();
        $this->userRepository = $container->get('AppBundle\Entity\Repository\UserRepository');
        $this->reportRepository = $container->get('AppBundle\Entity\Repository\ReportRepository');
        $this->clientRepository = $container->get('AppBundle\Entity\Repository\ClientRepository');
        $this->organisationRepository = $container->get('AppBundle\Entity\Repository\OrganisationRepository');
        $this->teamRepository = $container->get('AppBundle\Entity\Repository\TeamRepository');
        $this->namedDeputyRepository = $container->get('AppBundle\Entity\Repository\NamedDeputyRepository');
        $this->courtOrderAssembler = $container->get('AppBundle\v2\Assembler\CourtOrder\OrgCsvToCourtOrderDtoAssembler');
        $this->courtOrderDeputyAssembler = $container->get('AppBundle\v2\Assembler\CourtOrderDeputy\OrgCsvToCourtOrderDeputyDtoAssembler');

        $this->courtOrderCreator = self::prophesize(CourtOrderCreator::class);
        $this->courtOrderCreator
            ->upsertCourtOrder(Argument::type(CourtOrderDto::class), Argument::type(EntityDir\Report\Report::class))
            ->willReturn(new EntityDir\CourtOrder());
        $this->courtOrderCreator
            ->upsertCourtOrderDeputy(Argument::type(CourtOrderDeputyDto::class), Argument::type(EntityDir\CourtOrder::class), Argument::type(EntityDir\Organisation::class))
            ->willReturn(new EntityDir\CourtOrderDeputy());

        $this->pa = new OrgService(self::$em,
            $this->logger,
            $this->userRepository,
            $this->reportRepository,
            $this->clientRepository,
            $this->organisationRepository,
            $this->teamRepository,
            $this->namedDeputyRepository,
            new OrganisationFactory([]),
            new NamedDeputyFactory(),
            $this->courtOrderAssembler,
            $this->courtOrderDeputyAssembler,
            $this->courtOrderCreator->reveal()
        );

        Fixtures::deleteReportsData(['dd_user', 'client']);
        self::$em->clear();
    }

    /**
     * @param EntityDir\Client[] $clients
     * @param string $caseNumber
     * @return EntityDir\Client
     */
    public function getClientByCaseNumber(iterable $clients, string $caseNumber)
    {
        foreach ($clients as $client) {
            if ($client->getCaseNumber() === $caseNumber) {
                return $client;
            }
        }

        throw new \RuntimeException("Couldn't find client with case number $caseNumber");
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
            'named_deputies' => ['00000001', '00000002', '00000003'],
            'clients' => ['00001111', '1000000t', '1000004t', '10002222'],
            'reports' => ['00001111-2014-12-16', '1000000t-2015-02-05', '1000004t-2015-02-06', '10002222-2015-02-04'],
            'discharged_clients' => [],
        ], $ret1['added']);
        // add again and check no override
        $ret2 = $this->pa->addFromCasrecRows($data);
        $this->assertEquals([
            'named_deputies' => [],
            'clients' => [],
            'reports' => [],
            'discharged_clients' => [],
        ], $ret2['added']);
        self::$em->clear();

        //assert 1st deputy
        $nd1 = self::$fixtures->findNamedDeputyByNumber('00000001');
        $this->assertInstanceof(EntityDir\NamedDeputy::class, $nd1);
        $this->assertEquals('Dep1', $nd1->getFirstname());
        $this->assertEquals('Uty2', $nd1->getLastname());

        $clients = $nd1->getClients();
        $this->assertCount(2, $clients);

        // assert 1st client and report
        $client1 = $this->getClientByCaseNumber($clients, '00001111');
        $this->assertSame('00001111', $client1->getCaseNumber());
        $this->assertEquals('Cly1', $client1->getFirstname());
        $this->assertEquals('Hent1', $client1->getLastname());
        $this->assertEquals('a1', $client1->getAddress());
        $this->assertEquals('a2', $client1->getAddress2());
        $this->assertEquals('a3', $client1->getCounty());
        $this->assertEquals('ap', $client1->getPostcode());
        $this->assertEquals('client@provider.com', $client1->getEmail());
        $this->assertEquals(new DateTime('01-Jan-2015'), $client1->getCourtDate());
        $this->assertInstanceOf(DateTime::class, $client1->getDateOfBirth());
        $this->assertEquals('1947-01-05', $client1->getDateOfBirth()->format('Y-m-d'));
        $this->assertCount(1, $client1->getReports());
        $client1Report1 = $client1->getReports()->first();
        /* @var $client1Report1 EntityDir\Report\Report */
        $this->assertEquals('2013-12-17', $client1Report1->getStartDate()->format('Y-m-d'));
        $this->assertEquals('2014-12-16', $client1Report1->getEndDate()->format('Y-m-d'));
        $this->assertEquals(EntityDir\Report\Report::TYPE_102_6, $client1Report1->getType());

        // assert 2nd client and report
        $client2 = $this->getClientByCaseNumber($clients, '10002222');
        $this->assertEquals('Cly2', $client2->getFirstname());
        $this->assertEquals('Hent2', $client2->getLastname());
        $this->assertCount(1, $client2->getReports());

        $client2Report1 = $client2->getReports()->first();

        /* @var $client2Report1 EntityDir\Report\Report */
        $this->assertEquals(EntityDir\Report\Report::TYPE_103_6, $client2Report1->getType());

        // assert 2nd deputy
        $nd2 = self::$fixtures->findNamedDeputyByNumber('00000002');
        $this->assertInstanceof(EntityDir\NamedDeputy::class, $nd2);

        $clients = $nd2->getClients();
        $this->assertCount(1, $clients);

        // assert 3rd deputy
        $nd3 = self::$fixtures->findNamedDeputyByNumber('00000003');
        $this->assertInstanceof(EntityDir\NamedDeputy::class, $nd3);

        $clients = $nd3->getClients();
        $this->assertCount(1, $clients);

        // assert 1st client and report
        $client1 = $this->getClientByCaseNumber($nd2->getClients(), '1000000t');
        $this->assertEquals('Cly3', $client1->getFirstname());
        $this->assertEquals('Hent3', $client1->getLastname());
        $this->assertCount(1, $client1->getReports());
        $this->assertEquals(EntityDir\Report\Report::TYPE_103_6, $client1->getReports()->first()->getType());

        // check client 3 is associated with deputy2
        $this->assertCount(2, self::$fixtures->findNamedDeputyByNumber('00000001')->getClients());
        $this->assertCount(1, self::$fixtures->findNamedDeputyByNumber('00000002')->getClients());

        // assert prof client and report
        $client4 = $this->getClientByCaseNumber($nd3->getClients(), '1000004t');
        $this->assertEquals('Cly4', $client4->getFirstname());
        $this->assertEquals('Hent4', $client4->getLastname());
        $this->assertCount(1, $client4->getReports());
        $this->assertEquals(EntityDir\Report\Report::TYPE_103_5, $client4->getReports()->first()->getType());

        $client3 = $this->getClientByCaseNumber($nd1->getClients(), '10002222');
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

        // check client 2 remains with deputy 1
        $this->assertCount(2, self::$fixtures->findNamedDeputyByNumber('00000001')->getClients());
        $this->assertCount(1, self::$fixtures->findNamedDeputyByNumber('00000002')->getClients());
        $this->assertCount(1, self::$fixtures->findNamedDeputyByNumber('00000003')->getClients());

        // check that report type changes are applied
        $data[0]['Corref'] = 'L3G';
        $data[0]['Typeofrep'] = 'OPG103';
        $this->pa->addFromCasrecRows($data);
        $this->assertEquals([
            'named_deputies' => [],
            'clients' => [],
            'reports' => [],
            'discharged_clients' => [],
        ], $ret2['added']);
        self::$em->clear();
        self::$em->clear();

        $nd1 = self::$fixtures->findNamedDeputyByNumber('00000001');
        $this->assertInstanceof(EntityDir\NamedDeputy::class, $nd1, 'deputy not added');

        $client1 = $nd1->getClients()[0];
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
            'named_deputies' => [],
            'clients' => ['00001111', '1000000t', '10002222'],
            'reports' => ['00001111-2014-12-16',  '1000000t-2015-02-05', '10002222-2015-02-04'],
            'discharged_clients' => [],
        ], $ret1['added']);
        // add again and check no override
        $ret2 = $this->pa->addFromCasrecRows($data);
        $this->assertEquals([
            'named_deputies' => [],
            'clients' => [],
            'reports' => [],
            'discharged_clients' => [],
        ], $ret2['added']);
        self::$em->clear();

        //assert 1st deputy
        $nd1 = self::$fixtures->findNamedDeputyByNumber('00000001');
        $this->assertInstanceof(EntityDir\NamedDeputy::class, $nd1);
        $this->assertEquals('Dep1', $nd1->getFirstname());
        $this->assertEquals('Uty2', $nd1->getLastname());

        $clients = $nd1->getClients();
        $this->assertCount(2, $clients);

        // assert 1st client and report
        $client1 = $this->getClientByCaseNumber($clients, '00001111');
        $this->assertSame('00001111', $client1->getCaseNumber());
        $this->assertEquals('Cly1', $client1->getFirstname());
        $this->assertEquals('Hent1', $client1->getLastname());
        $this->assertEquals('a1', $client1->getAddress());
        $this->assertEquals('a2', $client1->getAddress2());
        $this->assertEquals('a3', $client1->getCounty());
        $this->assertEquals('ap', $client1->getPostcode());
        $this->assertEquals('client@provider.com', $client1->getEmail());
        $this->assertInstanceOf(DateTime::class, $client1->getDateOfBirth());
        $this->assertEquals('1947-01-05', $client1->getDateOfBirth()->format('Y-m-d'));
        $this->assertCount(1, $client1->getReports());
        $client1Report1 = $client1->getReports()->first();
        /* @var $client1Report1 EntityDir\Report\Report */
        $this->assertEquals('2013-12-17', $client1Report1->getStartDate()->format('Y-m-d'));
        $this->assertEquals('2014-12-16', $client1Report1->getEndDate()->format('Y-m-d'));
        $this->assertEquals(EntityDir\Report\Report::TYPE_102_5, $client1Report1->getType());

        // assert 2nd client and report
        $client2 = $this->getClientByCaseNumber($clients, '10002222');
        $this->assertEquals('Cly2', $client2->getFirstname());
        $this->assertEquals('Hent2', $client2->getLastname());
        $this->assertCount(1, $client2->getReports());
        $client2Report1 = $client2->getReports()->first();
        /* @var $client2Report1 EntityDir\Report\Report */
        $this->assertEquals(EntityDir\Report\Report::TYPE_103_5, $client2Report1->getType());

        // assert 2nd deputy
        $nd2 = self::$fixtures->findNamedDeputyByNumber('00000002');
        $this->assertInstanceof(EntityDir\NamedDeputy::class, $nd2);

        $clients = $nd2->getClients();
        $this->assertCount(1, $clients);

        // assert 1st client and report
        $client1 = $this->getClientByCaseNumber($clients, '1000000t');
        $this->assertEquals('Cly3', $client1->getFirstname());
        $this->assertEquals('Hent3', $client1->getLastname());
        $this->assertCount(1, $client1->getReports());
        $this->assertEquals(EntityDir\Report\Report::TYPE_103_5, $client1->getReports()->first()->getType());


        // check client 3 is associated with deputy2
        $this->assertCount(2, self::$fixtures->findNamedDeputyByNumber('00000001')->getClients());
        $this->assertCount(1, self::$fixtures->findNamedDeputyByNumber('00000002')->getClients());

        // move client2 from deputy1 -> deputy2
        $dataMove = [
            $deputy2 + self::$client2,
        ];
        $this->pa->addFromCasrecRows($dataMove);
        self::$em->clear();

        // check client 2 remains with deputy 1
        $this->assertCount(2, self::$fixtures->findNamedDeputyByNumber('00000001')->getClients());
        $this->assertCount(1, self::$fixtures->findNamedDeputyByNumber('00000002')->getClients());

        // check that report type changes are applied
        $data[0]['Corref'] = 'L3G';
        $data[0]['Typeofrep'] = 'OPG103';
        $this->pa->addFromCasrecRows($data);
        $this->assertEquals([
            'named_deputies' => [],
            'clients' => [],
            'reports' => [],
            'discharged_clients' => [],
        ], $ret2['added']);
        self::$em->clear();
        self::$em->clear();

        $nd1 = self::$fixtures->findNamedDeputyByNumber('00000001');

        $client1 = $this->getClientByCaseNumber($nd1->getClients(), '00001111');
        $this->assertCount(1, $client1->getReports());
        $report = $client1->getReports()->first();
        $this->assertEquals(EntityDir\Report\Report::TYPE_103_5, $report->getType());
    }

    public function testIgnoreClientsWithLayDeputies()
    {
        // Set up a lay deputy and client
        $deputy1 = self::$fixtures->createUser([
            'setRolename' => 'ROLE_LAY_DEPUTY',
            'setEmail' => 'testlaydeputy@digital.justice.gov.uk',
        ]);
        $client1 = self::$fixtures->createClient($deputy1, ['setCaseNumber' => '38973539']);
        self::$fixtures->flush()->clear();

        // Add professional deputy with same case number
        $row = [
            'Deputy No'    => '00000002',
            'Dep Forename' => 'Dep2',
            'Dep Surname'  => 'Uty2',
            'Dep Type'     => '21',
            'Email'        => 'dep2@provider.com',
            'Case'         => '38973539',
            'Forename'     => 'Cly2',
            'Surname'      => 'Hent2',
            'Corref'       => 'L3',
            'Typeofrep'    => 'OPG103',
            'Last Report Day' => '04-Feb-2015',
        ];
        $out = $this->pa->addFromCasrecRows([$row]);

        $this->assertCount(1, $out['errors']);
        $this->assertStringContainsString('Case number already used', $out['errors'][0]);

        $clients = self::$fixtures->getRepo('Client')->findBy(['caseNumber' => '38973539']);

        $this->assertCount(1, $clients);
        $this->assertCount(1, $client1->getUsers());
        $this->assertEquals('testlaydeputy@digital.justice.gov.uk', $client1->getUsers()[0]->getEmail());
    }

    public function testOrgNameSetToDefaultDuringCSVUpload()
    {
        $row = [
            'Deputy No'    => '01234567',
            'Dep Forename' => 'Dep2',
            'Dep Surname'  => 'Uty2',
            'Dep Type'     => '21',
            'Email'        => 'dep2@testing.com',
            'Case'         => '38973539',
            'Forename'     => 'Cly2',
            'Surname'      => 'Hent2',
            'Corref'       => 'L3',
            'Typeofrep'    => 'OPG103',
            'Last Report Day' => '04-Feb-2015',
            'Name' => 'Test Org'
        ];

        /** @var OrganisationFactory&ObjectProphecy $orgFactory */
        $orgFactory = self::prophesize(OrganisationFactory::class);
        $orgFactory->createFromFullEmail('Your Organisation', Argument::any())
            ->shouldBeCalled()
            ->willReturn(new Organisation());

        /** @var NamedDeputyFactory&ObjectProphecy $namedDeputyFactory */
        $namedDeputyFactory = self::prophesize(NamedDeputyFactory::class);

        /** @var OrganisationRepository&ObjectProphecy $orgRespository */
        $orgRespository = self::prophesize(OrganisationRepository::class);
        $orgRespository->findByEmailIdentifier(Argument::any())->willReturn(null);

        /** @var NamedDeputyRepository&ObjectProphecy $namedDeputyRepository */
        $namedDeputyRepository = self::prophesize(NamedDeputyRepository::class);

        $sut = new OrgService(self::$em,
            $this->logger,
            $this->userRepository,
            $this->reportRepository,
            $this->clientRepository,
            $orgRespository->reveal(),
            $this->teamRepository,
            $namedDeputyRepository->reveal(),
            $orgFactory->reveal(),
            $namedDeputyFactory->reveal(),
            $this->courtOrderAssembler,
            $this->courtOrderDeputyAssembler,
            $this->courtOrderCreator->reveal()
        );

        $sut->addFromCasrecRows([$row]);
    }

    public function testDontUpdateExistingClients()
    {
        $row = [
            'Deputy No'       => '01234567',
            'Dep Forename'    => 'Dep2',
            'Dep Surname'     => 'Uty2',
            'Dep Type'        => '21',
            'Email'           => 'dep2@testing.com',
            'Case'            => '38973539',
            'Forename'        => 'Cly2',
            'Surname'         => 'Hent2',
            'Client Adrs1'    => 'Address 1',
            'Client Phone'    => '07123456789',
            'Client Email'    => 'client@example.com',
            'Last Report Day' => '23-JUN-2016',
            'Made Date'       => '01-JAN-2016',
            'Corref'          => 'l2',
            'Typeofrep'       => 'opg102'
        ];

        /** @var EntityManager&ObjectProphecy $em */
        $em = $this->prophesize(EntityManager::class);
        $em->persist(Argument::any())->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        /** @var EntityDir\Report\Report&ObjectProphecy $report */
        $report = $this->prophesize(EntityDir\Report\Report::class);
        $report->getType()->shouldBeCalled()->willReturn('102-5');

        /** @var EntityDir\Client&ObjectProphecy $client */
        $client = $this->prophesize(EntityDir\Client::class);
        $client->hasDeputies()->shouldBeCalled()->willReturn(false);
        $client->getCurrentReport()->shouldBeCalled()->willReturn($report->reveal());
        $client->setCourtDate(Argument::any())->shouldBeCalled();
        $client->getOrganisation()->shouldBeCalled();
        $client->getNamedDeputy()->shouldBeCalled();
        $client->setNamedDeputy(Argument::any())->shouldBeCalled();

        // Ensure no client data is updated
        $client->setCaseNumber(Argument::any())->shouldNotBeCalled();
        $client->setFirstname(Argument::any())->shouldNotBeCalled();
        $client->setLastname(Argument::any())->shouldNotBeCalled();
        $client->setAddress(Argument::any())->shouldNotBeCalled();
        $client->setPhone(Argument::any())->shouldNotBeCalled();
        $client->setEmail(Argument::any())->shouldNotBeCalled();
        $client->setOrganisation(Argument::any())->shouldNotBeCalled();

        /** @var ClientRepository&ObjectProphecy $clientRepository */
        $clientRepository = self::prophesize(ClientRepository::class);
        $clientRepository->findOneBy(['caseNumber' => '38973539'])->willReturn($client->reveal());

        $sut = new OrgService(
            $em->reveal(),
            $this->logger,
            $this->userRepository,
            $this->reportRepository,
            $clientRepository->reveal(),
            $this->organisationRepository,
            $this->teamRepository,
            $this->namedDeputyRepository,
            new OrganisationFactory([]),
            new NamedDeputyFactory(),
            $this->courtOrderAssembler,
            $this->courtOrderDeputyAssembler,
            $this->courtOrderCreator->reveal()
        );

        $output = $sut->addFromCasrecRows([ $row ]);
        $this->assertEmpty($output['errors']);
    }

    public function testIdentifiesDeputiesByNameEmailAddress()
    {
        $namedDeputy = new EntityDir\NamedDeputy();

        /** @var NamedDeputyRepository&ObjectProphecy $namedDeputyRepository */
        $namedDeputyRepository = $this->prophesize(NamedDeputyRepository::class);

        $namedDeputyRepository->findOneBy([
            'deputyNo' => '00000001',
            'email1' => 'dep1@provider.com',
            'firstname' => 'Dep1',
            'lastname' => 'Uty2',
            'address1' => 'ADD1',
            'addressPostcode' => 'N1 ABC',
        ])->shouldBeCalled()->willReturn($namedDeputy);
        $namedDeputyRepository->findOneBy([
            'deputyNo' => '00000002',
            'email1' => 'dep2@provider.com',
            'firstname' => 'Dep2',
            'lastname' => 'Uty2',
            'address1' => null,
            'addressPostcode' => null,
        ])->shouldBeCalled()->willReturn(null);

        $sut = new OrgService(
            self::$em,
            $this->logger,
            $this->userRepository,
            $this->reportRepository,
            $this->clientRepository,
            $this->organisationRepository,
            $this->teamRepository,
            $namedDeputyRepository->reveal(),
            new OrganisationFactory([]),
            new NamedDeputyFactory(),
            $this->courtOrderAssembler,
            $this->courtOrderDeputyAssembler,
            $this->courtOrderCreator->reveal()
        );

        $this->assertEquals($namedDeputy, $sut->identifyNamedDeputy(self::$deputy1 + self::$client1));
        $this->assertEquals(null, $sut->identifyNamedDeputy(self::$deputy2 + self::$client2));
    }

    public function tearDown(): void
    {
        m::close();
    }
}
