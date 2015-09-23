<?php

namespace AppBundle\Controller;
use AppBundle\Entity\User;


class AuthControllerTest extends AbstractTestController
{
    public function testCreateUser()
    {
        $user = $this->fixtures->createUser([
            'setEmail' => 'user@mail.com'.time(),
            'setPassword' => 'plainPassword',
        ]);
        $this->fixtures->flush($user);
        $this->fixtures->clear();
        
        return $user;
    }


//    public function testProtectedAreaIsNotAccessibleWhenNotLogged()
//    {
//        $return = $this->assertRequest([
//            'method' => 'GET',
//            'uri' => '/auth/test',
//            'mustFail' => true
//        ]);
//
//        $this->assertContains('forbidden', $return['message']);
//    }
    
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
    }
}
