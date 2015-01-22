<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class UserControllerTest extends WebTestCase
{
    private static $client;
    
    public function setUp()
    {
        self::$client = static::createClient();
    }

    /**
     * @test
     */
    public function add()
    {
        // create user
         self::$client->request('POST', '/user/', array(), array(), array(), json_encode(array(
            'first_name' => 'Elvis',
            'last_name' => 'Ciotti',
            'email' => 'elvis.ciotti@digital.justice.gov.uk',
        )));
        $response =  self::$client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type','application/json'));
        $return = json_decode($response->getContent(), true);
        $this->assertNotEmpty($return, 'Response not json');
        $this->assertArrayHasKey('id', $return);
        
        return $return;
    }
    
    /**
     * @test
     * @depends add
     */
    public function getOne($return)
    {
        self::$client->request('GET', '/user/' . $return['id']);
        $response =  self::$client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type','application/json'));
        $return = json_decode($response->getContent(), true);
        $this->assertNotEmpty($return, 'Response not json');
        $this->assertEquals('Elvis', $return['firstname']);
        
    }
}
