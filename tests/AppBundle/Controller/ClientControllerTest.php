<?php

namespace Tests\AppBundle\Controller;

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

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        // deputy 1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);
        self::$report1 = self::fixtures()->createReport(self::$client1);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2);
        self::$report2 = self::fixtures()->createReport(self::$client2);

        self::fixtures()->flush()->clear();
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function setUp()
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }
    }

    public function testupsertAuth()
    {
        $url = '/client/upsert';
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNeedsAuth('PUT', $url);

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    public function testupsertAcl()
    {
        $url = '/client/upsert';
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenDeputy, [
            'users' => [0 => self::$deputy2->getId()],
        ]);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenDeputy, [
            'id' => self::$client2->getId(),
        ]);
    }

    private $updateData = [
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

    public function testupsertPost()
    {
        $url = '/client/upsert';

        $return = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => ['users' => [0 => self::$deputy1->getId()]] + $this->updateData,
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        // assert account created with transactions
        $client = self::fixtures()->getRepo('Client')->find($return['data']['id']); /* @var $client \AppBundle\Entity\Client */
        $this->assertEquals('Firstname', $client->getFirstname());
        $this->assertEquals(self::$deputy1->getId(), $client->getUsers()->first()->getId());
        $this->assertInstanceOf('AppBundle\Entity\Odr\Odr', $client->getOdr());
    }

    public function testupsertPut()
    {
        $url = '/client/upsert';

        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => ['id' => self::$client1->getId()] + $this->updateData,
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        // assert account created with transactions
        $client = self::fixtures()->getRepo('Client')->find($return['data']['id']); /* @var $client \AppBundle\Entity\Client */
        $this->assertEquals('Firstname', $client->getFirstname());
        $this->assertEquals(self::$deputy1->getId(), $client->getUsers()->first()->getId());
        // TODO assert other fields
    }

    public function testfindByIdAuth()
    {
        $url = '/client/'.self::$client1->getId();
        $this->assertEndpointNeedsAuth('GET', $url);

        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testfindByIdAcl()
    {
        $url2 = '/client/'.self::$client2->getId();

        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    /**
     * @depends testupsertPost
     * @depends testupsertPut
     */
    public function testfindById()
    {
        $url = '/client/'.self::$client1->getId();

          // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];
        $this->assertEquals(self::$client1->getId(), $data['id']);
        $this->assertEquals('Firstname', $data['firstname']);
    }
}
