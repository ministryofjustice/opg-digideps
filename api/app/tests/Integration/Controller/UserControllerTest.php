<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Entity\Role;
use App\Entity\User;

class UserControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $admin1;
    private static $deputy2;
    private static $primaryUserAccount;
    private static $nonPrimaryUserAccount;
    private static $tokenAdmin;
    private static $tokenSuperAdmin;
    private static $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        if (null === self::$tokenAdmin) {
            self::$tokenSuperAdmin = $this->loginAsSuperAdmin();
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }

        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$admin1 = self::fixtures()->getRepo('User')->findOneByEmail('admin@example.org');
        self::$deputy2 = self::fixtures()->createUser();
        self::$primaryUserAccount = self::fixtures()->getRepo('User')->findOneByEmail('multi-client-primary-deputy@example.org');
        self::$nonPrimaryUserAccount = self::fixtures()->getRepo('User')->findOneByEmail('multi-client-non-primary-deputy@example.org');

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
        $this->assertStringContainsString('role_name', $errorMessage);
        $this->assertStringContainsString('email', $errorMessage);
        $this->assertStringContainsString('firstname', $errorMessage);
        $this->assertStringContainsString('lastname', $errorMessage);
    }

    public function testAdd()
    {
        self::$tokenAdmin = $this->loginAsAdmin();

        $return = $this->assertJsonRequest('POST', '/user', [
            'data' => [
                'role_name' => User::ROLE_LAY_DEPUTY, // deputy role
                'firstname' => 'n',
                'lastname' => 's',
                'email' => 'n.s@example.org',
            ],
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ]);

        $user = $this->fixtures()->clear()->getRepo('User')->find($return['data']['id']);
        $loggedInUser = $this->fixtures()->clear()->getRepo('User')->find($this->loggedInUserId);

        $this->assertEquals('n', $user->getFirstname());
        $this->assertEquals('s', $user->getLastname());
        $this->assertEquals('n.s@example.org', $user->getEmail());
        $this->assertEquals($loggedInUser->getId(), $user->getCreatedBy()->getId(), sprintf('The User that created this user was not as expected. Wanted user with ID: %s, Got: %g', $this->loggedInUserId, $user->getCreatedBy() ? $user->getCreatedBy()->getId() : null));
        $this->assertEquals(User::ADMIN_INVITE, $user->getRegistrationRoute());
    }

    public function testUpdateAuth()
    {
        $url = '/user/'.self::$deputy1->getId();

        $this->assertEndpointNeedsAuth('PUT', $url);
    }

    public function testUpdateAcl()
    {
        $url = '/user/'.self::$deputy1->getId();
        $url2 = '/user/'.self::$deputy2->getId();

        // deputy can only change their data
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);

        // admin can change any user
        $this->assertEndpointAllowedFor('PUT', $url, self::$tokenAdmin);
        $this->assertEndpointAllowedFor('PUT', $url2, self::$tokenAdmin);
    }

    public function testUpdate()
    {
        $deputyId = self::$deputy1->getId();
        $url = '/user/'.$deputyId;

        // assert get
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'lastname' => self::$deputy1->getLastname().'-modified',
                'email' => self::$deputy1->getEmail().'-modified',
                'address1' => self::$deputy1->getAddress1().'-modified',
            ],
        ]);

        $user = self::fixtures()->clear()->getRepo('User')->find($deputyId); /* @var $user \App\Entity\User */

        $this->assertEquals(self::$deputy1->getLastname().'-modified', $user->getLastname());
        $this->assertEquals(self::$deputy1->getEmail().'-modified', $user->getEmail());
        $this->assertEquals(self::$deputy1->getAddress1().'-modified', $user->getAddress1());

        // restore previous data
        $user->setLastname(str_replace('-modified', '', $user->getLastname()));
        $user->setEmail(str_replace('-modified', '', $user->getEmail()));
        $user->setAddress1(str_replace('-modified', '', $user->getAddress1()));

        self::fixtures()->flush($user);
    }

    public function testUpdateNotPermittedToChangeType()
    {
        $deputyId = self::$deputy1->getId();
        $url = '/user/'.$deputyId;

        $output = $this->assertJsonRequest('PUT', $url, [
            'mustFail' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'lastname' => self::$deputy1->getLastname(),
                'email' => self::$deputy1->getEmail(),
                'address1' => self::$deputy1->getAddress1(),
                'role_name' => User::ROLE_ADMIN,
            ],
        ]);

        $this->assertEquals('Cannot change realm of user\'s role', $output['message']);
    }

    public function testIsPasswordCorrectAuth()
    {
        $url = '/user/'.self::$deputy2->getId().'/is-password-correct';

        $this->assertEndpointNeedsAuth('POST', $url);
    }

    public function testIsPasswordCorrectAcl()
    {
        $url = '/user/'.self::$deputy2->getId().'/is-password-correct';

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenDeputy);
    }

    public function testIsPasswordCorrect()
    {
        $url = '/user/'.self::$deputy1->getId().'/is-password-correct';
        $this->assertEndpointNeedsAuth('POST', $url);
    }

    public function testChangePasswordAuth()
    {
        $url = '/user/'.self::$deputy1->getId().'/set-password';

        $this->assertEndpointNeedsAuth('PUT', $url, ['password' => 'adfikhdbfsk']);
    }

    public function testChangePasswordAcl()
    {
        $url = '/user/'.self::$deputy2->getId().'/set-password';

        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenDeputy, ['password' => 'ashjbasdfjhb']);
    }

    public function testChangePasswordMissingParams()
    {
        $url = '/user/'.self::$deputy1->getId().'/set-password';

        // empty params
        $errorMessage = $this->assertJsonRequest('PUT', $url, [
            'mustFail' => true,
            'AuthToken' => self::$tokenDeputy,
            'assertResponseCode' => 400,
        ])['message'];
        $this->assertStringContainsString('password', $errorMessage);
    }

    public function testChangePasswordNoEmail()
    {
        $url = '/user/'.self::$deputy1->getId().'/set-password';

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'password' => 'DigidepsPass1234ne',
            ],
        ]);

        $this->login('deputy@example.org', 'DigidepsPass1234ne', API_TOKEN_DEPUTY);
    }

    /**
     * @depends testChangePasswordNoEmail
     */
    public function testChangePasswordEmailActivate()
    {
        $url = '/user/'.self::$deputy1->getId().'/set-password';

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'password' => 'DigidepsPass1234pa',
                'send_email' => 'activate',
            ],
        ]);

        $this->login('deputy@example.org', 'DigidepsPass1234pa', API_TOKEN_DEPUTY);
    }

    /**
     * @depends testChangePasswordEmailActivate
     */
    public function testChangePasswordEmailReset()
    {
        $url = '/user/'.self::$deputy1->getId().'/set-password';

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'password' => 'DigidepsPass1234', // restore password for subsequent logins
                'send_email' => 'password-reset',
            ],
        ]);
    }

    public function testChangeEmailAuth()
    {
        $url = '/user/'.self::$deputy1->getId().'/update-email';

        $this->assertEndpointNeedsAuth('PUT', $url);
    }

    public function testChangeEmailAcl()
    {
        $url = '/user/'.self::$deputy2->getId().'/update-email';

        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenDeputy);
    }

    public function testGetOneByIdAuth()
    {
        $url = '/user/'.self::$deputy1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);
    }

    public function testGetOneByIdAcl()
    {
        $url1 = '/user/'.self::$deputy1->getId();
        $url2 = '/user/'.self::$deputy2->getId();
        $url3 = '/user/'.self::$admin1->getId();

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
        $url = '/user/'.self::$deputy1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);

        $return = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $this->assertEquals('deputy@example.org', $return['data']['email']);
    }

    public function testDeleteAuth()
    {
        $url = '/user/'.self::$deputy1->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
    }

    public function testDeleteAcl()
    {
        $url = '/user/'.self::$deputy1->getId();

        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenDeputy);
    }

    public function testDeletePermittedForSuperAdmin()
    {
        $deputy3 = self::fixtures()->createUser();
        $deputy3->setRoleName(User::ROLE_LAY_DEPUTY);

        self::fixtures()->flush();
        $userToDeleteId = $deputy3->getId();

        $url = '/user/'.$userToDeleteId;

        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenSuperAdmin,
        ]);

        $this->assertTrue(null === self::fixtures()->clear()->getRepo('User')->find($userToDeleteId));
    }

    public function testDeleteNotPermittedForAdmin()
    {
        $deputy3 = self::fixtures()->createUser();
        $deputy3->setRoleName(User::ROLE_LAY_DEPUTY);

        self::fixtures()->flush();
        $userToDeleteId = $deputy3->getId();

        $url = '/user/'.$userToDeleteId;

        $this->assertJsonRequest('DELETE', $url, [
            'mustFail' => true,
            'assertResponseCode' => 403,
            'AuthToken' => self::$tokenAdmin,
        ]);
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
        $url = '/user/recreate-token/mail@example.org';

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

    public function testRecreateTokenWrongClientSecret()
    {
        $this->assertJsonRequest('PUT', '/user/recreate-token/mail@example.org', [
            'mustFail' => true,
            'assertResponseCode' => 403,
            'ClientSecret' => 'WRONG-CLIENT_SECRET',
        ]);
    }

    public function testRecreateTokenUserNotFound()
    {
        $this->assertJsonRequest('PUT', '/user/recreate-token/WRONGUSER@example.org', [
            'mustFail' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);
    }

    /**
     * Provides type, clientSecret, user email and whether the recreateToken should pass or fail (true|false).
     *
     * @return array
     */
    public static function recreateTokenProviderForRole()
    {
        return [
            [API_TOKEN_ADMIN, 'admin@example.org', true],
            [API_TOKEN_ADMIN, 'deputy@example.org', true],

            [API_TOKEN_DEPUTY, 'deputy@example.org', true],
            [API_TOKEN_DEPUTY, 'admin@example.org', false],

            [API_TOKEN_ADMIN, 'deputy@example.org', true],
            [API_TOKEN_ADMIN, 'admin@example.org', true],

            [API_TOKEN_DEPUTY, 'deputy@example.org', true],
            [API_TOKEN_DEPUTY, 'admin@example.org', false],
        ];
    }

    /**
     * @dataProvider recreateTokenProviderForRole
     */
    public function testRecreateTokenAcceptsClientSecret($secret, $email, $passOrFail)
    {
        $deputy = self::fixtures()->clear()->getRepo('User')->findOneByEmail($email);
        $deputy->setRegistrationToken(null);
        $deputy->setTokenDate(new \DateTime('2014-12-30'));
        self::fixtures()->flush($deputy);

        $url = '/user/recreate-token/'.$email;

        if ($passOrFail) {
            $this->assertJsonRequest('PUT', $url, [
                'mustSucceed' => true,
                'ClientSecret' => $secret,
            ]);
        } else {
            $this->assertJsonRequest('PUT', $url, [
                'mustFail' => true,
                'ClientSecret' => $secret,
            ]);
        }
    }

    public function testRecreateTokenEmailActivate()
    {
        $url = '/user/recreate-token/deputy@example.org';

        $deputy = self::fixtures()->clear()->getRepo('User')->findOneByEmail('deputy@example.org');
        $deputy->setRegistrationToken(null);
        $deputy->setTokenDate(new \DateTime('2014-12-30'));
        self::fixtures()->flush($deputy);

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
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

        $url = '/user/get-by-token/'.$deputy->getRegistrationToken();

        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
        ])['data'];
        $this->assertEquals('deputy@example.org', $data['email']);
    }

    public function testAgreeTermsUse()
    {
        // recreate reg token
        $deputy = self::fixtures()->clear()->getRepo('User')->findOneByEmail('deputy@example.org');
        $deputy->recreateRegistrationToken();
        self::fixtures()->flush($deputy);
        $url = '/user/agree-terms-use/'.$deputy->getRegistrationToken();

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
            'ClientSecret' => API_TOKEN_DEPUTY,
        ])['data'];

        $deputy = self::fixtures()->clear()->getRepo('User')->findOneByEmail('deputy@example.org');
        $this->assertTrue($deputy->getAgreeTermsUse());
        $this->assertEquals(date('Y-m-d'), $deputy->getAgreeTermsUseDate()->format('Y-m-d'));
    }

    public function testGetPrimaryAccount()
    {
        $url = '/user/get-primary-user-account/567890098765';

        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'assertResponseCode' => 200,
            'AuthToken' => self::$tokenAdmin,
        ])['data'];

        $this->assertEquals('multi-client-primary-deputy', $data['lastname']);
        $this->assertEquals('multi-client-primary-deputy@example.org', $data['email']);
        $this->assertTrue($data['is_primary']);
    }

    public function testGetPrimaryEmailNoUser(): void
    {
        $url = '/user/get-primary-email/523456234';

        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'assertResponseCode' => 200,
            'AuthToken' => self::$tokenAdmin,
        ])['data'];

        self::assertNull($data);
    }

    public function testGetPrimaryEmailMultiplePrimaryUsers(): void
    {
        $deputyUid = 8364689421;

        self::fixtures()->createUser([
            'setDeputyUid' => $deputyUid,
            'setEmail' => 'mrfake1@fakeland.fake',
            'setIsPrimary' => true,
        ]);

        self::fixtures()->createUser([
            'setDeputyUid' => $deputyUid,
            'setEmail' => 'mrfake2@fakeland.fake',
            'setIsPrimary' => true,
        ]);

        self::fixtures()->flush()->clear();

        $url = "/user/get-primary-email/$deputyUid";

        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'assertResponseCode' => 200,
            'AuthToken' => self::$tokenAdmin,
        ])['data'];

        self::assertNull($data);
    }

    public function testGetPrimaryEmail(): void
    {
        $deputyUid = 9975467801;
        $expectedEmail = 'fakenotrealperson@fake.fake';

        self::fixtures()->createUser([
            'setDeputyUid' => $deputyUid,
            'setEmail' => $expectedEmail,
            'setIsPrimary' => true,
        ]);

        self::fixtures()->flush()->clear();

        $url = "/user/get-primary-email/$deputyUid";

        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'assertResponseCode' => 200,
            'AuthToken' => self::$tokenAdmin,
        ])['data'];

        self::assertEquals($expectedEmail, $data);
    }
}
