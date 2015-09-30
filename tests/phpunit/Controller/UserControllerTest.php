<?php

namespace AppBundle\Controller;

class UserControllerTest extends AbstractTestController
{
    
    
    /**
     * @test
     */
    public function addJson()
    {
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
    
    /**
     * @test
     * @depends addJson
     */
    public function getOneJson($id)
    {
        $token = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');
        
        $return = $this->assertRequest('GET', '/user/' . $id, [
            'mustSucceed' => true,
            'AuthToken' => $token
        ]);
        $this->assertTrue($return['success'], $return['message']);
        $this->assertEquals('n', $return['data']['firstname']);
    }
    
    /**
     * @test
     */
    public function getOneJsonException()
    {
        $token = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');
        
        $return = $this->assertRequest('GET', '/user/0', [
            'mustFail' => true,
            'AuthToken' => $token
        ]);
        $this->assertEmpty($return['data']);
        $this->assertContains('not found', $return['message']);
    }
    
    /**
     * @test
     */
    public function acl()
    {
        $this->assertEndpointReturnAuthError('POST', '/user');
        $this->assertEndpointReturnAuthError('GET', '/user/1');
    }
}
