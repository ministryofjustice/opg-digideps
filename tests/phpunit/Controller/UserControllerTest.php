<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    /**
     * @var Symfony\Bundle\FrameworkBundle\Client 
     */
    private $client;
    
    public function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * @test
     */
    public function addJson()
    {
        // create user
         $this->client->request(
            'POST', '/user/', 
            array(), array(), 
            array('CONTENT_TYPE' => 'application/json'), 
            json_encode(array(
                'first_name' => 'Elvis',
                'last_name' => 'Ciotti',
                'email' => 'elvis.ciotti@digital.justice.gov.uk',
            ))
        );
        $response =  $this->client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type','application/json'), 'wrong content type');
//        echo $response->getContent();die;
        $return = json_decode($response->getContent(), true);
//        print_r($return);die;
        $this->assertNotEmpty($return, 'Response not json');
        $this->assertTrue($return['success'], $return['message']);
        $this->assertArrayHasKey('message', $return);
        $this->assertTrue($return['data']['id'] > 0);
        
        return $return['data']['id'];
    }
    
    /**
     * @test
     * @depends addJson
     */
    public function getOneJson($id)
    {
        $this->client->request('GET', '/user/' . $id, array(), array(), array('CONTENT_TYPE' => 'application/json'), 'wrong content type');
        $response =  $this->client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type','application/json'));
//        echo $response->getContent();die;
        $return = json_decode($response->getContent(), true);
//        print_r($return); die;
        $this->assertNotEmpty($return, 'Response not json');
        $this->assertTrue($return['success'], $return['message']);
        $this->assertEquals('Elvis', $return['data']['firstname']);
    }
    
    /**
     * @test
     */
    public function getOneJsonException()
    {
        $this->client->request('GET', '/user/' . 0, array(), array(), array('CONTENT_TYPE' => 'application/json'), 'wrong content type');
        $response =  $this->client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type','application/json'));
        $return = json_decode($response->getContent(), true);
        $this->assertNotEmpty($return, 'Response not json');
        $this->assertFalse($return['success']);
        $this->assertEmpty($return['data']);
        $this->assertContains('not found', $return['message']);
        
    }
    
    /**
     * @test
     * @depends addJson
     */
    public function getOneXml($id)
    {
        $this->client->request('GET', '/user/' . $id, array(), array(), array('CONTENT_TYPE' => 'application/xml'), 'wrong content type' );
        $response =  $this->client->getResponse();
        $xml = simplexml_load_string($response->getContent());
        $this->assertTrue(count($xml->children()) > 1);
    }
}
