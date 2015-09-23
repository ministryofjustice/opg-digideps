<?php

namespace AppBundle\Controller;
use AppBundle\Entity\User;
use AppBundle\Entity\Role;

class AuthControllerTest extends AbstractTestController
{
    public function testCreateUser()
    {
        $user = $this->fixtures->createUser([
            'setEmail' => 'user@mail.com',
            'setPassword' => 'plainPassword',
            'setRole'=> $this->fixtures->getRepo('Role')->find(Role::ROLE_LAY_DEPUTY)
        ]);
        $this->fixtures->flush($user);
        $this->fixtures->clear();
        
        return $user;
    }


    public function testProtectedAreaIsNotAccessibleWhenNotLogged()
    {
        $this->assertRequest([
            'method' => 'GET',
            'uri' => '/auth/get-logged-user',
            'mustFail' => true
        ]);
    }
    
    /**
     * @depends testCreateUser
     */
    public function testLoginFail(User $user)
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
        $this->assertRequest([
            'method' => 'GET',
            'uri' => '/auth/get-logged-user',
            'mustFail' => true
        ]);
    }
    
    /**
     * @depends testCreateUser
     */
    public function testLoginSuccess(User $user)
    {
        $data = $this->assertRequest([
            'uri' => '/auth/login',
            'method' => 'POST',
            'data' => [
                'email' => $user->getEmail(),
                'password' => $user->getPassword(),
            ],
            'mustSucceed' => true
        ])['data'];
        
        $this->assertEquals($user->getId(), $data['id']);
        $this->assertEquals($user->getEmail(), $data['email']);
        
        // assert I'm not logged
        $this->assertRequest([
            'method' => 'GET',
            'uri' => '/auth/get-logged-user',
            'mustSucceed' => true
        ]);
    }
}
