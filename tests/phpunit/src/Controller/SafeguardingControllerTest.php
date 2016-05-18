<?php

namespace AppBundle\Controller;

class SafeguardingControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $report1;
    private static $safeguarding1;
    private static $deputy2;
    private static $client2;
    private static $report2;
    private static $safeguarding2;
    private static $tokenAdmin = null;
    private static $tokenDeputy = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        //deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);
        self::$report1 = self::fixtures()->createReport(self::$client1);
        self::$safeguarding1 = self::fixtures()->createSafeguarding(self::$report1, ['setDoYouLiveWithClient' => 'y']);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2);
        self::$report2 = self::fixtures()->createReport(self::$client2);
        self::$safeguarding2 = self::fixtures()->createSafeguarding(self::$report2);

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

    private $dataUpdate = [
        'do_you_live_with_client' => 'y-m',
        'how_often_do_you_visit' => 'ho-m',
        'how_often_do_you_contact_client' => 'hodycc',
    ];

    public function setUp()
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }
    }

    public function testgetOneByIdAuth()
    {
        $url = '/report/safeguarding/'.self::$safeguarding1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    /**
     * @depends testgetOneByIdAuth
     */
    public function testgetOneByIdAcl()
    {
        $url2 = '/report/safeguarding/'.self::$safeguarding2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    /**
     * @depends testgetOneByIdAcl
     */
    public function testgetOneById()
    {
        $url = '/report/safeguarding/'.self::$safeguarding1->getId();

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
                'mustSucceed' => true,
                'AuthToken' => self::$tokenDeputy,
            ])['data'];

        $this->assertEquals(self::$safeguarding1->getId(), $data['id']);
        $this->assertEquals(self::$safeguarding1->getDoYouLiveWithClient(), $data['do_you_live_with_client']);
    }

    /**
     * @depends testgetOneById
     */
    public function testgetSafeguardingsAuth()
    {
        $url = '/report/'.self::$report1->getId().'/safeguardings';

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    /**
     * @depends testgetSafeguardingsAuth
     */
    public function testgetSafeguardingsAcl()
    {
        $url2 = '/report/'.self::$report2->getId().'/safeguardings';

        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    /**
     * @depends testgetSafeguardingsAcl
     */
    public function testgetSafeguardings()
    {
        $url = '/report/'.self::$report1->getId().'/safeguardings';

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
                'mustSucceed' => true,
                'AuthToken' => self::$tokenDeputy,
            ])['data'];

        $this->assertCount(1, $data);
        $this->assertEquals(self::$safeguarding1->getId(), $data[0]['id']);
        $this->assertEquals(self::$safeguarding1->getDoYouLiveWithClient(), $data[0]['do_you_live_with_client']);
    }

    /**
     * @depends testgetSafeguardings
     */
    public function testAddUpdateAuth()
    {
        $url = '/report/safeguarding';
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    /**
     * @depends testAddUpdateAuth
     */
    public function testAddUpdateAcl()
    {
        $url2post = '/report/safeguarding';
        $url2put = '/report/safeguarding/'.self::$safeguarding2->getId();

        $this->assertEndpointNotAllowedFor('POST', $url2post, self::$tokenDeputy, [
            'report_id' => ['id' => self::$report2->getId()],
        ]);
        $this->assertEndpointNotAllowedFor('PUT', $url2put, self::$tokenDeputy);
    }

    /**
     * @depends testAddUpdateAcl
     */
    public function testUpdate()
    {
        $url = '/report/safeguarding/'.self::$safeguarding1->getId();

        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => $this->dataUpdate,
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        $safeguarding = self::fixtures()->getRepo('Safeguarding')->find($return['data']['id']); /* @var $safeguarding \AppBundle\Entity\Safeguarding */
        $this->assertEquals('y-m', $safeguarding->getDoYouLiveWithClient());
        $this->assertEquals('hodycc', $safeguarding->getHowOftenDoYouContactClient());
        $this->assertEquals(self::$report1->getId(), $safeguarding->getReport()->getId());
        // TODO assert other fields
    }

    /**
     * @depends testAddUpdateAcl
     */
    public function testDeleteSafeguardingAuth()
    {
        $url = '/report/safeguarding/'.self::$safeguarding1->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);
    }

    /**
     * @depends testDeleteSafeguardingAuth
     */
    public function testDeleteSafeguardingAcl()
    {
        $url2 = '/report/safeguarding/'.self::$safeguarding2->getId();

        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);
    }

    /**
     * Run this last to avoid corrupting the data.
     * 
     * @depends testDeleteSafeguardingAcl
     */
    public function testDeleteSafeguarding()
    {
        $id = self::$safeguarding1->getId();
        $url = '/report/safeguarding/'.$id;

        $data = $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $this->assertTrue(null === self::fixtures()->clear()->getRepo('Safeguarding')->find($id));
    }

    /**
     * need the record to be deleted first.
     * 
     * @depends testDeleteSafeguarding
     */
    public function testAdd()
    {
        $url = '/report/safeguarding';

        $return = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => ['report_id' => self::$report1->getId()] + $this->dataUpdate,
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        // assert account created with transactions
        $safeguarding = self::fixtures()->getRepo('Safeguarding')->find($return['data']['id']); /* @var $safeguarding \AppBundle\Entity\Safeguarding */
        $this->assertEquals('y-m', $safeguarding->getDoYouLiveWithClient());
        $this->assertEquals(self::$report1->getId(), $safeguarding->getReport()->getId());
        // TODO assert other fields
    }
}
