<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderKind;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderType;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Repository\UserRepository;

class UserControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $admin1;
    private static $deputy2;
    private static $tokenAdmin;
    private static $tokenSuperAdmin;
    private static $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        if (self::$tokenAdmin === null) {
            self::$tokenSuperAdmin = $this->loginAsSuperAdmin();
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }

        /**
         * @var UserRepository $repository
         */
        $repository = self::fixtures()->getRepo(User::class);

        self::$deputy1 = $repository->findOneByEmail('deputy@example.org') ?? throw new \LogicException('Improper fixture setup');
        self::$admin1 = $repository->findOneByEmail('admin@example.org') ?? throw new \LogicException('Improper fixture setup');
        self::$deputy2 = self::fixtures()->createUser();

        self::fixtures()->flush()->clear();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function testAddAuth(): void
    {
        $url = '/user';

        $this->assertEndpointNeedsAuth('POST', $url);

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenDeputy);
    }

    public function testAddMissingParams(): void
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

    public function testAdd(): void
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

        $user = $this->fixtures()->clear()->getRepo(User::class)->find($return['data']['id']);
        $loggedInUser = $this->fixtures()->clear()->getRepo(User::class)->find($this->loggedInUserId);
        $this->assertNotNull($user);
        $this->assertNotNull($loggedInUser);
        $this->assertEquals('n', $user->getFirstname());
        $this->assertEquals('s', $user->getLastname());
        $this->assertEquals('n.s@example.org', $user->getEmail());
        $this->assertEquals($loggedInUser->getId(), $user->getCreatedBy()?->getId(), sprintf('The User that created this user was not as expected. Wanted user with ID: %s, Got: %g', $this->loggedInUserId, $user->getCreatedBy()?->getId()));
        $this->assertEquals(User::ADMIN_INVITE, $user->getRegistrationRoute());
    }

    public function testUpdateAuth(): void
    {
        $url = '/user/' . self::$deputy1->getId();

        $this->assertEndpointNeedsAuth('PUT', $url);
    }

    public function testUpdateAcl(): void
    {
        $url = '/user/' . self::$deputy1->getId();
        $url2 = '/user/' . self::$deputy2->getId();

        // deputy can only change their data
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);

        // admin can change any user
        $this->assertEndpointAllowedFor('PUT', $url, self::$tokenAdmin);
        $this->assertEndpointAllowedFor('PUT', $url2, self::$tokenAdmin);
    }

    public function testUpdate(): void
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

        $user = self::fixtures()->clear()->getRepo(User::class)->find($deputyId);
        $this->assertNotNull($user);

        $this->assertEquals(self::$deputy1->getLastname() . '-modified', $user->getLastname());
        $this->assertEquals(self::$deputy1->getEmail() . '-modified', $user->getEmail());
        $this->assertEquals(self::$deputy1->getAddress1() . '-modified', $user->getAddress1());

        // restore previous data
        $user->setLastname(str_replace('-modified', '', $user->getLastname()));
        $user->setEmail(str_replace('-modified', '', $user->getEmail()));
        $user->setAddress1(str_replace('-modified', '', $user->getAddress1() ?? ''));

        self::fixtures()->flush($user);
    }

    public function testUpdateNotPermittedToChangeType(): void
    {
        $deputyId = self::$deputy1->getId();
        $url = '/user/' . $deputyId;

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

    public function testIsPasswordCorrectAuth(): void
    {
        $url = '/user/' . self::$deputy2->getId() . '/is-password-correct';

        $this->assertEndpointNeedsAuth('POST', $url);
    }

    public function testIsPasswordCorrectAcl(): void
    {
        $url = '/user/' . self::$deputy2->getId() . '/is-password-correct';

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenDeputy);
    }

    public function testIsPasswordCorrect(): void
    {
        $url = '/user/' . self::$deputy1->getId() . '/is-password-correct';
        $this->assertEndpointNeedsAuth('POST', $url);
    }

    public function testChangePasswordAuth(): void
    {
        $url = '/user/' . self::$deputy1->getId() . '/set-password';

        $this->assertEndpointNeedsAuth('PUT', $url, ['password' => 'adfikhdbfsk']);
    }

    public function testChangePasswordAcl(): void
    {
        $url = '/user/' . self::$deputy2->getId() . '/set-password';

        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenDeputy, ['password' => 'ashjbasdfjhb']);
    }

    public function testChangePasswordMissingParams(): void
    {
        $url = '/user/' . self::$deputy1->getId() . '/set-password';

        // empty params
        $errorMessage = $this->assertJsonRequest('PUT', $url, [
            'mustFail' => true,
            'AuthToken' => self::$tokenDeputy,
            'assertResponseCode' => 400,
        ])['message'];
        $this->assertStringContainsString('password', $errorMessage);
    }

    public function testChangePasswordNoEmail(): void
    {
        $url = '/user/' . self::$deputy1->getId() . '/set-password';

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'password' => 'DigidepsPass1234ne',
            ],
        ]);

        $this->login('deputy@example.org', 'DigidepsPass1234ne', self::$deputySecret);
    }

    /**
     * @depends testChangePasswordNoEmail
     */
    public function testChangePasswordEmailActivate(): void
    {
        $url = '/user/' . self::$deputy1->getId() . '/set-password';

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'password' => 'DigidepsPass1234pa',
                'send_email' => 'activate',
            ],
        ]);

        $this->login('deputy@example.org', 'DigidepsPass1234pa', self::$deputySecret);
    }

    /**
     * @depends testChangePasswordEmailActivate
     */
    public function testChangePasswordEmailReset(): void
    {
        $url = '/user/' . self::$deputy1->getId() . '/set-password';

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'password' => 'DigidepsPass1234', // restore password for subsequent logins
                'send_email' => 'password-reset',
            ],
        ]);
    }

    public function testChangeEmailAuth(): void
    {
        $url = '/user/' . self::$deputy1->getId() . '/update-email';

        $this->assertEndpointNeedsAuth('PUT', $url);
    }

    public function testChangeEmailAcl(): void
    {
        $url = '/user/' . self::$deputy2->getId() . '/update-email';

        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenDeputy);
    }

    public function testGetOneByIdAuth(): void
    {
        $url = '/user/' . self::$deputy1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);
    }

    public function testGetOneByIdAcl(): void
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

    public function testGetOneById(): void
    {
        $url = '/user/' . self::$deputy1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);

        $return = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $this->assertEquals('deputy@example.org', $return['data']['email']);
    }

    public function testDeleteAuth(): void
    {
        $url = '/user/' . self::$deputy1->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
    }

    public function testDeleteAcl(): void
    {
        $url = '/user/' . self::$deputy1->getId();

        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenDeputy);
    }

    public function testDeletePermittedForSuperAdmin(): void
    {
        $deputy3 = self::fixtures()->createUser();
        $deputy3->setRoleName(User::ROLE_LAY_DEPUTY);
        self::fixtures()->flush();

        $client = self::fixtures()->createClient();
        $client->setUsers(new ArrayCollection([$deputy3]));
        self::fixtures()->flush();

        $userToDeleteId = $deputy3->getId();

        $url = '/user/' . $userToDeleteId;

        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenSuperAdmin,
        ]);

        $this->assertTrue(self::fixtures()->clear()->getRepo(User::class)->find($userToDeleteId) === null);
        $this->assertTrue($client->getDeletedAt() === null);
    }

    public function testDeleteNotPermittedForAdmin(): void
    {
        $deputy3 = self::fixtures()->createUser();
        $deputy3->setRoleName(User::ROLE_LAY_DEPUTY);

        self::fixtures()->flush();
        $userToDeleteId = $deputy3->getId();

        $url = '/user/' . $userToDeleteId;

        $this->assertJsonRequest('DELETE', $url, [
            'mustFail' => true,
            'assertResponseCode' => 403,
            'AuthToken' => self::$tokenAdmin,
        ]);
    }

    // DDLS-860 - deleting a user with associated court orders via admin UI, which calls DELETE /user/<id>
    public function testDeleteWithAssociatedCourtOrders(): void
    {
        $user = self::fixtures()->createUser();
        $user->setRoleName(User::ROLE_LAY_DEPUTY);

        $deputy = self::fixtures()->createDeputy([
            'setLastName' => 'DIRKOS',
            'setUser' => $user,
        ]);

        $courtOrder = self::fixtures()->createCourtOrder(
            '9944938281',
            CourtOrderType::PFA,
            CourtOrderKind::Single,
            'ACTIVE',
        );

        $deputy->associateWithCourtOrder($courtOrder);

        self::fixtures()->persist($user, $deputy, $courtOrder);
        self::fixtures()->flush();

        $url = sprintf('/user/%d', $user->getId());

        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenSuperAdmin,
        ]);
    }

    public function testGetAllAuth(): void
    {
        $url = '/user/get-all';

        $this->assertEndpointNeedsAuth('GET', $url);
    }

    public function testGetAllAcl(): void
    {
        $url = '/user/get-all';

        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenDeputy);
    }

    public function testGetAll(): void
    {
        $url = '/user/get-all';

        $return = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ]);

        $this->assertTrue(count($return['data']) > 2);
    }

    public function testRecreateTokenMissingClientSecret(): void
    {
        $url = '/user/recreate-token/mail@example.org';

        // assert client token
        $this->assertJsonRequest('PUT', $url, [
            'mustFail' => true,
            'assertResponseCode' => 403,
        ]);
    }

    public static function recreateTokenProvider(): array
    {
        return [
            ['activate', 'activate your account'],
            ['pass-reset', 'reset your password'],
        ];
    }

    public function testRecreateTokenWrongClientSecret(): void
    {
        $this->assertJsonRequest('PUT', '/user/recreate-token/mail@example.org', [
            'mustFail' => true,
            'assertResponseCode' => 403,
            'ClientSecret' => 'WRONG-CLIENT_SECRET',
        ]);
    }

    public function testRecreateTokenUserNotFound(): void
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
    public static function recreateTokenProviderForRole(): array
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
    public function testRecreateTokenAcceptsClientSecret($secret, string $email, bool $passOrFail): void
    {
        /**
         * @var UserRepository $repository
         */
        $repository = self::fixtures()->clear()->getRepo(User::class);
        $deputy = $repository->findOneByEmail($email);
        $this->assertNotNull($deputy);
        $deputy->setRegistrationToken(null);
        $deputy->setTokenDate(new \DateTime('2014-12-30'));
        self::fixtures()->flush($deputy);

        $url = '/user/recreate-token/' . $email;

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

    public function testRecreateTokenEmailActivate(): void
    {
        $url = '/user/recreate-token/deputy@example.org';

        /**
         * @var UserRepository $repository
         */
        $repository = self::fixtures()->clear()->getRepo(User::class);
        $user = $repository->findOneByEmail('deputy@example.org');
        $this->assertNotNull($user);
        $user->setRegistrationToken(null);
        $user->setTokenDate(new \DateTime('2014-12-30'));
        self::fixtures()->flush($user);

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        // refresh deputy from db and chack token has been reset
        self::fixtures()->clear();
        $userRefreshed = $repository->findOneByEmail('deputy@example.org');
        $this->assertNotNull($userRefreshed);
        $this->assertTrue(strlen($userRefreshed->getRegistrationToken() ?? '') > 5);
        $this->assertSame('0', $userRefreshed->getTokenDate()?->diff(new \DateTime())?->format('%a'));
    }

    public function testGetByToken(): void
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

        /**
         * @var UserRepository $repository
         */
        $repository = self::fixtures()->clear()->getRepo(User::class);
        $user = $repository->findOneByEmail('deputy@example.org');
        $this->assertNotNull($user);
        $user->recreateRegistrationToken();
        self::fixtures()->flush($user);

        $url = '/user/get-by-token/' . $user->getRegistrationToken();

        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
        ])['data'];
        $this->assertEquals('deputy@example.org', $data['email']);
    }

    public function testAgreeTermsUse(): void
    {
        /**
         * @var UserRepository $repository
         */
        $repository = self::fixtures()->getRepo(User::class);

        // recreate reg token
        self::fixtures()->clear();
        $user = $repository->findOneByEmail('deputy@example.org');
        $this->assertNotNull($user);
        $user->recreateRegistrationToken();
        self::fixtures()->flush($user);
        $url = '/user/agree-terms-use/' . $user->getRegistrationToken();

        $this->assertJsonRequest('PUT', $url, [
            'mustFail' => true,
            'assertResponseCode' => 403,
        ]);
        $this->assertJsonRequest('PUT', $url, [
            'mustFail' => true,
            'assertResponseCode' => 403,
            'ClientSecret' => 'WRONG-CLIENT_SECRET',
        ]);

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
        ])['data'];

        self::fixtures()->clear();
        $user = $repository->findOneByEmail('deputy@example.org');
        $this->assertNotNull($user);
        $this->assertTrue($user->getAgreeTermsUse());
        $this->assertEquals(date('Y-m-d'), $user->getAgreeTermsUseDate()?->format('Y-m-d'));
    }

    public function testGetPrimaryAccount(): void
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

        self::fixtures()->createUser(
            email: 'mrfake1@fakeland.fake',
            deputyUid: $deputyUid,
            isPrimary: true
        );

        self::fixtures()->createUser(
            email: 'mrfake2@fakeland.fake',
            deputyUid: $deputyUid,
            isPrimary: true
        );

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

        self::fixtures()->createUser(
            email: $expectedEmail,
            deputyUid: $deputyUid,
            isPrimary: true
        );

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
