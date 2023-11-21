<?php

namespace App\Tests\Unit\Controller;

use App\Entity\Client;
use App\Entity\Ndr\Ndr;

class ClientControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $report1;
    private static $deputy2;
    private static $client2;
    private static $report2;

    private static $tokenAdmin;
    private static $tokenDeputy;
    private static $tokenPa;
    private static $tokenProf;

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
        'address3' => 'Address3',
        'address4' => 'Address4',
        'address5' => 'Address5',
        'postcode' => 'Postcode',
        'country' => 'Country',
        'phone' => 'Phone',
        'court_date' => '2015-12-31',
    ];

    private $updateDataPa = [
        'firstname' => 'f',
        'lastname' => 'l',
        'address' => 'a1',
        'address2' => 'a2',
        'address3' => 'a3',
        'address4' => 'a4',
        'address5' => 'a5',
        'postcode' => 'p',
        'phone' => 'p',
        'email' => 'e',
        'date_of_birth' => '1947-1-31',
    ];

    public function setUp(): void
    {
        parent::setUp();

        self::$fixtures::deleteReportsData(['client']);

        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
            self::$tokenPa = $this->loginAsPa();
            self::$tokenProf = $this->loginAsProf();
        }

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
        self::$pa1Client1 = self::fixtures()->createClient(self::$pa1, ['setFirstname' => 'pa1Client1', 'setCaseNumber' => 'pa000001']);
        self::$pa1Client1Report1 = self::fixtures()->createReport(self::$pa1Client1);

        // prof
        self::$prof1 = self::fixtures()->getRepo('User')->findOneByEmail('prof@example.org');

        $org = self::fixtures()->createOrganisation('Example', rand(1, 999999).'example.org', true);
        self::fixtures()->flush();
        self::fixtures()->addClientToOrganisation(self::$pa1Client1->getId(), $org->getId());
        self::fixtures()->addUserToOrganisation(self::$pa1->getId(), $org->getId());

        self::fixtures()->flush()->clear();
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function testUpsertAuth()
    {
        $url = '/client/upsert';
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNeedsAuth('PUT', $url);

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    public function testUpsertPostLayDeputy()
    {
        $url = '/client/upsert';

        $return = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => ['users' => [0 => self::$deputy1->getId()]] + $this->updateDataLay,
        ]);
        self::fixtures()->clear();

        $client = self::fixtures()->getRepo('Client')->find($return['data']['id']); /* @var $client Client */
        $this->assertEquals('Firstname', $client->getFirstname());
        $this->assertCount(1, $client->getUsers());
        $this->assertEquals(self::$deputy1->getId(), $client->getUsers()->first()->getId());
    }

    public function testUpsertPutLayDeputy()
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
        $client = self::fixtures()->getRepo('Client')->find($return['data']['id']); /* @var $client Client */
        $this->assertEquals('Firstname', $client->getFirstname());
        $this->assertEquals('Lastname', $client->getLastname());
        $this->assertEquals('Address', $client->getAddress());
        $this->assertEquals('Address2', $client->getAddress2());
        $this->assertEquals('Postcode', $client->getPostcode());
        $this->assertEquals('Address3', $client->getAddress3());
        $this->assertEquals('Address4', $client->getAddress4());
        $this->assertEquals('Address5', $client->getAddress5());
        $this->assertEquals('Phone', $client->getPhone());
        $this->assertEquals(null, $client->getDateOfBirth());
        $this->assertEquals('2015-12-31', $client->getCourtDate()->format('Y-m-d'));
        $this->assertEquals(self::$deputy1->getId(), $client->getUsers()->first()->getId());
    }

    public function testUpsertPutLayDeputyNDREnabled()
    {
        $url = '/client/upsert';

        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);

        // Lay deputy
        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => ['id' => self::$client1->getId(), 'ndr_enabled' => true] + $this->updateDataLay,
        ]);
        self::fixtures()->clear();
        $client = self::fixtures()->getRepo('Client')->find($return['data']['id']); /* @var $client Client */
        $this->assertInstanceOf(Ndr::class, $client->getNdr());
    }

    public function testUpsertPutPA()
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
        $client = self::fixtures()->getRepo('Client')->find($return['data']['id']); /* @var $client Client */
        $this->assertEquals('f', $client->getFirstname());
        $this->assertEquals('l', $client->getLastname());
        $this->assertEquals('a1', $client->getAddress());
        $this->assertEquals('a2', $client->getAddress2());
        $this->assertEquals('a3', $client->getAddress3());
        $this->assertEquals('a4', $client->getAddress4());
        $this->assertEquals('a5', $client->getAddress5());
        $this->assertEquals('p', $client->getPostcode());
        $this->assertEquals('p', $client->getPhone());
        $this->assertEquals('1947-01-31', $client->getDateOfBirth()->format('Y-m-d'));
        $this->assertEquals('pa000001', $client->getCaseNumber()); // assert not changed
        $this->assertNull($client->getNdr());
    }

    public function testfindByIdAuth()
    {
        $url = '/client/'.self::$client1->getId();
        $this->assertEndpointNeedsAuth('GET', $url);
    }

    public function testfindByIdAcl()
    {
        $url2 = '/client/'.self::$client2->getId();

        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testfindById()
    {
        // Lay
        $url = '/client/'.self::$client1->getId();
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];
        $this->assertEquals(self::$client1->getId(), $data['id']);
        $this->assertEquals('deputy1Client1', $data['firstname']);

        // PA
        $url = '/client/'.self::$pa1Client1->getId().'?'.http_build_query(['groups' => ['client', 'report-id', 'current-report']]);
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenPa,
        ])['data'];
        $this->assertEquals(self::$pa1Client1->getId(), $data['id']);
        $this->assertEquals('pa1Client1', $data['firstname']);
        $this->assertEquals(self::$pa1Client1Report1->getId(), $data['current_report']['id']);
    }

    public function testArchiveClientAuth()
    {
        $url = '/client/'.self::$pa1Client1->getId().'/archive';

        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenDeputy);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    public function testArchiveClient()
    {
        $url = '/client/'.self::$pa1Client1->getId().'/archive';
        $this->assertEquals(1, count(self::$pa1Client1->getUsers()));
        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenPa,
            'data' => [],
        ]);
        $client = self::fixtures()->clear()->getRepo('Client')->find($return['data']['id']);

        $this->assertInstanceOf('App\Entity\Client', $client);
        $this->assertEquals(1, count($client->getUsers()));
        $this->assertInstanceOf(\DateTime::class, $client->getArchivedAt());
    }

    public function testDetailsAction()
    {
        $url = '/client/'.self::$client1->getId().'/details';

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

        $this->assertEquals('deputy1Client1', $data['firstname']);
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

        $this->assertCount(3, $data);
    }
}
