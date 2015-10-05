<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

abstract class AbstractTestController extends WebTestCase
{
    /**
     * @var \Fixtures
     */
    protected $fixtures;
    
    /**
     * @var Client 
     */
    protected $client;
    
    public function setUp()
    {
        $this->frameworkBundleClient = static::createClient([ 'environment' => 'test',
                                               'debug' => true ]);
        $em = $this->frameworkBundleClient->getContainer()->get('em');
        
        $em->clear();
        $this->fixtures = new \Fixtures($em);
    }
    
    /**
     * 
     * @param array $options with keys method, uri, data, mustSucceed, mustFail, assertId
     * @return type
     */
    public function assertRequest($method, $uri, array $options = [])
    {
        $headers = ['CONTENT_TYPE' => 'application/json'];
        if (isset($options['AuthToken'])) {
            $headers['HTTP_AuthToken'] = $options['AuthToken'];
        }
        if (isset($options['ClientSecret'])) {
            $headers['HTTP_ClientSecret'] = $options['ClientSecret'];
        }
        
        $this->frameworkBundleClient->request(
            $method, 
            $uri,
            [], [],
            $headers,
            isset($options['data']) ? json_encode($options['data']) : null
        );
        $response =  $this->frameworkBundleClient->getResponse();

        $this->assertTrue($response->headers->contains('Content-Type','application/json'), 'wrong content type. Headers: ' . $response->headers);
        $return = json_decode($response->getContent(), true);
        $this->assertNotEmpty($return, 'Response not json');
        if (!empty($options['mustSucceed'])) {
            $this->assertTrue($return['success'], "Endpoint didn't succeed as expected. Response: " . print_r($return, true));
            if (!empty($options['assertId'])) {
                $this->assertTrue($return['data']['id'] > 0);
            }
        }
        if (!empty($options['mustFail'])) {
            $this->assertFalse($return['success'], "Endpoint didn't failE as expected. Response: " . print_r($return, true));
        }
         if (!empty($options['assertCode'])) {
            $this->assertEquals($options['assertResponseCode'], $return['code'], "Response: " . print_r($return, true));
        }
        if (!empty($options['assertResponseCode'])) {
            $this->assertEquals($options['assertResponseCode'], $response->getStatusCode(), "Response: " .  $response->getStatusCode() . print_r($return, true));
        }
        
        return $return;
    }
    
    
    /**
     * @param string $email
     * @param string $password
     * 
     * @return string token
     */
    public function login($email, $password, $clientSecret)
    {
        $responseArray = $this->assertRequest('POST', '/auth/login', [
            'mustSucceed' => true,
            'ClientSecret' => $clientSecret,
            'data' => [
                'email' => $email,
                'password' => $password
            ],
        ])['data'];
        $this->assertEquals($email, $responseArray['email']);
        
        // check token
        $token = $this->frameworkBundleClient->getResponse()->headers->get('AuthToken');
        
        return $token;
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
    
    protected function assertEndpointNeedsAuth($method, $uri, $authToken = 'WRONG')
    {
        $response = $this->assertRequest($method, $uri, [
            'mustFail' => true,
            'AuthToken' => $authToken,
            'assertResponseCode' => 419
        ]);
        $this->assertEquals(419, $response['code']);
    }
    
    
    protected function assertEndpointNotAllowedFor($method, $uri, $token)
    {
        $this->assertRequest($method, $uri, [
            'mustFail' => true,
            'AuthToken' => $token,
            'assertResponseCode' => 403
        ]);
    }
    
    /**
     * @return string token
     */
    protected function loginAsDeputy()
    {
        return $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');
    }
    
    
    /**
     * @return string token
     */
    protected function loginAsAdmin()
    {
        return $this->login('admin@example.org', 'Abcd1234', '123abc-admin');
    }
    
    
}
