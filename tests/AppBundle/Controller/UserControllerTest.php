<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;

class UserControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $admin1;
    private static $deputy2;
    private static $tokenAdmin = null;
    private static $tokenDeputy = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$admin1 = self::fixtures()->getRepo('User')->findOneByEmail('admin@example.org');
        self::$deputy2 = self::fixtures()->createUser();

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

    public function testAddAuth()
    {
        $url = '/user';

        $this->assertEndpointNeedsAuth('POST', $url);

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenDeputy);
    }

    public function testAddMissingParams()
    {
        $url = '/user';

        // empty params
        $errorMessage = $this->assertJsonRequest('POST', $url, [
            'data' => [
            ],
            'mustFail' => true,
            'AuthToken' => self::$tokenAdmin,
            'assertResponseCode' => 400,
        ])['message'];
        $this->assertContains('role_name', $errorMessage);
        $this->assertContains('email', $errorMessage);
        $this->assertContains('firstname', $errorMessage);
        $this->assertContains('lastname', $errorMessage);
    }

    public function testAdd()
    {
        $return = $this->assertJsonRequest('POST', '/user', [
            'data' => [
                'role_name' => User::ROLE_LAY_DEPUTY, //deputy role
                'firstname' => 'n',
                'lastname' => 's',
                'email' => 'n.s@example.org',
            ],
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ]);

        $user = $this->fixtures()->clear()->getRepo('User')->find($return['data']['id']);
        $this->assertEquals('n', $user->getFirstname());
        $this->assertEquals('s', $user->getLastname());
        $this->assertEquals('n.s@example.org', $user->getEmail());
    }

    public function testUpdateAuth()
    {
        $url = '/user/' . self::$deputy1->getId();

        $this->assertEndpointNeedsAuth('PUT', $url);
    }

    public function testUpdateAcl()
    {
        $url = '/user/' . self::$deputy1->getId();
        $url2 = '/user/' . self::$deputy2->getId();

        // deputy can only change their data
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);

        // admin can change any user
        $this->assertEndpointAllowedFor('PUT', $url, self::$tokenAdmin);
        $this->assertEndpointAllowedFor('PUT', $url2, self::$tokenAdmin);
    }

    public function testUpdate()
    {
        $deputyId = self::$deputy1->getId();
        $url = '/user/' . $deputyId;

        // assert get
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'lastname' => self::$deputy1->getLastname() . '-modified',
                'email' => self::$deputy1->getEmail() . '-modified',
                'address1' => self::$deputy1->getAddress1() . '-modified',
            ],
        ]);

        $user = self::fixtures()->clear()->getRepo('User')->find($deputyId); /* @var $user \AppBundle\Entity\User */

        $this->assertEquals(self::$deputy1->getLastname() . '-modified', $user->getLastname());
        $this->assertEquals(self::$deputy1->getEmail() . '-modified', $user->getEmail());
        $this->assertEquals(self::$deputy1->getAddress1() . '-modified', $user->getAddress1());

        // restore previous data
        $user->setLastname(str_replace('-modified', '', $user->getLastname()));
        $user->setEmail(str_replace('-modified', '', $user->getEmail()));
        $user->setAddress1(str_replace('-modified', '', $user->getAddress1()));

        self::fixtures()->flush($user);
    }

    public function testUpdatePa()
    {
        $this->markTestSkipped('pa team name disabled');

        $deputyId = self::$deputy1->getId();
        $url = '/user/' . $deputyId;

        $this->assertCount(0, self::$deputy1->getTeams());

        // assert get
        foreach (['pt.old', 'pt.new'] as $teamName) {
            $this->assertJsonRequest('PUT', $url, [
                'mustSucceed' => true,
                'AuthToken' => self::$tokenDeputy,
                'data' => [
                    'pa_team_name' => $teamName,
                ],
            ]);
        }

        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertEquals('pt.new', $data['pa_team_name']);
    }

    public function testIsPasswordCorrectAuth()
    {
        $url = '/user/' . self::$deputy2->getId() . '/is-password-correct';

        $this->assertEndpointNeedsAuth('POST', $url);
    }

    public function testIsPasswordCorrectAcl()
    {
        $url = '/user/' . self::$deputy2->getId() . '/is-password-correct';

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenDeputy);
    }

    public function testIsPasswordCorrect()
    {
        $url = '/user/' . self::$deputy1->getId() . '/is-password-correct';
        $this->assertEndpointNeedsAuth('POST', $url);
    }

    public function testChangePasswordAuth()
    {
        $url = '/user/' . self::$deputy1->getId() . '/set-password';

        $this->assertEndpointNeedsAuth('PUT', $url);
    }

    public function testChangePasswordAcl()
    {
        $url = '/user/' . self::$deputy2->getId() . '/set-password';

        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenDeputy);
    }

    public function testChangePasswordMissingParams()
    {
        $url = '/user/' . self::$deputy1->getId() . '/set-password';

        // empty params
        $errorMessage = $this->assertJsonRequest('PUT', $url, [
            'mustFail' => true,
            'AuthToken' => self::$tokenDeputy,
            'assertResponseCode' => 400,
        ])['message'];
        $this->assertContains('password_plain', $errorMessage);
    }

    public function testChangePasswordNoEmail()
    {
        $url = '/user/' . self::$deputy1->getId() . '/set-password';

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'password_plain' => 'Abcd1234ne',
            ],
        ]);

        $this->login('deputy@example.org', 'Abcd1234ne', '123abc-deputy');
    }

    /**
     * @depends testChangePasswordNoEmail
     */
    public function testChangePasswordEmailActivate()
    {
        $url = '/user/' . self::$deputy1->getId() . '/set-password';

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'password_plain' => 'Abcd1234pa',
                'send_email' => 'activate',

            ],
        ]);

        $this->login('deputy@example.org', 'Abcd1234pa', '123abc-deputy');
    }

    /**
     * @depends testChangePasswordEmailActivate
     */
    public function testChangePasswordEmailReset()
    {
        $url = '/user/' . self::$deputy1->getId() . '/set-password';

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'password_plain' => 'Abcd1234', //restore password for subsequent logins
                'send_email' => 'password-reset',
            ],
        ]);
    }

    public function testGetOneByIdAuth()
    {
        $url = '/user/' . self::$deputy1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);
    }

    public function testGetOneByIdAcl()
    {
        $url1 = '/user/' . self::$deputy1->getId();
        $url2 = '/user/' . self::$deputy2->getId();
        $url3 = '/user/' . self::$admin1->getId();

        // deputy can only see his data
        $this->assertEndpointAllowedFor('GET', $url1, self::$tokenDeputy);
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
        $this->assertEndpointNotAllowedFor('GET', $url3, self::$tokenDeputy);

        // admin can see all users
        $this->assertEndpointAllowedFor('GET', $url1, self::$tokenAdmin);
        $this->assertEndpointAllowedFor('GET', $url2, self::$tokenAdmin);
        $this->assertEndpointAllowedFor('GET', $url3, self::$tokenAdmin);
    }

    public function testGetOneById()
    {
        $url = '/user/' . self::$deputy1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);

        $return = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $this->assertEquals('deputy@example.org', $return['data']['email']);
    }

    public function testDeleteAuth()
    {
        $url = '/user/' . self::$deputy1->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
    }

    public function testDeleteAcl()
    {
        $url = '/user/' . self::$deputy1->getId();

        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenDeputy);
    }

    public function testDelete()
    {
        $deputy3 = self::fixtures()->createUser();
        self::fixtures()->flush();
        $userToDeleteId = $deputy3->getId();

        $url = '/user/' . $userToDeleteId;

        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ]);

        $this->assertTrue(null === self::fixtures()->clear()->getRepo('User')->find($userToDeleteId));
    }

    public function testGetAllAuth()
    {
        $url = '/user/get-all';

        $this->assertEndpointNeedsAuth('GET', $url);
    }

    public function testGetAllAcl()
    {
        $url = '/user/get-all';

        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenDeputy);
    }

    public function testGetAll()
    {
        $url = '/user/get-all';

        $return = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ]);

        $this->assertTrue(count($return['data']) > 2);
    }

    public function testRecreateTokenMissingClientSecret()
    {
        $url = '/user/recreate-token/mail@example.org/activate';

        // assert client token
        $this->assertJsonRequest('PUT', $url, [
            'mustFail' => true,
            'assertResponseCode' => 403,
        ]);
    }

    public static function recreateTokenProvider()
    {
        return [
            ['activate', 'activate your account'],
            ['pass-reset', 'reset your password'],
        ];
    }

    /**
     * @dataProvider recreateTokenProvider
     */
    public function testRecreateTokenWrongClientSecret($urlPart)
    {
        $this->assertJsonRequest('PUT', '/user/recreate-token/mail@example.org/' . $urlPart, [
            'mustFail' => true,
            'assertResponseCode' => 403,
            'ClientSecret' => 'WRONG-CLIENT_SECRET',
        ]);
    }

    /**
     * @dataProvider recreateTokenProvider
     */
    public function testRecreateTokenUserNotFound($urlPart)
    {
        $this->assertJsonRequest('PUT', '/user/recreate-token/WRONGUSER@example.org/' . $urlPart, [
            'mustFail' => true,
            'ClientSecret' => '123abc-deputy',
        ]);
    }

    /**
     * @dataProvider recreateTokenProvider
     */
    public function testRecreateTokenEmailActivate($urlPart, $emailSubject)
    {
        $url = '/user/recreate-token/deputy@example.org/' . $urlPart;

        $deputy = self::fixtures()->clear()->getRepo('User')->findOneByEmail('deputy@example.org');
        $deputy->setRegistrationToken(null);
        $deputy->setTokenDate(new \DateTime('2014-12-30'));
        self::fixtures()->flush($deputy);

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'ClientSecret' => '123abc-deputy',
        ]);

        // refresh deputy from db and chack token has been reset
        $deputyRefreshed = self::fixtures()->clear()->getRepo('User')->findOneByEmail('deputy@example.org');
        $this->assertTrue(strlen($deputyRefreshed->getRegistrationToken()) > 5);
        $this->assertEquals(0, $deputyRefreshed->getTokenDate()->diff(new \DateTime())->format('%a'));
    }

    public function testGetByToken()
    {
        $this->assertJsonRequest('GET', '/user/get-by-token/123abcd', [
            'mustFail' => true,
            'assertResponseCode' => 403,
        ]);

        $this->assertJsonRequest('GET', '/user/get-by-token/123abcd', [
            'mustFail' => true,
            'assertResponseCode' => 403,
            'ClientSecret' => 'WRONG-CLIENT_SECRET',
        ]);

        $deputy = self::fixtures()->clear()->getRepo('User')->findOneByEmail('deputy@example.org');
        $deputy->recreateRegistrationToken();
        self::fixtures()->flush($deputy);

        $url = '/user/get-by-token/' . $deputy->getRegistrationToken();

        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'ClientSecret' => '123abc-deputy',
        ])['data'];
        $this->assertEquals('deputy@example.org', $data['email']);
    }

    public function testAgreeTermsUSe()
    {
        // recreate reg token
        $deputy = self::fixtures()->clear()->getRepo('User')->findOneByEmail('deputy@example.org');
        $deputy->recreateRegistrationToken();
        self::fixtures()->flush($deputy);
        $url = '/user/agree-terms-use/' . $deputy->getRegistrationToken();

        $this->assertJsonRequest('PUT', $url, [
            'mustFail' => true,
            'assertResponseCode' => 403,
        ]);
        $this->assertJsonRequest('PUT', $url, [
            'mustFail' => true,
            'assertResponseCode' => 403,
            'ClientSecret' => 'WRONG-CLIENT_SECRET',
        ]);

        $data = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'ClientSecret' => '123abc-deputy',
        ])['data'];
        $this->assertEquals(true, $data['agree_terms_use']);
        $this->assertEquals(date('Y-m-d'), $data['agree_terms_use_date']);
    }
}
