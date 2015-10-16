<?php

namespace AppBundle\Controller;
use AppBundle\Entity\User;
use AppBundle\Entity\Role;

class AuthControllerTest extends AbstractTestController
{
    public static function setUpBeforeClass()
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
    
    public function testLoginFailWrongSecret()
    {
        $return = $this->assertJsonRequest('POST', '/auth/login', [
            'mustFail' => true,
            'ClientSecret' => 'WRONG CLIENT SECRET',
            'assertCode' => 403,
            'assertResponseCode' => 403
        ]);
        $this->assertContains('client secret not accepted', $return['message']);
        
        // assert I'm not logged
        $this->assertJsonRequest('GET','/auth/get-logged-user', [
            'mustFail' => true
        ]);
    }
    
    public function testLoginFailWrongPassword()
    {
        $return = $this->assertJsonRequest('POST', '/auth/login', [
            'mustFail' => true,
            'data' => [
                'email' => 'user@mail.com-WRONG',
                'password' => 'password-WRONG',
            ],
            'ClientSecret' => '123abc-deputy',
            'assertCode' => 498,
            'assertResponseCode' => 498
        ]);
        $this->assertContains('Cannot find user', $return['message']);
        
        // assert I'm still not logged
        $this->assertJsonRequest('GET','/auth/get-logged-user', [
            'mustFail' => true
        ]);
    }
    
    public function testLoginFailSecretPermissions()
    {
        $return = $this->assertJsonRequest('POST', '/auth/login', [
            'mustFail' => true,
            'data' => [
                'email' => 'admin@example.org',
                'password' => 'Abcd1234',
            ],
            'ClientSecret' => '123abc-deputy',
            'assertCode' => 403,
            'assertResponseCode' => 403
        ]);
        $this->assertContains('not allowed from this client', $return['message']);
        
        // assert I'm still not logged
        $this->assertJsonRequest('GET','/auth/get-logged-user', [
            'mustFail' => true
        ]);
    }
    
    
    public function testFailWrongAuthToken()
    {
        $authToken = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');
        
        $this->assertTrue(strlen($authToken)> 5, "Token $authToken not valid");
        
        // assert fail without token
        $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true,
            'assertCode' => 401,
            'assertResponseCode' => 401
        ]);
        
        // assert fail with wrong token
        $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true,
            'AuthToken' => 'WRONG_AUTH_TOKEN',
            'assertCode' => 419,
            'assertResponseCode' => 419
        ]);
    }
    
    public function testLoginSuccess()
    {
        $authToken = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');
        
        // assert succeed with token
        $data = $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustSucceed' => true,
            'AuthToken' => $authToken
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
            'AuthToken' => $authToken
        ]);

        // assert the request with the old token fails
        $this->assertEndpointNeedsAuth('GET', '/auth/get-logged-user');
    }
    
    /**
     * @depends testLoginSuccess
     */
    public function testMultipleAccountCanLoginAtTheSameTimeAndThereIsNoInterference()
    {
        $authTokenDeputy = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');
        $authTokenAdmin = $this->login('admin@example.org', 'Abcd1234', '123abc-admin');
        
        // assert deputy can access
        $data = $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustSucceed' => true,
            'AuthToken' => $authTokenDeputy
        ])['data'];
        $this->assertEquals('deputy@example.org', $data['email']);
        
        
        // assert admin can access
        $data = $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustSucceed' => true,
            'AuthToken' => $authTokenAdmin
        ])['data'];
        $this->assertEquals('admin@example.org', $data['email']);
        
        //logout admin and test deputy can still acess
        $this->assertJsonRequest('POST', '/auth/logout', [
            'mustSucceed' => true,
            'AuthToken' => $authTokenAdmin
        ]);
        $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true,
            'AuthToken' => $authTokenAdmin
        ]);
        $data = $this->assertJsonRequest('GET', '/auth/get-logged-user', [
            'mustSucceed' => true,
            'AuthToken' => $authTokenDeputy
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
            'assertResponseCode' => 419
        ]);
    }
    
    public static function bruteforceProvider()
    {
        return [
            [[
                // deputy@example.org: 5 attempts 
                ['deputy@example.org', 'password-WRONG', 498],
                ['deputy@example.org', 'password-WRONG', 498],
                ['deputy@example.org', 'password-WRONG', 498],
                ['deputy@example.org', 'password-WRONG', 498],
                ['deputy@example.org', 'password-WRONG', 498],
                // deputy-nonexisting@example.org: 5 attempts
                ['deputynonexisting@example.org', 'password-WRONG', 498],
                ['deputynonexisting@example.org', 'password-WRONG', 498],
                ['deputynonexisting@example.org', 'password-WRONG', 498],
                ['deputynonexisting@example.org', 'password-WRONG', 498],
                ['deputynonexisting@example.org', 'password-WRONG', 498],
                
                //
                ['deputy@example.org', 'password-WRONG', 403],
                ['deputynonexisting@example.org', 'password-WRONG', 403],
            ]],
            [[
                // if the email changes, no blocking !
                ['deputy1@example.org', 'password-WRONG', 498],
                ['deputy2@example.org', 'password-WRONG', 498],
                ['deputy3@example.org', 'password-WRONG', 498],
                ['deputy4@example.org', 'password-WRONG', 498],
                ['deputy5@example.org', 'password-WRONG', 498],
                ['deputy6@example.org', 'password-WRONG', 498],
            ]]
        ];
    }
    
    
    /**
     * @dataProvider bruteforceProvider
     */
    public function testBruteForceSameEmail()
    {
        // just to warm up container and 
        self::$frameworkBundleClient->request('GET', '/');
        $bfChecker = self::$frameworkBundleClient->getContainer()->get('bruteForceChecker');
        $bfChecker->resetAll();
        $maxAttempts = $bfChecker->getOptions()['max_attempts_email'];
        if (!$maxAttempts) {
            $this->fail(__METHOD__." : bruteForceChecker.max_attempts_email not set");
        }
        
        for ($i=0; $i<5; $i++) {
            $this->assertJsonRequest('POST', '/auth/login', [
                'mustFail' => true,
                'data' => [
                    'email' => 'deputy@example.org',
                    'password' => 'password-WRONG',
                ],
                'ClientSecret' => '123abc-deputy',
                'assertCode' => 498,
                'assertResponseCode' => 498
            ]);
        }
        
        $this->assertJsonRequest('POST', '/auth/login', [
                'mustFail' => true,
                'data' => [
                    'email' => 'deputy@example.org',
                    'password' => 'password-WRONG',
                ],
                'ClientSecret' => '123abc-deputy',
                'assertCode' => 423,
                'assertResponseCode' => 423
            ]);
        
    }
}
