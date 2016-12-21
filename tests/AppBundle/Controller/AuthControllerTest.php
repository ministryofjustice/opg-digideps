<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\User;
use Mockery as m;

class AuthControllerTest extends AbstractTestController
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    private function resetAttempts($key)
    {
        self::$frameworkBundleClient->request('GET', '/'); // warm up to get container

        self::$frameworkBundleClient->getContainer()->get('attemptsInTimeChecker')->resetAttempts($key);
        self::$frameworkBundleClient->getContainer()->get('attemptsIncrementalWaitingChecker')->resetAttempts($key);
    }

    /**
     * @test
     */
    public function endpointAuthChecks()
    {
        $this->assertEndpointNeedsAuth('GET', '/auth/get-logged-user');
    }

    public function testLoginFailWrongSecret()
    {
        $return = $this->assertJsonRequest('POST', '/auth/login', [
            'mustFail' => true,
            'ClientSecret' => 'WRONG CLIENT SECRET',
            'assertCode' => 403,
            'assertResponseCode' => 403,
        ]);
        $this->assertContains('client secret not accepted', $return['message']);

        // assert I'm not logged
        $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true,
        ]);
    }

    public function testLoginFailWrongPassword()
    {
        $this->resetAttempts('email'.'user@mail.com-WRONG');

        $return = $this->assertJsonRequest('POST', '/auth/login', [
            'mustFail' => true,
            'data' => [
                'email' => 'user@mail.com-WRONG',
                'password' => 'password-WRONG',
            ],
            'ClientSecret' => '123abc-deputy',
            'assertCode' => 498,
            'assertResponseCode' => 498,
        ]);
        $this->assertContains('Cannot find user', $return['message']);

        // assert I'm still not logged
        $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true,
        ]);
    }

    public function testLoginFailSecretPermissions()
    {
        $this->resetAttempts('email'.'admin@example.org');

        $return = $this->assertJsonRequest('POST', '/auth/login', [
            'mustFail' => true,
            'data' => [
                'email' => 'admin@example.org',
                'password' => 'Abcd1234',
            ],
            'ClientSecret' => '123abc-deputy',
            'assertCode' => 403,
            'assertResponseCode' => 403,
        ]);
        $this->assertContains('not allowed from this client', $return['message']);

        // assert I'm still not logged
        $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true,
        ]);
    }

    public function testFailWrongAuthToken()
    {
        $authToken = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');

        $this->assertTrue(strlen($authToken) > 5, "Token $authToken not valid");

        // assert fail without token
        $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true,
            'assertCode' => 401,
            'assertResponseCode' => 401,
        ]);

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
        $authToken = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');

        // assert succeed with token
        $data = $this->assertJsonRequest('GET', '/auth/get-logged-user', [
                'mustSucceed' => true,
                'AuthToken' => $authToken,
            ])['data'];
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

    /**
     * @depends testLoginSuccess
     */
    public function testMultipleAccountCanLoginAtTheSameTimeAndThereIsNoInterference()
    {
        $this->resetAttempts('email'.'deputy@example.org');
        $this->resetAttempts('email'.'admin@example.org');

        $authTokenDeputy = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');
        $authTokenAdmin = $this->login('admin@example.org', 'Abcd1234', '123abc-admin');

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

        //logout admin and test deputy can still acess
        $this->assertJsonRequest('POST', '/auth/logout', [
            'mustSucceed' => true,
            'AuthToken' => $authTokenAdmin,
        ]);
        $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true,
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
        $authToken = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');

        // manually expire token in REDIS
        self::$frameworkBundleClient->getContainer()->get('snc_redis.default')->expire($authToken, 0);

        $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true,
            'AuthToken' => $authToken,
            'assertCode' => 419,
            'assertResponseCode' => 419,
        ]);
    }

    public function testBruteForceSameEmail()
    {
        $this->resetAttempts('email'.'deputy@example.org');

        // change in accordance with config_test.yml

        $expectedReturnCodes = [
            498, 498, 498, 498,
            499, 4,
        ];

        // assert the application returns 498 (invalid credentials) for the 1st 4 attempts
        // and after 4 attempts it will return 499 (invalid credentials + too many attempts detected),
        // still allowing the user to try
        foreach ([498, 498, 498, 498, 499] as $expectedReturnCode) {
            $this->assertJsonRequest('POST', '/auth/login', [
                'mustFail' => true,
                'data' => [
                    'email' => 'deputy@example.org',
                    'password' => 'password-WRONG',
                ],
                'ClientSecret' => '123abc-deputy',
                'assertCode' => $expectedReturnCode,
                'assertResponseCode' => $expectedReturnCode,
            ]);
        }

        // assert I can still log in if the right password is provided
        $data = $this->assertJsonRequest('POST', '/auth/login', [
            'mustFail' => false,
            'data' => [
                'email' => 'deputy@example.org',
                'password' => 'Abcd1234',
            ],
            'ClientSecret' => '123abc-deputy',
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
        foreach ([498, 498, 498, 498, 499, 499, 499, 499, 499] as $expectedReturnCode) {
            $this->assertJsonRequest('POST', '/auth/login', [
                'mustFail' => true,
                'data' => [
                    'email' => 'deputy@example.org',
                    'password' => 'password-WRONG',
                ],
                'ClientSecret' => '123abc-deputy',
                'assertCode' => $expectedReturnCode,
                'assertResponseCode' => $expectedReturnCode,
            ]);
        }

        // assert it's now locked, even if the password is correct
        $data = $this->assertJsonRequest('POST', '/auth/login', [
                'mustFail' => true,
                'data' => [
                    'email' => 'deputy@example.org',
                    'password' => 'Abcd1234',
                ],
                'ClientSecret' => '123abc-deputy',
                'assertCode' => 423,
                'assertResponseCode' => 423,
        ])['data'];

        $expectedTimeStamp = time() + 600;
        $this->assertTrue(abs($expectedTimeStamp -  $data) < 30, 'data does not contain when login with be unlocked');
    }
}
