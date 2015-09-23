<?php

namespace AppBundle\Controller;
use AppBundle\Entity\User;
use AppBundle\Entity\Role;

class AuthControllerTest extends AbstractTestController
{
    public function testProtectedAreaIsNotAccessibleWhenNotLogged()
    {
        $this->assertRequest([
            'method' => 'GET',
            'uri' => '/auth/get-logged-user',
            'mustFail' => true
        ]);
    }
    
    public function testLoginFail()
    {
        $return = $this->assertRequest([
            'uri' => '/auth/login',
            'method' => 'POST',
            'data' => [
                'email' => 'user@mail.com-WRONG',
                'password' => 'password-WRONG',
            ],
            'mustFail' => true
        ]);
        $this->assertContains('not found', $return['message']);
        
        // assert I'm still not logged
        $this->assertRequest([ 'method' => 'GET', 'uri' => '/auth/get-logged-user',
            'mustFail' => true
        ]);
    }
    
    public function testLoginSuccess()
    {
        $this->login('deputy@example.org');
        
        // assert I'm logged
        $data = $this->assertRequest(['method' => 'GET', 'uri' => '/auth/get-logged-user',
            'mustSucceed' => true
        ])['data'];
        $this->assertEquals('deputy@example.org', $data['email']);
    }
    
    public function testLogout()
    {
        $this->testLoginSuccess();
        
        $this->logout();
        
        // assert I'm still not logged
        $this->assertRequest([ 'method' => 'GET', 'uri' => '/auth/get-logged-user',
            'mustFail' => true
        ]);
    }
}
