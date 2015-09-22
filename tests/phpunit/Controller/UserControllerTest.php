<?php

namespace AppBundle\Controller;

class UserControllerTest extends AbstractTestController
{
    /**
     * @test
     */
    public function addJson()
    {
        $user = $this->assertPostPutRequest('/user?skip-mail=1', [
            'firstname' => 'n',
            'lastname' => 's',
            'email' => 'n.s.justice.gov.uk'
        ], 'POST');
        
        return $user['id'];
    }
    
    /**
     * @test
     * @depends addJson
     */
    public function getOneJson($id)
    {
        $return = $this->assertGetRequest('/user/' . $id);
        $this->assertTrue($return['success'], $return['message']);
        $this->assertEquals('n', $return['data']['firstname']);
    }
    
    /**
     * @test
     */
    public function getOneJsonException()
    {
        $return = $this->assertGetRequest('/user/0');
        $this->assertFalse($return['success']);
        $this->assertEmpty($return['data']);
        $this->assertContains('not found', $return['message']);
        
    }
}
