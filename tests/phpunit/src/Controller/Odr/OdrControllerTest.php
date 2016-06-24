<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Controller\AbstractTestController;
use AppBundle\Entity\Odr\VisitsCare;

class OdrControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $odr1;
    private static $deputy2;
    private static $client2;
    private static $odr2;
    private static $tokenAdmin = null;
    private static $tokenDeputy = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        //deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);
        self::$odr1 = self::fixtures()->createOdr(self::$client1);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2);
        self::$odr2 = self::fixtures()->createOdr(self::$client2);

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

    public function testGetOneByIdAuth()
    {
        $url = '/odr/'.self::$odr1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testGetOneByIdAcl()
    {
        $url2 = '/odr/'.self::$odr2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }


    public function testGetOneByIdData()
    {
        $url = '/odr/'.self::$odr1->getId();

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
                'mustSucceed' => true,
                'AuthToken' => self::$tokenDeputy,
            ])['data'];

        $this->assertEquals(self::$odr1->getId(), $data['id']);
    }

}
