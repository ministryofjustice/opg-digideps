<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Ndr\Ndr;

class ClientControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $report1;
    private static $deputy2;
    private static $client2;
    private static $report2;

    private static $tokenAdmin = null;
    private static $tokenDeputy = null;
    private static $tokenPa = null;
    private static $tokenProf = null;

    // pa
    private static $pa1;
    private static $prof1;
    private static $pa1Client1;
    private static $pa1Client1Report1;


    private $updateDataLay = [
        'firstname' => 'Firstname',
        'lastname' => 'Lastname',
        'case_number' => 'CaseNumber',
        'allowed_court_order_types' => [],
        'address' => 'Address',
        'address2' => 'Address2',
        'postcode' => 'Postcode',
        'country' => 'Country',
        'county' => 'County',
        'phone' => 'Phone',
        'court_date' => '2015-12-31',
    ];

    private $updateDataPa = [
        'firstname' => 'f',
        'lastname' => 'l',
        'address' => 'a1',
        'address2' => 'a2',
        'postcode' => 'p',
        'county' => 'c',
        'phone' => 'p',
        'email' => 'e',
        'date_of_birth' => '1947-1-31',
    ];

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        // deputy 1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'deputy1Client1']);
        self::$report1 = self::fixtures()->createReport(self::$client1);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2, ['setFirstname' => 'deputy2Client1']);
        self::$report2 = self::fixtures()->createReport(self::$client2);

        // pa
        self::$pa1 = self::fixtures()->getRepo('User')->findOneByEmail('pa@example.org');
        self::$pa1Client1 = self::fixtures()->createClient(self::$pa1, ['setFirstname' => 'pa1Client1', 'setCaseNumber'=>'pa000001']);
        self::$pa1Client1Report1 = self::fixtures()->createReport(self::$pa1Client1);

        // prof
        self::$prof1 = self::fixtures()->getRepo('User')->findOneByEmail('prof@example.org');

        self::fixtures()->flush()->clear();
    }

    public function setUp()
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
            self::$tokenPa = $this->loginAsPa();
            self::$tokenProf = $this->loginAsProf();
        }
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function testupsertAuth()
    {
        $url = '/client/upsert';
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNeedsAuth('PUT', $url);

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    public function testupsertPostLayDeputy()
    {
        $url = '/client/upsert';

        $return = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => ['users' => [0 => self::$deputy1->getId()]] + $this->updateDataLay,
        ]);
        self::fixtures()->clear();

        $client = self::fixtures()->getRepo('Client')->find($return['data']['id']); /* @var $client \AppBundle\Entity\Client */
        $this->assertEquals('Firstname', $client->getFirstname());
        $this->assertCount(1, $client->getUsers());
        $this->assertEquals(self::$deputy1->getId(), $client->getUsers()->first()->getId());
    }

    public function testupsertPut_lay_deputy()
    {
        $url = '/client/upsert';

        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);

        // Lay deputy
        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => ['id' => self::$client1->getId()] + $this->updateDataLay,
        ]);
        self::fixtures()->clear();
        $client = self::fixtures()->getRepo('Client')->find($return['data']['id']); /* @var $client \AppBundle\Entity\Client */
        $this->assertEquals('Firstname', $client->getFirstname());
        $this->assertEquals('Lastname', $client->getLastname());
        $this->assertEquals('Address', $client->getAddress());
        $this->assertEquals('Address2', $client->getAddress2());
        $this->assertEquals('Postcode', $client->getPostcode());
        $this->assertEquals('County', $client->getCounty());
        $this->assertEquals('Phone', $client->getPhone());
        $this->assertEquals(null, $client->getDateOfBirth());
        $this->assertEquals('2015-12-31', $client->getCourtDate()->format('Y-m-d'));
        $this->assertEquals(self::$deputy1->getId(), $client->getUsers()->first()->getId());
    }

    public function testupsertPut_lay_deputy_ndr_enabled()
    {
        $url = '/client/upsert';

        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);

        // Lay deputy
        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => ['id' => self::$client1->getId()] + $this->updateDataLay,
        ]);
        self::fixtures()->clear();
        $client = self::fixtures()->getRepo('Client')->find($return['data']['id']); /* @var $client \AppBundle\Entity\Client */
        $this->assertInstanceOf(Ndr::class, $client->getNdr());
    }

    public function testupsertPut_PA()
    {
        $url = '/client/upsert';

        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);

        // PA
        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenPa,
            'data' => ['id' => self::$pa1Client1->getId()] + $this->updateDataPa,
        ]);
        self::fixtures()->clear();
        $client = self::fixtures()->getRepo('Client')->find($return['data']['id']); /* @var $client \AppBundle\Entity\Client */
        $this->assertEquals('f', $client->getFirstname());
        $this->assertEquals('l', $client->getLastname());
        $this->assertEquals('a1', $client->getAddress());
        $this->assertEquals('a2', $client->getAddress2());
        $this->assertEquals('p', $client->getPostcode());
        $this->assertEquals('c', $client->getCounty());
        $this->assertEquals('p', $client->getPhone());
        $this->assertEquals('1947-01-31', $client->getDateOfBirth()->format('Y-m-d'));
        $this->assertEquals('pa000001', $client->getCaseNumber()); //assert not changed
        $this->assertNull($client->getNdr());
    }

    public function testfindByIdAuth()
    {
        $url = '/client/' . self::$client1->getId();
        $this->assertEndpointNeedsAuth('GET', $url);

        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testfindByIdAcl()
    {
        $url2 = '/client/' . self::$client2->getId();

        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testfindById()
    {
        // Lay
        $url = '/client/' . self::$client1->getId();
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];
        $this->assertEquals(self::$client1->getId(), $data['id']);
        $this->assertEquals('Firstname', $data['firstname']);

        // PA
        $url = '/client/' . self::$pa1Client1->getId() . '?' . http_build_query(['groups' => ['client', 'report-id', 'current-report']]);
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenPa,
        ])['data'];
        $this->assertEquals(self::$pa1Client1->getId(), $data['id']);
        $this->assertEquals('f', $data['firstname']);
        $this->assertEquals(self::$pa1Client1Report1->getId(), $data['current_report']['id']);
    }

    public function testArchiveClientAuth()
    {
        $url = '/client/' . self::$pa1Client1->getId() . '/archive';

        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenDeputy);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    public function testArchiveClient()
    {
        $url = '/client/' . self::$pa1Client1->getId() . '/archive';
        $this->assertEquals(1, count(self::$pa1Client1->getUsers()));
        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenPa,
            'data' => [],
        ]);
        $client = self::fixtures()->clear()->getRepo('Client')->find($return['data']['id']);

        $this->assertInstanceOf('AppBundle\Entity\Client', $client);
        $this->assertEquals(1, count($client->getUsers()));
        $this->assertInstanceOf(\DateTime::class, $client->getArchivedAt());

    }

    public function testDetailsAction()
    {
        $url = '/client/' . self::$client1->getId() . '/details';

        $this->assertJsonRequest('GET', $url, [
            'mustFail' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertJsonRequest('GET', $url, [
            'mustFail' => true,
            'AuthToken' => self::$tokenPa,
        ])['data'];

        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ])['data'];

        $this->assertEquals('Firstname', $data['firstname']);
        $this->assertCount(1, $data['users']);
        $this->assertCount(1, $data['reports']);
    }

    public function testGetAllAction()
    {
        $url = '/client/get-all';

        $this->assertJsonRequest('GET', $url, [
            'mustFail' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertJsonRequest('GET', $url, [
            'mustFail' => true,
            'AuthToken' => self::$tokenPa,
        ])['data'];

        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ])['data'];

        $this->assertCount(9, $data);
    }
}
