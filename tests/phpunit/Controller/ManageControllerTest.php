<?php

namespace AppBundle\Controller;
use AppBundle\Entity\User;


class ManageControllerTest extends AbstractTestController
{
    
    public function testAvailability()
    {
        $ret = $this->assertRequest([
            'method' => 'GET', 
            'uri' => '/manage/availability',
            'assertResponseCode' => 200
        ])['data'];
    
        $this->assertEquals(1, $ret['healthy']);
    }
    
    public function testElb()
    {
        $ret = $this->assertRequest([
            'method' => 'GET', 
            'uri' => '/manage/elb',
            'assertResponseCode' => 200
        ])['data'];

        $this->assertEquals('ok', $ret);
    }
}
