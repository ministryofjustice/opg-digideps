<?php

namespace Tests\OPG\Digideps\Backend\Integration\Controller;

use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Service\BruteForce\AttemptsIncrementalWaitingChecker;
use OPG\Digideps\Backend\Service\BruteForce\AttemptsInTimeChecker;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;

class AuthControllerTest extends AbstractTestController
{
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    #[Test] public function endpointAuthChecks(): void
    {
        $this->assertEndpointNeedsAuth('GET', '/auth/get-logged-user');
    }

    #[Test] public function testLoginFailWrongPassword(): void
    {
        $this->resetAttempts('emailuser@mail.com-WRONG');

        $return = $this->assertJsonRequest('POST', '/auth/login', [
            'mustFail' => true,
            'data' => [
                'email' => 'user@mail.com-WRONG',
                'password' => 'password-WRONG',
            ],
            'ClientSecret' => self::$deputySecret,
            'assertCode' => 500,
            'assertResponseCode' => 500,
        ]);
        $this->assertStringContainsString('Bad credentials', $return['message']);

        // assert I'm still not logged
        $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true,
        ]);
    }

    private function resetAttempts($key): void
    {
        static::getContainer()->get(AttemptsInTimeChecker::class)->resetAttempts($key);
        static::getContainer()->get(AttemptsIncrementalWaitingChecker::class)->resetAttempts($key);
    }

    #[Test] public function testLoginFailSecretPermissions(): void
    {
        $this->resetAttempts('emailadmin@example.org');

        $return = $this->assertJsonRequest('POST', '/auth/login', [
            'mustFail' => true,
            'data' => [
                'email' => 'admin@example.org',
                'password' => 'DigidepsPass1234',
            ],
            'ClientSecret' => self::$deputySecret,
            'assertCode' => 403,
            'assertResponseCode' => 403,
        ]);
        $this->assertStringContainsString('not allowed from this client', $return['message']);

        // assert I'm still not logged
        $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true,
        ]);
    }

    #[Test] public function testFailWrongAuthToken(): void
    {
        $authToken = $this->login('deputy@example.org', 'DigidepsPass1234', self::$deputySecret);

        $this->assertTrue(strlen($authToken) > 5, "Token $authToken not valid");

        // assert fail with wrong token
        $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true,
            'AuthToken' => 'WRONG_AUTH_TOKEN',
            'assertCode' => 419,
            'assertResponseCode' => 419,
        ]);
    }

    #[Test] public function testLoginSuccess(): mixed
    {
        $authToken = $this->login('deputy@example.org', 'DigidepsPass1234', self::$deputySecret);

        // assert succeed with token
        $data = $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustSucceed' => true,
            'AuthToken' => $authToken,
        ])['data'];

        $this->assertEquals(User::ROLE_LAY_DEPUTY, $data['role_name']);
        $this->assertEquals('deputy@example.org', $data['email']);

        return $authToken;
    }

    #[Depends('testLoginSuccess')] public function testLogout($authToken): void
    {
        $this->assertJsonRequest('POST', '/auth/logout', [
            'mustSucceed' => true,
            'AuthToken' => $authToken,
        ]);

        // assert the request with the old token fails
        $this->assertEndpointNeedsAuth('GET', '/auth/get-logged-user');
    }

    #[Test] public function testMultipleAccountCanLoginAtTheSameTimeAndThereIsNoInterference(): void
    {
        $this->resetAttempts('emaildeputy@example.org');
        $this->resetAttempts('emailadmin@example.org');

        $authTokenDeputy = $this->login('deputy@example.org', 'DigidepsPass1234', self::$deputySecret);
        $authTokenAdmin = $this->login('admin@example.org', 'DigidepsPass1234', self::$adminSecret);

        // assert deputy can access
        $data = $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustSucceed' => true,
            'AuthToken' => $authTokenDeputy,
        ])['data'];
        $this->assertEquals('deputy@example.org', $data['email']);

        // assert admin can access
        $data = $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustSucceed' => true,
            'AuthToken' => $authTokenAdmin,
        ])['data'];
        $this->assertEquals('admin@example.org', $data['email']);

        // logout admin and test deputy can still acess
        $this->assertJsonRequest('POST', '/auth/logout', [
            'mustSucceed' => true,
            'AuthToken' => $authTokenAdmin,
        ]);

        $data = $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustSucceed' => true,
            'AuthToken' => $authTokenDeputy,
        ])['data'];

        $this->assertEquals('deputy@example.org', $data['email']);
    }

    #[Test] public function testLoginTimeout(): void
    {
        $authToken = $this->login('deputy@example.org', 'DigidepsPass1234', self::$deputySecret);

        // manually expire token in REDIS
        static::getContainer()->get('predis')->expire($authToken, 0);

        $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true,
            'AuthToken' => $authToken,
            'assertCode' => 419,
            'assertResponseCode' => 419,
        ]);
    }

    #[Test] public function testPasswordHashNotInResponse(): void
    {
        $authToken = $this->login('deputy@example.org', 'DigidepsPass1234', self::$deputySecret);

        // assert succeed with token
        $data = $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustSucceed' => true,
            'AuthToken' => $authToken,
        ])['data'];

        $this->assertEquals('deputy@example.org', $data['email']);
        $this->assertFalse(isset($data['password']), 'A password was returned when it should not have been returned');
    }

    #[Test] public function testBruteForceSameEmail(): void
    {
        $this->resetAttempts('emaildeputy@example.org');

        // assert the application returns 498 (invalid credentials) for the 1st 4 attempts
        // and after 4 attempts it will return 499 (invalid credentials + too many attempts detected),
        // still allowing the user to try
        foreach ([500, 500, 500, 500, 500] as $expectedReturnCode) {
            $this->assertJsonRequest('POST', '/auth/login', [
                'mustFail' => true,
                'data' => [
                    'email' => 'deputy@example.org',
                    'password' => 'password-WRONG',
                ],
                'ClientSecret' => self::$deputySecret,
                'assertCode' => $expectedReturnCode,
                'assertResponseCode' => $expectedReturnCode,
            ]);
        }

        // assert I can still log in if the right password is provided
        $data = $this->assertJsonRequest('POST', '/auth/login', [
            'mustFail' => false,
            'data' => [
                'email' => 'deputy@example.org',
                'password' => 'DigidepsPass1234',
            ],
            'ClientSecret' => self::$deputySecret,
            'assertResponseCode' => 200,
        ])['data'];
        $this->assertEquals('deputy@example.org', $data['email']);

        // logout
        $authToken = self::$frameworkBundleClient->getResponse()->headers->get('AuthToken');
        $this->assertJsonRequest('POST', '/auth/logout', [
            'mustSucceed' => true,
            'AuthToken' => $authToken,
        ]);

        // 10  attempts
        foreach ([500, 500, 500, 500, 500, 500, 500, 500, 500] as $expectedReturnCode) {
            $this->assertJsonRequest('POST', '/auth/login', [
                'mustFail' => true,
                'data' => [
                    'email' => 'deputy@example.org',
                    'password' => 'password-WRONG',
                ],
                'ClientSecret' => self::$deputySecret,
                'assertCode' => $expectedReturnCode,
                'assertResponseCode' => $expectedReturnCode,
            ]);
        }

        // assert it's now locked, even if the password is correct
        $data = $this->assertJsonRequest('POST', '/auth/login', [
            'mustFail' => true,
            'data' => [
                'email' => 'deputy@example.org',
                'password' => 'DigidepsPass1234',
            ],
            'ClientSecret' => self::$deputySecret,
            'assertCode' => 423,
            'assertResponseCode' => 423,
        ])['data'];

        $this->assertTrue($data < 30, 'Data does not contain time the user is locked out for');
    }
}
