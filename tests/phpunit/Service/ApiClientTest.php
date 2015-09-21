<?php
namespace AppBundle\Tests\Service;

//use AppBundle\Service\ApiClient;
use Mockery as m;
use AppBundle\Entity as EntityDir;
use GuzzleHttp\Client;


class ApiClientTest extends \PHPUnit_Framework_TestCase
{
    private $jsonSerializer;
    private $apiClientMock;
    private $session;
    private $options;
    
    public function setUp()
    {
        $this->jsonSerializer = m::mock('JMS\Serializer\Serializer');
        
        $this->options = [ 'base_url' => 'https://digideps.api/',
                            'format' => 'json',
                            'debug' => null,
                            'collectData' => false];
        
        $this->session = m::mock('session', [ 'start' => 1, 'getId' => 'test_session_id']);
        
        
        $containerMock = m::mock('Symfony\Component\DependencyInjection\ContainerInterface')
            ->shouldReceive('get')->with('jms_serializer')->andReturn($this->jsonSerializer)
            ->shouldReceive('get')->with('session')->andReturn($this->session)
            ->getMock();
            
        
        $this->apiClientMock = m::mock('AppBundle\Service\ApiClient[get,post,put]', [ $containerMock, $this->options ]);
    }
    
    public function tearDown()
    {
        m::close();
    }
    
    /**
     * Test get entity
     */
    public function testGetEntity()
    {
        $user = [ 'data' => [ 'id' => 1,
                              'firstname' => 'John',
                              'lastname' => 'Doe',
                              'email' => 'john.doe@digital.justice.gov.uk',
                              'password' => 'nothingsecret',
                              'active' => true,
                              'role' => 'ROLE_ADMIN',
                            ]];
        
        $userObject = new EntityDir\User();
        
        $this->jsonSerializer->shouldReceive('deserialize')->times(2)->with(m::any(),m::any(),m::any())->andReturn($user,$userObject);
       
        $mockGuzzleResponse = m::mock('Guzzle\Http\Message\Response');
        
        $mockGuzzleResponse->shouldReceive(['getBody' => json_encode($user)])->times(1);
        
        $this->apiClientMock->shouldReceive('get')->times(1)->andReturn($mockGuzzleResponse);
        
        $this->assertInstanceOf('\AppBundle\Entity\User',$this->apiClientMock->getEntity('User', 'find-user-by-email'));
    }
    
    /**
     * test get entities
     */
    public function testGetEntities()
    {
        $users = [ 'data' => [ [ 'id' => 1,
                                 'firstname' => 'John',
                                 'lastname' => 'Doe',
                                 'email' => 'john.doe@digital.justice.gov.uk',
                                 'password' => 'nothingsecret',
                                 'active' => true,
                                 'role' => 'ROLE_ADMIN' ],
           
                                [ 'id' => 1,
                                  'firstname' => 'Nolan',
                                  'lastname' => 'Ross',
                                  'email' => 'nolan.ross@digital.justice.gov.uk',
                                  'password' => 'nothingsecret',
                                  'active' => true,
                                  'role' => 'ROLE_ADMIN' ] 
                             ] 
               ];
       $userObject = new EntityDir\User();
       
       $this->jsonSerializer->shouldReceive('deserialize')->times(3)->with(m::any(),m::any(),m::any())->andReturn($users,$userObject, $userObject);
       
       $mockGuzzleResponse = m::mock('Guzzle\Http\Message\Response');
       
       $mockGuzzleResponse->shouldReceive(['getBody' => json_encode($users)])->times(1);
        
       $this->apiClientMock->shouldReceive('get')->times(1)->andReturn($mockGuzzleResponse);
        
       $this->assertInternalType('array',$this->apiClientMock->getEntities('User', 'find-user-by-email'));
    }
    
    /**
     * test postC
     */
    public function testPostCWhenBodyEntityIsObject()
    {
        $user = [ 'id' => 1,
                  'firstname' => 'John',
                  'lastname' => 'Doe',
                  'email' => 'john.doe@digital.justice.gov.uk',
                  'password' => 'nothingsecret',
                  'active' => true,
                  'role' => 'ROLE_ADMIN' ];
        
        $userObject = new EntityDir\User();
        
        $this->jsonSerializer->shouldReceive('serialize')->times(1)->with(m::any(),m::any(), m::any())->andReturn(json_encode($user));
        $this->jsonSerializer->shouldReceive('deserialize')->times(1)->with(m::any(),m::any(), m::any())->andReturn(['data' => $user]);
        
        $mockGuzzleResponse = m::mock('Guzzle\Http\Message\Response');
        $mockGuzzleResponse->shouldReceive(['getBody' => json_encode([ 'data' => json_encode($user)]) ])->times(1);
        
        $this->apiClientMock->shouldReceive('post')->with(m::any(),m::any())->times(1)->andReturn($mockGuzzleResponse);
        
        $this->assertInternalType('array', $this->apiClientMock->postC('find-user-by-email', $userObject));
    }
    
    
    /**
     * test putC
     */
    public function testPutCWhenBodyEntityIsObject()
    {
        $user = [ 'id' => 1,
                  'firstname' => 'John',
                  'lastname' => 'Doe',
                  'email' => 'john.doe@digital.justice.gov.uk',
                  'password' => 'nothingsecret',
                  'active' => true,
                  'role' => 'ROLE_ADMIN' ];
        
        $userObject = new EntityDir\User();
        
        $this->jsonSerializer->shouldReceive('serialize')->times(1)->with(m::any(),m::any(), m::any())->andReturn(json_encode($user));
        $this->jsonSerializer->shouldReceive('deserialize')->times(1)->with(m::any(),m::any(), m::any())->andReturn(['data' => $user]);
        
        $mockGuzzleResponse = m::mock('Guzzle\Http\Message\Response');
        $mockGuzzleResponse->shouldReceive(['getBody' => json_encode([ 'data' => json_encode($user)]) ])->times(1);
        
        $this->apiClientMock->shouldReceive('put')->with(m::any(),m::any())->times(1)->andReturn($mockGuzzleResponse);
        
        $this->assertInternalType('array', $this->apiClientMock->putC('find-user-by-email', $userObject));
    }
    
    /*public function testCreateRequest()
    {
        $this->assertInstanceOf('GuzzleHttp\Message\Request', $this->apiClientMock->createRequest('GET','find-user-by-email', [ 'query' => [ 'test']]));
    }*/
}