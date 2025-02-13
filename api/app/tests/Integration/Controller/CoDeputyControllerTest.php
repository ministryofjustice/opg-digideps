<?php

namespace App\Tests\Integration\Controller;

class CoDeputyControllerTest extends AbstractTestController
{
    private static $tokenAdmin;
    private static $tokenCoDeputy;
    private static $coDeputy;
    private static $coDeputyClient;

    public function setUp(): void
    {
        parent::setUp();

        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenCoDeputy = $this->loginAsCoDeputy();
        }

        self::$coDeputy = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$coDeputyClient = self::fixtures()->createCoDeputyClient([self::$coDeputy], ['setFirstname' => 'coDeputyClient']);

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

    public function testAdd()
    {
        self::$tokenCoDeputy = $this->loginAsCoDeputy();
        $clientId = self::$coDeputyClient->getId();

        $return = $this->assertJsonRequest('POST', sprintf('/codeputy/add/%s', $clientId), [
            'data' => [
                'email' => 'janesmith@example.org',
                'firstname' => 'Jane',
                'lastname' => 'Smith',
            ],
            'mustSucceed' => true,
            'AuthToken' => self::$tokenCoDeputy,
        ]);

        $coDeputy = $this->fixtures()->clear()->getRepo('User')->find($return['data']['id']);

        $this->assertEquals('Jane', $coDeputy->getFirstname());
        $this->assertEquals('Smith', $coDeputy->getLastname());
        $this->assertEquals('janesmith@example.org', $coDeputy->getEmail());
        $this->assertEquals('CO_DEPUTY_INVITE', $coDeputy->getRegistrationRoute());
    }
}
