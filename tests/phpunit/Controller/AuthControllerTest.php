<?php

namespace AppBundle\Controller;
use AppBundle\Entity\User;
use AppBundle\Entity\Role;

class AuthControllerTest extends AbstractTestController
{
    public function testProtectedAreaIsNotAccessibleWhenNotLogged()
    {
        $this->assertRequest('GET', '/auth/get-logged-user',[
            'mustFail' => true
        ]);
    }
    
    public function testLoginFail()
    {
        $return = $this->assertRequest('POST', '/auth/login', [
            'mustFail' => true,
            'data' => [
                'email' => 'user@mail.com-WRONG',
                'password' => 'password-WRONG',
            ]
        ]);
        $this->assertContains('not found', $return['message']);
        
        // assert I'm still not logged
        $this->assertRequest('GET','/auth/get-logged-user', [
            'mustFail' => true
        ]);
    }
    
    public function testLoginSuccess()
    {
        $this->login('deputy@example.org');
        
        // assert I'm logged
        $data = $this->assertRequest('GET', '/auth/get-logged-user', [
            'mustSucceed' => true
        ])['data'];
        $this->assertEquals('deputy@example.org', $data['email']);
    }
    
    public function testLogout()
    {
        $this->testLoginSuccess();
        
        $this->logout();
        
        // assert I'm still not logged
        $this->assertRequest('GET', '/auth/get-logged-user', [
            'mustFail' => true
        ]);
    }
}
