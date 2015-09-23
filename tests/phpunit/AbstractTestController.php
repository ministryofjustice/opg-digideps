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
     * 
     * @param array $options with keys method, uri, data, mustSucceed, mustFail, assertId
     * @return type
     */
    public function assertRequest(array $options)
    {
        $this->client->request(
            $options['method'], 
            $options['uri'],
            array(), array(),
            array('CONTENT_TYPE' => 'application/json'),
            isset($options['data']) ? json_encode($options['data']) : null
        );
        $response =  $this->client->getResponse();
        
        $this->assertTrue($response->headers->contains('Content-Type','application/json'), 'wrong content type');
        $return = json_decode($response->getContent(), true);
        $this->assertNotEmpty($return, 'Response not json');
        if (!empty($options['mustSucceed'])) {
            $this->assertTrue($return['success'], $return['message']);
            if (!empty($options['assertId'])) {
                $this->assertTrue($return['data']['id'] > 0);
            }
        }
        if (!empty($options['mustFail'])) {
            $this->assertFalse($return['success']);
        }
        
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
