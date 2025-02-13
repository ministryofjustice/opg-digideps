<?php

namespace App\Tests\Unit\Controller\Ndr;

use App\Entity\Ndr\VisitsCare;
use app\tests\Integration\Controller\AbstractTestController;

class VisitsCareControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $ndr1;
    private static $visitsCare1;
    private static $deputy2;
    private static $client2;
    private static $ndr2;
    private static $visitsCare2;
    private static $tokenAdmin;
    private static $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }

        // deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);
        self::$ndr1 = self::fixtures()->createNdr(self::$client1);
        self::$visitsCare1 = self::fixtures()->createNdrVisitsCare(self::$ndr1, ['setDoYouLiveWithClient' => 'y']);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2);
        self::$ndr2 = self::fixtures()->createNdr(self::$client2);
        self::$visitsCare2 = self::fixtures()->createNdrVisitsCare(self::$ndr2);

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

    private $dataUpdate = [
        'do_you_live_with_client' => 'y-m',
        'how_often_do_you_visit' => 'ho-m',
        'how_often_do_you_contact_client' => 'hodycc',
        'plan_move_new_residence' => 'yes',
        'plan_move_new_residence_details' => "Toscany\nItaly",
    ];

    public function testGetOneByIdAuth()
    {
        $url = '/ndr/visits-care/'.self::$visitsCare1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testGetOneByIdAcl()
    {
        $url2 = '/ndr/visits-care/'.self::$visitsCare2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testGetOneByIdData()
    {
        $url = '/ndr/visits-care/'.self::$visitsCare1->getId();

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
    //        $url = '/ndr/'.self::$ndr1->getId().'/visits-care';
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
    //        $url2 = '/ndr/'.self::$ndr2->getId().'/visits-care';
    //
    //        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    //    }
    //
    //    /**
    //     * @depends testGetAcl
    //     */
    //    public function testGet()
    //    {
    //        $url = '/ndr/'.self::$ndr1->getId().'/visits-care';
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
        $url = '/ndr/visits-care';
        $url2 = '/ndr/visits-care/'.self::$visitsCare1->getId();
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
        $url2post = '/ndr/visits-care';
        $url2put = '/ndr/visits-care/'.self::$visitsCare2->getId();

        $this->assertEndpointNotAllowedFor('POST', $url2post, self::$tokenDeputy, [
            'ndr_id' => ['id' => self::$ndr2->getId()],
        ]);
        $this->assertEndpointNotAllowedFor('PUT', $url2put, self::$tokenDeputy);
    }

    /**
     * @depends testAddUpdateAcl
     */
    public function testUpdate()
    {
        $url = '/ndr/visits-care/'.self::$visitsCare1->getId();

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
        $visitsCare = self::fixtures()->getRepo('Ndr\VisitsCare')->find($return['data']['id']); /* @var $visitsCare \App\Entity\Ndr\VisitsCare */
        $this->assertEquals('y-m', $visitsCare->getDoYouLiveWithClient());
        $this->assertEquals('hodycc', $visitsCare->getHowOftenDoYouContactClient());
        $this->assertEquals('yes', $visitsCare->getPlanMoveNewResidence());
        $this->assertEquals("Toscany\nItaly", $visitsCare->getPlanMoveNewResidenceDetails());
        $this->assertEquals(self::$ndr1->getId(), $visitsCare->getNdr()->getId());
        // TODO assert other fields
    }

    /**
     * @depends testAddUpdateAcl
     */
    public function testDeleteAuth()
    {
        $url = '/ndr/visits-care/'.self::$visitsCare1->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);
    }

    /**
     * @depends testDeleteAuth
     */
    public function testDeleteAcl()
    {
        $url2 = '/ndr/visits-care/'.self::$visitsCare2->getId();

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
        $url = '/ndr/visits-care/'.$id;

        $data = $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $this->assertTrue(null === self::fixtures()->clear()->getRepo('Report\VisitsCare')->find($id));
    }

    public function testAdd()
    {
        $id = self::$visitsCare1->getId();
        $url = '/ndr/visits-care/'.$id;

        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $url = '/ndr/visits-care';

        $return = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => ['ndr_id' => self::$ndr1->getId()] + $this->dataUpdate,
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        // assert account created with transactions
        $visitsCare = self::fixtures()->getRepo('Ndr\VisitsCare')->find($return['data']['id']); /* @var $visitsCare VisitsCare */
        $this->assertEquals('y-m', $visitsCare->getDoYouLiveWithClient());
        $this->assertEquals(self::$ndr1->getId(), $visitsCare->getNdr()->getId());
        // TODO assert other fields
    }
}
