<?php

namespace App\Tests\Unit\Controller;

use App\Entity\User;
use App\Service\BruteForce\AttemptsIncrementalWaitingChecker;
use App\Service\BruteForce\AttemptsInTimeChecker;
use app\tests\Integration\Controller\AbstractTestController;

class AuthControllerTest extends AbstractTestController
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    /**
     * @test
     */
    public function endpointAuthChecks()
    {
        $this->assertEndpointNeedsAuth('GET', '/auth/get-logged-user');
    }

    public function testLoginFailWrongPassword()
    {
        $this->resetAttempts('emailuser@mail.com-WRONG');

        $return = $this->assertJsonRequest('POST', '/auth/login', [
            'mustFail' => true,
            'data' => [
                'email' => 'user@mail.com-WRONG',
                'password' => 'password-WRONG',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
            'assertCode' => 498,
            'assertResponseCode' => 498,
        ]);
        $this->assertStringContainsString('Bad credentials', $return['message']);

        // assert I'm still not logged
        $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true,
        ]);
    }

    private function resetAttempts($key)
    {
        static::getContainer()->get(AttemptsInTimeChecker::class)->resetAttempts($key);
        static::getContainer()->get(AttemptsIncrementalWaitingChecker::class)->resetAttempts($key);
    }

    public function testLoginFailSecretPermissions()
    {
        $this->resetAttempts('emailadmin@example.org');

        $return = $this->assertJsonRequest('POST', '/auth/login', [
            'mustFail' => true,
            'data' => [
                'email' => 'admin@example.org',
                'password' => 'DigidepsPass1234',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
            'assertCode' => 403,
            'assertResponseCode' => 403,
        ]);
        $this->assertStringContainsString('not allowed from this client', $return['message']);

        // assert I'm still not logged
        $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true,
        ]);
    }

    public function testFailWrongAuthToken()
    {
        $authToken = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        $this->assertTrue(strlen($authToken) > 5, "Token $authToken not valid");

        // assert fail with wrong token
        $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true,
            'AuthToken' => 'WRONG_AUTH_TOKEN',
            'assertCode' => 419,
            'assertResponseCode' => 419,
        ]);
    }

    public function testLoginSuccess()
    {
        $authToken = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        // assert succeed with token
        $data = $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustSucceed' => true,
            'AuthToken' => $authToken,
        ])['data'];

        $this->assertEquals(User::ROLE_LAY_DEPUTY, $data['role_name']);
        $this->assertEquals('deputy@example.org', $data['email']);

        return $authToken;
    }

    /**
     * @depends testLoginSuccess
     */
    public function testLogout($authToken)
    {
        $this->assertJsonRequest('POST', '/auth/logout', [
            'mustSucceed' => true,
            'AuthToken' => $authToken,
        ]);

        // assert the request with the old token fails
        $this->assertEndpointNeedsAuth('GET', '/auth/get-logged-user');
    }

    public function testMultipleAccountCanLoginAtTheSameTimeAndThereIsNoInterference()
    {
        $this->resetAttempts('emaildeputy@example.org');
        $this->resetAttempts('emailadmin@example.org');

        $authTokenDeputy = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);
        $authTokenAdmin = $this->login('admin@example.org', 'DigidepsPass1234', API_TOKEN_ADMIN);

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

    public function testLoginTimeout()
    {
        $authToken = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        // manually expire token in REDIS
        static::getContainer()->get('snc_redis.default')->expire($authToken, 0);

        $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true,
            'AuthToken' => $authToken,
            'assertCode' => 419,
            'assertResponseCode' => 419,
        ]);
    }

    public function testPasswordHashNotInResponse()
    {
        $authToken = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        // assert succeed with token
        $data = $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustSucceed' => true,
            'AuthToken' => $authToken,
        ])['data'];

        $this->assertEquals('deputy@example.org', $data['email']);
        $this->assertFalse(isset($data['password']), 'A password was returned when it should not have been returned');
    }

    public function testBruteForceSameEmail()
    {
        $this->resetAttempts('emaildeputy@example.org');

        // assert the application returns 498 (invalid credentials) for the 1st 4 attempts
        // and after 4 attempts it will return 499 (invalid credentials + too many attempts detected),
        // still allowing the user to try
        foreach ([498, 498, 498, 498, 500] as $expectedReturnCode) {
            $this->assertJsonRequest('POST', '/auth/login', [
                'mustFail' => true,
                'data' => [
                    'email' => 'deputy@example.org',
                    'password' => 'password-WRONG',
                ],
                'ClientSecret' => API_TOKEN_DEPUTY,
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
            'ClientSecret' => API_TOKEN_DEPUTY,
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
        foreach ([498, 498, 498, 498, 500, 500, 500, 500, 500] as $expectedReturnCode) {
            $this->assertJsonRequest('POST', '/auth/login', [
                'mustFail' => true,
                'data' => [
                    'email' => 'deputy@example.org',
                    'password' => 'password-WRONG',
                ],
                'ClientSecret' => API_TOKEN_DEPUTY,
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
            'ClientSecret' => API_TOKEN_DEPUTY,
            'assertCode' => 423,
            'assertResponseCode' => 423,
        ])['data'];

        $this->assertTrue($data < 30, 'Data does not contain time the user is locked out for');
    }
}
