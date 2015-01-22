<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class UserControllerTest extends WebTestCase
{
    
    
    public function testAddGet()
    {
        $client = static::createClient();

        // create user
        $client->request('POST', '/user/', array(), array(), array(), json_encode(array(
            'first_name' => 'Elvis',
            'last_name' => 'Ciotti',
            'email' => 'elvis.ciotti@digital.justice.gov.uk',
        )));
        $response =  $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type','application/json'));
        $return = json_decode($response->getContent(), true);
        $this->assertNotEmpty($return, 'Response not json');
        $this->assertArrayHasKey('id', $return);
        
        // create user
        $client->request('GET', '/user/' . $return['id']);
        $response =  $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type','application/json'));
        $return = json_decode($response->getContent(), true);
        $this->assertNotEmpty($return, 'Response not json');
        $this->assertEquals('Elvis', $return['firstname']);
        
    }
}
