<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Controller\AbstractTestController;
use AppBundle\Entity\Odr\VisitsCare;

class VisitsCareControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $odr1;
    private static $visitsCare1;
    private static $deputy2;
    private static $client2;
    private static $odr2;
    private static $visitsCare2;
    private static $tokenAdmin = null;
    private static $tokenDeputy = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        //deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);
        self::$odr1 = self::fixtures()->createOdr(self::$client1);
        self::$visitsCare1 = self::fixtures()->createOdrVisitsCare(self::$odr1, ['setDoYouLiveWithClient' => 'y']);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2);
        self::$odr2 = self::fixtures()->createOdr(self::$client2);
        self::$visitsCare2 = self::fixtures()->createOdrVisitsCare(self::$odr2);

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
        'plan_move_new_residence' => 'yes',
        'plan_move_new_residence_details' => "Toscany\nItaly",
    ];

    public function setUp()
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }
    }

    public function testGetOneByIdAuth()
    {
        $url = '/odr/visits-care/'.self::$visitsCare1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testGetOneByIdAcl()
    {
        $url2 = '/odr/visits-care/'.self::$visitsCare2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }


    public function testGetOneByIdData()
    {
        $url = '/odr/visits-care/'.self::$visitsCare1->getId();

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
                'mustSucceed' => true,
                'AuthToken' => self::$tokenDeputy,
            ])['data'];

        $this->assertEquals(self::$visitsCare1->getId(), $data['id']);
        $this->assertEquals(self::$visitsCare1->getDoYouLiveWithClient(), $data['do_you_live_with_client']);
    }

//    /**
//     * @depends testGetOneByIdData
//     */
//    public function testGetAuth()
//    {
//        $url = '/odr/'.self::$odr1->getId().'/visits-care';
//
//        $this->assertEndpointNeedsAuth('GET', $url);
//        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
//    }
//
//    /**
//     * @depends testGetAuth
//     */
//    public function testGetAcl()
//    {
//        $url2 = '/odr/'.self::$odr2->getId().'/visits-care';
//
//        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
//    }
//
//    /**
//     * @depends testGetAcl
//     */
//    public function testGet()
//    {
//        $url = '/odr/'.self::$odr1->getId().'/visits-care';
//
//        // assert get
//        $data = $this->assertJsonRequest('GET', $url, [
//                'mustSucceed' => true,
//                'AuthToken' => self::$tokenDeputy,
//            ])['data'];
//
//        $this->assertCount(1, $data);
//        $this->assertEquals(self::$visitsCare1->getId(), $data[0]['id']);
//        $this->assertEquals(self::$visitsCare1->getDoYouLiveWithClient(), $data[0]['do_you_live_with_client']);
//    }

    public function testAddUpdateAuth()
    {
        $url = '/odr/visits-care';
        $url2 = '/odr/visits-care/'.self::$visitsCare1->getId();
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNeedsAuth('PUT', $url2);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenAdmin);
    }

    /**
     * @depends testAddUpdateAuth
     */
    public function testAddUpdateAcl()
    {
        $url2post = '/odr/visits-care';
        $url2put = '/odr/visits-care/'.self::$visitsCare2->getId();

        $this->assertEndpointNotAllowedFor('POST', $url2post, self::$tokenDeputy, [
            'odr_id' => ['id' => self::$odr2->getId()],
        ]);
        $this->assertEndpointNotAllowedFor('PUT', $url2put, self::$tokenDeputy);
    }

    /**
     * @depends testAddUpdateAcl
     */
    public function testUpdate()
    {
        $url = '/odr/visits-care/'.self::$visitsCare1->getId();

        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => $this->dataUpdate,
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        /**
         * @var $visitsCare VisitsCare
         */
        $visitsCare = self::fixtures()->getRepo('Odr\VisitsCare')->find($return['data']['id']); /* @var $visitsCare \AppBundle\Entity\Odr\VisitsCare */
        $this->assertEquals('y-m', $visitsCare->getDoYouLiveWithClient());
        $this->assertEquals('hodycc', $visitsCare->getHowOftenDoYouContactClient());
        $this->assertEquals('yes', $visitsCare->getPlanMoveNewResidence());
        $this->assertEquals("Toscany\nItaly", $visitsCare->getPlanMoveNewResidenceDetails());
        $this->assertEquals(self::$odr1->getId(), $visitsCare->getOdr()->getId());
        // TODO assert other fields
    }

    /**
     * @depends testAddUpdateAcl
     */
    public function testDeleteAuth()
    {
        $url = '/odr/visits-care/'.self::$visitsCare1->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);
    }

    /**
     * @depends testDeleteAuth
     */
    public function testDeleteAcl()
    {
        $url2 = '/odr/visits-care/'.self::$visitsCare2->getId();

        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);
    }

    /**
     * Run this last to avoid corrupting the data.
     *
     * @depends testDeleteAcl
     */
    public function testDelete()
    {
        $id = self::$visitsCare1->getId();
        $url = '/odr/visits-care/'.$id;

        $data = $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $this->assertTrue(null === self::fixtures()->clear()->getRepo('Report\VisitsCare')->find($id));
    }

    /**
     * need the record to be deleted first.
     *
     * @depends testDelete
     */
    public function testAdd()
    {
        $url = '/odr/visits-care';

        $return = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => ['odr_id' => self::$odr1->getId()] + $this->dataUpdate,
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        // assert account created with transactions
        $visitsCare = self::fixtures()->getRepo('Odr\VisitsCare')->find($return['data']['id']); /* @var $visitsCare VisitsCare */
        $this->assertEquals('y-m', $visitsCare->getDoYouLiveWithClient());
        $this->assertEquals(self::$odr1->getId(), $visitsCare->getOdr()->getId());
        // TODO assert other fields
    }
}
