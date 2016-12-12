<?php

namespace Tests\AppBundle\Controller;

class CourtOrderTypeControllerTest extends AbstractTestController
{
    private static $tokenAdmin = null;
    private static $tokenDeputy = null;

    public function setUp()
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }
    }

    public function testgetAllCourtOrderTypeActionAuth()
    {
        $url = '/court-order-type';

        $this->assertEndpointNeedsAuth('GET', $url);
    }

    public function testgetAllCourtOrderTypeAction()
    {
        $url = '/court-order-type';

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
                'mustSucceed' => true,
                'AuthToken' => self::$tokenDeputy,
            ])['data'];

        $this->assertEquals(1, $data['court_order_types'][0]['id']);
        $this->assertEquals('Personal Welfare', $data['court_order_types'][0]['name']);

        $this->assertEquals(2, $data['court_order_types'][1]['id']);
        $this->assertEquals('Property and Affairs', $data['court_order_types'][1]['name']);
    }
}
