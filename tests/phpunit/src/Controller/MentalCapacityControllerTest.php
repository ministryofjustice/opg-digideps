<?php

namespace AppBundle\Controller;

use AppBundle\Entity\MentalCapacity;

class MentalCapacityControllerTest extends AbstractTestController
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

        //deputy1
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

    public function testupdateAuth()
    {
        $url = '/report/'.self::$report1->getId().'/mental-capacity';
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    public function testupdateAcl()
    {
        $url2 = '/report/'.self::$report2->getId().'/mental-capacity';

        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);
    }


    public function testupdate()
    {
        $url = '/report/'.self::$report1->getId().'/mental-capacity';

        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'has_capacity_changed' => MentalCapacity::CAPACITY_CHANGED,
                'has_capacity_changed_details' => 'ccd',
            ],
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        $mc = self::fixtures()->getRepo('MentalCapacity')->find($return['data']['id']); /* @var $mc \AppBundle\Entity\MentalCapacity */
        $this->assertEquals(MentalCapacity::CAPACITY_CHANGED, $mc->getHasCapacityChanged());
        $this->assertEquals('ccd', $mc->getHasCapacityChangedDetails());
        
        
        // update with choice not requiring details. (covers record existing and also data cleaned up ok)
        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'has_capacity_changed' => MentalCapacity::CAPACITY_STAYED_SAME,
                'has_capacity_changed_details' => 'should no tbe saved',
            ],
        ]);
        $this->assertTrue($return['data']['id'] > 0);
        self::fixtures()->clear();
        $mc = self::fixtures()->getRepo('MentalCapacity')->find($return['data']['id']); /* @var $mc \AppBundle\Entity\MentalCapacity */
        $this->assertEquals(MentalCapacity::CAPACITY_STAYED_SAME, $mc->getHasCapacityChanged());
        $this->assertEquals(null, $mc->getHasCapacityChangedDetails());
        
    }

}
