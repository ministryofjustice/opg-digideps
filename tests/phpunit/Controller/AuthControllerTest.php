<?php

namespace AppBundle\Controller;
use AppBundle\Entity\User;
use AppBundle\Entity\Role;

class AuthControllerTest extends AbstractTestController
{
     /**
     * @test
     */
    public function endpointAuthChecks()
    {
        $this->assertEndpointReturnAuthError('GET', '/auth/get-logged-user');
    }
    
    public function testLoginFailWrongSecret()
    {
        $return = $this->assertRequest('POST', '/auth/login', [
            'mustFail' => true,
            'ClientSecret' => 'WRONG CLIENT SECRET',
            'assertCode' => 403,
            'assertResponseCode' => 403
        ]);
        $this->assertContains('client secret not accepted', $return['message']);
        
        // assert I'm not logged
        $this->assertRequest('GET','/auth/get-logged-user', [
            'mustFail' => true
        ]);
    }
    
    public function testLoginFailWrongPassword()
    {
        $return = $this->assertRequest('POST', '/auth/login', [
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
        $this->assertRequest('GET','/auth/get-logged-user', [
            'mustFail' => true
        ]);
    }
    
    public function testLoginFailSecretPermissions()
    {
        $return = $this->assertRequest('POST', '/auth/login', [
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
        $this->assertRequest('GET','/auth/get-logged-user', [
            'mustFail' => true
        ]);
    }
    
    
    public function testFailWrongAuthToken()
    {
        $authToken = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');
        
        $this->assertTrue(strlen($authToken)> 5, "Token $authToken not valid");
        
        // assert fail without token
        $this->assertRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true,
            'assertCode' => 401,
            'assertResponseCode' => 401
        ]);
        
        // assert fail with wrong token
        $this->assertRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true,
            'AuthToken' => 'WRONG_AUTH_TOKEN',
            'assertCode' => 401,
            'assertResponseCode' => 401
        ]);
    }
    
    public function testLoginSuccess()
    {
        $authToken = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');
        
        // assert succeed with token
        $data = $this->assertRequest('GET', '/auth/get-logged-user', [
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
        $this->assertRequest('POST', '/auth/logout', [
            'mustSucceed' => true,
            'AuthToken' => $authToken
        ]);

        // assert the request with the old token fails
        $this->assertEndpointReturnAuthError('GET', '/auth/get-logged-user');
    }
    
    /**
     * @depends testLoginSuccess
     */
    public function testMultipleAccountCanLoginAtTheSameTimeAndThereIsNoInterference()
    {
        $authTokenDeputy = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');
        $authTokenAdmin = $this->login('admin@example.org', 'Abcd1234', '123abc-admin');
        
        // assert deputy can access
        $data = $this->assertRequest('GET', '/auth/get-logged-user', [
            'mustSucceed' => true,
            'AuthToken' => $authTokenDeputy
        ])['data'];
        $this->assertEquals('deputy@example.org', $data['email']);
        
        
        // assert admin can access
        $data = $this->assertRequest('GET', '/auth/get-logged-user', [
            'mustSucceed' => true,
            'AuthToken' => $authTokenAdmin
        ])['data'];
        $this->assertEquals('admin@example.org', $data['email']);
        
        //logout admin and test deputy can still acess
        $this->assertRequest('POST', '/auth/logout', [
            'mustSucceed' => true,
            'AuthToken' => $authTokenAdmin
        ]);
        $this->assertRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true,
            'AuthToken' => $authTokenAdmin
        ]);
        $data = $this->assertRequest('GET', '/auth/get-logged-user', [
            'mustSucceed' => true,
            'AuthToken' => $authTokenDeputy
        ])['data'];
        $this->assertEquals('deputy@example.org', $data['email']);
    }
    
    public function testLoginTimeout()
    {
        $authToken = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');
        
        $this->assertRequest('GET', '/auth/get-logged-user', [
            'mustSucceed' => true,
            'AuthToken' => $authToken
        ]);
        
        // statically override timeout
        \AppBundle\Service\Auth\UserProvider::overrideTimeoutSeconds(-3600);
        
        $this->assertRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true,
            'AuthToken' => $authToken,
            'assertCode' => 419,
            'assertResponseCode' => 419
        ]);
        
        // remove timeout override
        \AppBundle\Service\Auth\UserProvider::overrideTimeoutSeconds(null);
    }
}
