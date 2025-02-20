<?php

namespace App\Controller;

use App\Tests\Integration\Controller\AbstractTestController;

class DeputyControllerTest extends AbstractTestController
{
    private static $tokenAdmin;
    private static $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }

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

    public function testAddAuth()
    {
        $url = '/deputy/add';

        $this->assertEndpointNeedsAuth('POST', $url);
    }

    public function testAdd()
    {
        self::$tokenDeputy = $this->loginAsDeputy();

        $return = $this->assertJsonRequest('POST', '/deputy/add', [
            'data' => [
                'firstname' => 'n',
                'lastname' => 's',
                'email' => 'n.s@example.org',
                'deputy_uid' => '7999999990',
            ],
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $deputy = $this->fixtures()->clear()->getRepo('Deputy')->find($return['data']['id']);

        $this->assertEquals('n', $deputy->getFirstname());
        $this->assertEquals('s', $deputy->getLastname());
        $this->assertEquals('n.s@example.org', $deputy->getEmail1());
        $this->assertEquals('7999999990', $deputy->getDeputyUid());
    }

    public function testAddDeputyIdAlreadyExists()
    {
        self::$tokenDeputy = $this->loginAsDeputy();

        $return = $this->assertJsonRequest('POST', '/deputy/add', [
            'data' => [
                'firstname' => 'd',
                'lastname' => 'e',
                'email' => 'd.e@example.org',
                'deputy_uid' => '7999999990',
            ],
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $deputy = $this->fixtures()->clear()->getRepo('Deputy')->find($return['data']['id']);

        $this->assertEquals('n', $deputy->getFirstname());
        $this->assertEquals('s', $deputy->getLastname());
        $this->assertEquals('n.s@example.org', $deputy->getEmail1());
        $this->assertEquals('7999999990', $deputy->getDeputyUid());
    }
}
