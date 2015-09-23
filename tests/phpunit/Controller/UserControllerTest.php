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
        
        $return = $this->assertRequest('POST', '/user?skip-mail=1', [
            'data' => [
                'firstname' => 'n',
                'lastname' => 's',
                'email' => 'n.s.justice.gov.uk'
            ],
            'mustSucceed' => true
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
        
        $return = $this->assertRequest('GET', '/user/' . $id, [
            'mustSucceed' => true
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
        
        $return = $this->assertRequest('GET', '/user/0', [
            'mustFail' => true
        ]);
        $this->assertEmpty($return['data']);
        $this->assertContains('not found', $return['message']);
    }
}
