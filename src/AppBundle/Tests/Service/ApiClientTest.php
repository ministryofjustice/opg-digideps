<?php
namespace AppBundle\Tests\Service;

//use AppBundle\Service\ApiClient;
use Mockery as m;

class ApiClientTest extends \PHPUnit_Framework_TestCase
{
    private $jsonSerializer;
    private $apiClientMock;
    
    public function setUp()
    {
        $this->jsonSerializer = m::mock('JMS\Serializer\Serializer');
        
        $options = [ 'base_url' => 'http://digideps.api/',
                     'endpoints' => [ 'find_user_by_email' => 'find-user-by-email'],
                     'format' => null,
                     'debug' => null ];
        //mock api client 
        $this->apiClientMock = m::mock('AppBundle\Service\ApiClient[get,post]', [ $this->jsonSerializer, $options ]);
    }
    
    public function tearDown()
    {
        m::close();
    }
    
    public function testGetEntity()
    {
        $this->jsonSerializer->shouldReceive('deserialize')->times(2)->with(m::any(),m::any(),m::any())->andReturn(true,true);
       
        $mockGuzzleResponse = m::mock('Guzzle\Http\Message\Response');
        $mockGuzzleResponse->shouldReceive(['json' => true , 'getBody' => true])->times(1);
        
        $this->apiClientMock->shouldReceive('get')->times(1)->andReturn($mockGuzzleResponse);
        
        $this->assertTrue($this->apiClientMock->getEntity('Test', 'find_user_by_email'));
    }
    
    public function testGetEntities()
    {
       $this->jsonSerializer->shouldReceive('deserialize')->times(4)->with(m::any(),m::any(),m::any())->andReturn( [ 'data' => [ true, true, true ]],true, true,true);
       
       $mockGuzzleResponse = m::mock('Guzzle\Http\Message\Response');
       $mockGuzzleResponse->shouldReceive(['json' => true , 'getBody' => true])->times(1);
        
       $this->apiClientMock->shouldReceive('get')->times(1)->andReturn($mockGuzzleResponse);
        
       $this->assertInternalType('array',$this->apiClientMock->getEntities('Test', 'find_user_by_email'));
    }
    
    public function testPostCWhenBodyEntityIsObject()
    {
        $this->jsonSerializer->shouldReceive('serialize')->times(1)->with(m::any(),m::any())->andReturn( "{'data': {}}" );
        
        $mockGuzzleResponse = m::mock('Guzzle\Http\Message\Response');
        $mockGuzzleResponse->shouldReceive(['getBody' => json_encode([ 'data' => []]) ])->times(1);
        
        $this->apiClientMock->shouldReceive('post')->with(m::any(),m::any())->times(1)->andReturn($mockGuzzleResponse);
        
        $this->assertInternalType('array', $this->apiClientMock->postC('find_user_by_email', new \stdClass()));
    }
    
    
    public function testCreateRequest()
    {
        $this->assertInstanceOf('GuzzleHttp\Message\Request', $this->apiClientMock->createRequest('GET','find_user_by_email', [ 'query' => [ 'test']]));
    }
}