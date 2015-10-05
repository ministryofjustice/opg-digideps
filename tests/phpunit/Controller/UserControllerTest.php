<?php

namespace AppBundle\Controller;

class UserControllerTest extends AbstractTestController
{
    public function testAdd()
    {
        $this->assertEndpointNeedsAuth('POST', '/user');
    
        $token = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');
        
        $return = $this->assertRequest('POST', '/user?skip-mail=1', [
            'data' => [
                'firstname' => 'n',
                'lastname' => 's',
                'email' => 'n.s.justice.gov.uk'
            ],
            'mustSucceed' => true,
            'AuthToken' => $token
        ]);
        
        return $return['data']['id'];
    }
    
    
    public function testupdate()
    {
        $this->assertEndpointNeedsAuth('PUT', '/user/1');
        
        //
    }
    
    public function testisPasswordCorrect()
    {
        $this->assertEndpointNeedsAuth('POST', '/user/1/is-password-correct');
        
    }
    
    public function testchangePassword()
    {
        $this->assertEndpointNeedsAuth('PUT', '/user/1/set-password');
    }
    
    /**
     * 
     * @depends testAdd
     */
    public function testGet($id)
    {
        $this->assertEndpointNeedsAuth('GET', '/user/1');
        
        $token = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');
        
        $return = $this->assertRequest('GET', '/user/' . $id, [
            'mustSucceed' => true,
            'AuthToken' => $token
        ]);
        $this->assertTrue($return['success'], $return['message']);
        $this->assertEquals('n', $return['data']['firstname']);
    }
    
    public function testGetUserNotExisting()
    {
        $token = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');
        
        $return = $this->assertRequest('GET', '/user/0', [
            'mustFail' => true,
            'AuthToken' => $token
        ]);
        $this->assertEmpty($return['data']);
        $this->assertContains('not found', $return['message']);
    }
    
    
    public function testdelete()
    {
        $this->assertEndpointNeedsAuth('DELETE', '/user/1/1');
    }
    
    public function testgetAll()
    {
        $this->assertEndpointNeedsAuth('GET', '/user/get-all/firstname/ASC');
    }
    
    
    public function testrecreateToken()
    {
        // assert client token
        $this->assertRequest('PUT', '/user/recreate-token/mail@example.org/activate', [
            'mustFail' => true,
            'assertResponseCode' => 403
        ]);
        $this->assertRequest('PUT', '/user/recreate-token/mail@example.org/activate', [
            'mustFail' => true,
            'assertResponseCode' => 403,
            'ClientSecret' => 'WRONG-CLIENT_SECRET'
        ]);
    }
    
    public function testregetByToken()
    {
        // assert client token 
        $this->assertRequest('GET', '/user/get-by-token/123abcd', [
            'mustFail' => true,
            'assertResponseCode' => 403
        ]);
        $this->assertRequest('GET', '/user/get-by-token/123abcd', [
            'mustFail' => true,
            'assertResponseCode' => 403,
            'ClientSecret' => 'WRONG-CLIENT_SECRET'
        ]);
    }
    
    
    
    
}
