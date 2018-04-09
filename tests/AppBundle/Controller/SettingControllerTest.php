<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Note;
use AppBundle\Entity\Setting;
use Symfony\Bridge\Doctrine\Tests\Fixtures\User;
use Fixtures;

class SettingTest extends AbstractTestController
{
    // users
    private static $tokenDeputy;
    private static $tokenAdmin;
    private static $tokenPa;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

//        //deputy1
//        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
//        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);
//
//        // pa 1
//        self::$pa1 = self::fixtures()->getRepo('User')->findOneByEmail('pa@example.org');
//        self::$pa1Client1 = self::fixtures()->createClient(self::$pa1, ['setFirstname' => 'pa1Client1']);
//        self::$pa1Client1Note1 = self::fixtures()->createNote(self::$pa1Client1, self::$pa1, 'cat', 'title', 'content');
//        self::$pa1Client2 = self::fixtures()->createClient(self::$pa1, ['setFirstname' => 'pa1Client2']);
//        // pa2 (same team as pa1)
//        self::$pa2 = self::fixtures()->getRepo('User')->findOneByEmail('pa_admin@example.org')->addClient(self::$pa1Client1);
//
//        // pa 3 with other client (other team)
//        self::$pa3 = self::fixtures()->getRepo('User')->findOneByEmail('pa_team_member@example.org');
//        self::$pa3Client1 = self::fixtures()->createClient(self::$pa3, ['setFirstname' => 'pa2Client1']);

//        self::fixtures()->flush()->clear();
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
        }
    }


    public function testgetOneById()
    {
        $id = 'service-notification';
        $url = '/setting/' . $id;
        Fixtures::deleteReportsData(['setting']);

        $setting = new Setting($id, 'snc', true);
        self::fixtures()->persist($setting)->flush();

        // assert Auth and ACL
        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenDeputy);
        $this->assertEndpointAllowedFor('GET', $url, self::$tokenAdmin);

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenAdmin,
        ])['data'];

        $this->assertEquals($id, $data['id']);
        $this->assertEquals('snc', $data['content']);
        $this->assertEquals(true, $data['enabled']);
    }


    public function testupdate()
    {
        $id = 'service-notification';
        $url = '/setting/' . $id;
        Fixtures::deleteReportsData(['setting']);

        // assert Auth
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenDeputy);

        // assert PUT (first time / add)
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenAdmin,
            'data'        => [
                'content' => 'snc1',
                'enabled' => true,
            ],
        ]);
        $setting = self::fixtures()->clear()->getRepo('Setting')->find($id); /* @var $setting Setting */
        $this->assertEquals($id, $setting->getId());
        $this->assertEquals('snc1', $setting->getContent());
        $this->assertEquals(true, $setting->isEnabled());

        // assert PUT (first time / add)
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenAdmin,
            'data'        => [
                'content' => 'snc2',
                'enabled' => false,
            ],
        ]);
        $setting = self::fixtures()->clear()->getRepo('Setting')->find($id); /* @var $setting Setting */
        $this->assertEquals($id, $setting->getId());
        $this->assertEquals('snc2', $setting->getContent());
        $this->assertEquals(false, $setting->isEnabled());

    }

}
