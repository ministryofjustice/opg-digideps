<?php

namespace AppBundle\Controller;

class UserControllerTest extends AbstractTestController
{
    /**
     * @test
     */
    public function addJson()
    {
        $this->login('deputy@example.org');
        
        $return = $this->assertRequest([
            'uri'=>'/user?skip-mail=1', 
            'method' => 'POST',
            'data'=>[
                'firstname' => 'n',
                'lastname' => 's',
                'email' => 'n.s.justice.gov.uk'
            ]
        ]);
        
        return $return['data']['id'];
    }
    
    /**
     * @test
     * @depends addJson
     */
    public function getOneJson($id)
    {
        $this->login('deputy@example.org');
        
        $return = $this->assertRequest([
            'uri'=>'/user/' . $id,
            'method'=>'GET'
        ]);
        $this->assertTrue($return['success'], $return['message']);
        $this->assertEquals('n', $return['data']['firstname']);
    }
    
    /**
     * @test
     */
    public function getOneJsonException()
    {
        $this->login('deputy@example.org');
        
        $return = $this->assertRequest([
            'uri'=>'/user/0',
            'method'=>'GET'
        ]);
        $this->assertFalse($return['success']);
        $this->assertEmpty($return['data']);
        $this->assertContains('not found', $return['message']);
        
    }
}
