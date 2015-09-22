<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

abstract class AbstractTestController extends WebTestCase
{
    /**
     * @var Fixtures
     */
    protected $fixtures;
    
    /**
     * @var Client 
     */
    protected $client;
    
    public function setUp()
    {
        $this->client = static::createClient([ 'environment' => 'test',
                                               'debug' => true ]);
        
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        
        $em->clear();
        $this->fixtures = new \Fixtures($em);
    }
    
    
    /**
     * @return array
     */
    public function assertPostPutRequest($url, array $data, $method = "POST")
    {
        $this->client->request(
            $method, 
            $url,
            array(), array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($data)
        );
        $response =  $this->client->getResponse();
        
        $this->assertTrue($response->headers->contains('Content-Type','application/json'), 'wrong content type');
        $return = json_decode($response->getContent(), true);
        $this->assertNotEmpty($return, 'Response not json');
        $this->assertTrue($return['success'], $return['message']);
        $this->assertTrue($return['data']['id'] > 0);
        
        return $return['data'];
    }
    
    /**
     * @return array
     */
    public function assertGetRequest($url)
    {
        $this->client->request('GET', $url);
        $response =  $this->client->getResponse();
        
        $this->assertTrue($response->headers->contains('Content-Type','application/json'), 'wrong content type');
        $return = json_decode($response->getContent(), true);
        $this->assertNotEmpty($return, 'Response not json');
        
        
        return $return;
    }
    
    
    public function tearDown()
    {
        $this->fixtures->clear();
    }
    
    protected function assertKeysArePresentWithTheFollowingValues($subset, $array)
    {
        foreach ($subset as $k=>$v) {
            $this->assertEquals($v, $array[$k]);
        }
    }
    
}
