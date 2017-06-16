<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Note;
use Symfony\Component\Validator\Constraints\DateTime;
use Tests\AppBundle\Controller\AbstractTestController;

class NoteControllerTest extends AbstractTestController
{

    // users
    private static $tokenDeputy;
    private static $tokenAdmin;
    private static $tokenPa;
    private static $tokenPaAdmin;

    // lay
    private static $deputy1;
    private static $client1;

    // pa
    private static $pa1;
    private static $pa1Client1;
    private static $pa1Client1Note1;
    private static $pa1Client2;
    private static $pa3Admin;
    private static $pa3Client1;


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        //deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);

        // pa 1
        self::$pa1 = self::fixtures()->getRepo('User')->findOneByEmail('pa@example.org');
        self::$pa1Client1 = self::fixtures()->createClient(self::$pa1, ['setFirstname' => 'pa1Client1']);
        self::$pa1Client1Note1 = new Note(self::$pa1Client1, 'cat', 'title', 'content');
        self::$pa1Client2 = self::fixtures()->createClient(self::$pa1, ['setFirstname' => 'pa1Client2']);

        // pa 3 (other team)
        self::$pa3Admin = self::fixtures()->getRepo('User')->findOneByEmail('pa_admin@example.org');
        self::$pa3Client1 = self::fixtures()->createClient(self::$pa3Admin, ['setFirstname' => 'pa2Client1']);

        self::fixtures()->persist(self::$pa1Client1Note1)->flush()->clear();
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
            self::$tokenDeputy = $this->loginAsDeputy();
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenPa = $this->loginAsPa();
            self::$tokenPaAdmin = $this->loginAsPaAdmin();
        }
    }

    public function testAdd()
    {
        $this->markTestIncomplete('needs endpoint to rely on client Id first');
    }


    public function testgetOneById()
    {
        $url = '/note/' . self::$pa1Client1Note1->getId();

        // assert Auth
        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
        // assert ACL
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenPaAdmin);

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenPa,
        ])['data'];

        $this->assertEquals(self::$pa1Client1Note1->getId(), $data['id']);
        $this->assertEquals('cat', $data['category']);
        $this->assertEquals('title', $data['title']);
        $this->assertEquals('content', $data['content']);
        // TODO check created_by, using ID created from add test
        //$this->assertEquals(self::$pa1->getId(), $data['created_by']['id']);
        $this->assertEquals(true, -time() - strtotime($data['created_on']) < 3600);
    }

    public function testupdateNote()
    {
        $url = '/note/' . self::$pa1Client1Note1->getId();

        // assert Auth
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
        // assert ACL
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenPaAdmin);

        // assert PUT
        $data = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenPa,
            'data'        => [
                'category'     => 'cat-edited',
                'title'   => 'title-edited',
                'content' => 'content-edited',
            ],
        ])['data'];

        $this->assertEquals(self::$pa1Client1Note1->getId(), $data['id']);
        $this->assertEquals('cat-edited', $data['category']);
        $this->assertEquals('title-edited', $data['title']);
        $this->assertEquals('content-edited', $data['content']);
        $this->assertEquals(true, -time() - strtotime($data['created_on']) < 3600);

        //assert cannot change others' notes
        // assert PUT
        $data = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => false,
            'AuthToken'   => self::$tokenPaAdmin,
            'data'        => [
                'category'     => 'cat-edited2',
                'title'   => 'title-edited2',
                'content' => 'content-edited2',
            ],
        ])['data'];
    }

}
