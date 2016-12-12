<?php

namespace Tests\AppBundle\Controller;

class RoleControllerTest extends AbstractTestController
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
        $url = '/role';

        $this->assertEndpointNeedsAuth('GET', $url);
    }

    public function testgetAllCourtOrderTypeAction()
    {
        $url = '/role';

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertEquals([
            'id' => 1,
            'name' => 'OPG Administrator',
            'role' => 'ROLE_ADMIN',
        ], $data[0]);

        $this->assertEquals([
            'id' => 2,
            'name' => 'Lay Deputy',
            'role' => 'ROLE_LAY_DEPUTY',
        ], $data[1]);
    }
}
