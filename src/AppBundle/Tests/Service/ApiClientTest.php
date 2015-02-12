<?php
namespace AppBundle\Tests\Service;

use AppBundle\Service\ApiClient;

class ApiClientTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateRequest()
    {
        $api = [
            'base_url' => 'http://digideps.api/',
            'endpoints' => [ 'find_user_by_email' => 'find-user-by-email']
        ];
        
        $expectedUrl = 'http://digideps.api/find-user-by-email';
        
        $restClient = new ApiClient($api);
        $request = $restClient->createRequest('GET', 'find_user_by_email');
        
        $this->assertInstanceOf('GuzzleHttp\Message\Request', $request);
        $this->assertEquals($expectedUrl,$request->getUrl());
    }
}