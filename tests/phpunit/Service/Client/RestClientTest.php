<?php

namespace AppBundle\Service\Client;

use GuzzleHttp\ClientInterface;
use JMS\Serializer\SerializerInterface;
use AppBundle\Service\Client\TokenStorage\TokenStorageInterface;
use Symfony\Bridge\Monolog\Logger;
use AppBundle\Entity\User;
use MockeryStub as m;

class RestClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RestClient
     */
    private $object;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var SerializerInterface
     */
    private $serialiser;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var Logger
     */
    private $logger;

    public function setUp()
    {
        $this->client = m::mock('GuzzleHttp\ClientInterface');
        $this->tokenStorage = m::mock('AppBundle\Service\Client\TokenStorage\TokenStorageInterface');
        $this->serialiser = m::mock('JMS\Serializer\SerializerInterface');
        $this->logger = m::mock('Symfony\Bridge\Monolog\Logger');
        $this->clientSecret = 'secret-123';
        $this->sessionToken = 'sessionToken347349r783';
        $this->container = m::mock('Symfony\Component\DependencyInjection\ContainerInterface')
            ->shouldReceive('get')->with('jms_serializer')->andReturn($this->serialiser)
            ->shouldReceive('get')->with('logger')->andReturn($this->logger)
            ->shouldReceive('getParameter')->with('kernel.debug')->andReturn(false)
            ->getMock();
        $this->container->shouldIgnoreMissing();

        $this->endpointResponse = m::mock('GuzzleHttp\Message\Response');

        $this->object = new RestClient(
            $this->container,
            $this->client,
            $this->tokenStorage,
            $this->clientSecret
        );

        $this->object->setLoggedUserId(1);
    }

    public function testLogin()
    {
        $credentialsArray = ['username' => 'u', 'password' => 'p'];
        $credentialsJson = json_encode($credentialsArray);
        $loggedUser = m::mock('AppBundle\Entity\User')
            ->shouldReceive('getId')->andReturn(1)
            ->getMock();
        $userArray = ['id' => 1, 'firstname' => 'Peter'];
        $userJson = json_encode($userArray);

        $this->serialiser
            ->shouldReceive('serialize')->with($credentialsArray, 'json')->andReturn($credentialsJson)
            ->shouldReceive('deserialize')->with($userJson, 'array', 'json')->andReturn(['success' => true, 'data' => $userArray])
            ->shouldReceive('deserialize')->with($userJson, 'AppBundle\Entity\User', 'json')->andReturn($loggedUser)
        ;

        $this->endpointResponse
            ->shouldReceive('getHeader')->with('AuthToken')->andReturn($this->sessionToken)
            ->shouldReceive('getBody')->andReturn($userJson)
        ;

        $this->client
            ->shouldReceive('post')->with('/auth/login', [
                'body' => $credentialsJson,
                'headers' => ['ClientSecret' => $this->clientSecret],
            ])->andReturn($this->endpointResponse);

        $this->tokenStorage
            ->shouldReceive('set')->once()->with(m::any(), $this->sessionToken);

        $this->logger
            ->shouldReceive('warning')->never();

        $this->assertEquals($loggedUser, $this->object->login($credentialsArray));
    }

    public function testLogout()
    {
        $responseData = 'ok';
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);

        $this->endpointResponse
            ->shouldReceive('getBody')->andReturn($responseJson);

        $this->tokenStorage
            ->shouldReceive('get')->once()->andReturn($this->sessionToken)
            ->shouldReceive('remove')->once()->with(1);

        $this->serialiser
            ->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray);

        $this->client
            ->shouldReceive('post')->with('/auth/logout', [
            'headers' => ['AuthToken' => $this->sessionToken],
            ])->andReturn($this->endpointResponse);

        $this->assertEquals($responseData, $this->object->logout());
    }

    public function testLoadUserByToken()
    {
        $token = 'user-token-123';
        $userArray = ['id' => 1, 'firstname' => 'Peter'];
        $userJson = json_encode($userArray);
        $responseArray = ['success' => true, 'data' => $userArray];
        $responseJson = json_encode($responseArray);
        $loggedUser = m::mock('AppBundle\Entity\User');

        $this->serialiser
            ->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray)
            ->shouldReceive('deserialize')->with($userJson, 'AppBundle\Entity\User', 'json')->andReturn($loggedUser)
        ;

        $this->endpointResponse
            ->shouldReceive('getBody')->andReturn($responseJson);

        $this->client->shouldReceive('get')->with("user/get-by-token/{$token}", [
            'headers' => ['ClientSecret' => $this->clientSecret],
        ])->andReturn($this->endpointResponse);

        $this->assertEquals($loggedUser, $this->object->loadUserByToken($token));
    }


    public function testRegisterUser()
    {
        $data = ['id' => 1];
        $responseArray = ['success' => true, 'data' => $data];
        $responseJson = json_encode($responseArray);
        $selfRegData = m::mock('AppBundle\Model\SelfRegisterData');
        $selfRegDataJson = 'selfRegData.json';

        $this->serialiser
            ->shouldReceive('serialize')->with($selfRegData, 'json', m::any())->andReturn($selfRegDataJson)
            ->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray)
        ;

        $this->endpointResponse
            ->shouldReceive('getBody')->andReturn($responseJson);

        $this->client->shouldReceive('post')->with('selfregister', [
                'headers' => ['ClientSecret' => $this->clientSecret],
                'body' => $selfRegDataJson,
            ])->andReturn($this->endpointResponse);

        $this->assertEquals($data, $this->object->registerUser($selfRegData));
    }

    public function testPut()
    {
        $putData = ['id' => 1, 'field' => 'value'];
        $putDataSerialised = json_encode($putData);

        $responseData = ['b'];
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);
        $endpointUrl = '/path/to/endpoint';

        $this->serialiser
            ->shouldReceive('serialize')->with($putData, 'json')->andReturn($putDataSerialised)
            ->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray)
        ;

        $this->endpointResponse
            ->shouldReceive('getBody')->andReturn($responseJson);

        $this->tokenStorage
            ->shouldReceive('get')->once()->andReturn($this->sessionToken);

        $this->client->shouldReceive('put')->with($endpointUrl, [
                'headers' => ['AuthToken' => $this->sessionToken],
                'body' => $putDataSerialised,
            ])->andReturn($this->endpointResponse);

        $this->assertEquals($responseData, $this->object->put($endpointUrl, $putData, []));
    }

    public function testPost()
    {
        $postData = ['id' => 1, 'field' => 'value'];
        $postDataSerialised = json_encode($postData);

        $responseData = ['b'];
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);
        $endpointUrl = '/path/to/endpoint';

        $this->serialiser
            ->shouldReceive('serialize')->with($postData, 'json')->andReturn($postDataSerialised)
            ->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray)
        ;

        $this->endpointResponse
            ->shouldReceive('getBody')->andReturn($responseJson);

        $this->tokenStorage
            ->shouldReceive('get')->once()->andReturn($this->sessionToken);

        $this->client->shouldReceive('post')->with($endpointUrl, [
                'headers' => ['AuthToken' => $this->sessionToken],
                'body' => $postDataSerialised,
            ])->andReturn($this->endpointResponse);

        $this->assertEquals($responseData, $this->object->post($endpointUrl, $postData, []));
    }

    public function testGetArray()
    {
        $endpointUrl = '/path/to/endpoint';
        $responseType = 'array';
        $responseData = ['b'];
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);

        $this->serialiser
            ->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray)
        ;

        $this->tokenStorage
            ->shouldReceive('get')->once()->andReturn($this->sessionToken);

        $this->endpointResponse
            ->shouldReceive('getBody')->andReturn($responseJson);

        $this->client->shouldReceive('get')->with($endpointUrl, [
                'headers' => ['AuthToken' => $this->sessionToken],
            ])->andReturn($this->endpointResponse);

        $this->assertEquals($responseData, $this->object->get($endpointUrl, $responseType));
    }

    public function testGetEntity()
    {
        $endpointUrl = '/path/to/endpoint';
        $expectedResponseType = 'User';
        $responseData = ['b'];
        $responseDataJson = json_encode('b');
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);
        $user = m::mock('AppBundle\Entity\User');

        $this->serialiser
            ->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray) //extractDataArray()
            ->shouldReceive('deserialize')->with($responseDataJson, 'AppBundle\Entity\User', 'json')->andReturn($user)
        ;

        $this->tokenStorage
            ->shouldReceive('get')->once()->andReturn($this->sessionToken);

        $this->endpointResponse
            ->shouldReceive('getBody')->andReturn($responseJson);

        $this->client->shouldReceive('get')->with($endpointUrl, [
                'headers' => ['AuthToken' => $this->sessionToken],
            ])->andReturn($this->endpointResponse);

        $this->assertEquals($user, $this->object->get($endpointUrl, $expectedResponseType));
    }

    public function testGetEntities()
    {
        $endpointUrl = '/path/to/endpoint';
        $expectedResponseType = 'User[]';
        $user1Array = ['id' => 1];
        $user1Json = json_encode($user1Array);
        $user2Array = ['id' => 2];
        $user2Json = json_encode($user2Array);
        $responseData = [$user1Array, $user2Array];
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);
        $user1 = m::stub('AppBundle\Entity\User', ['getId' => 1]);
        $user2 = m::stub('AppBundle\Entity\User', ['getId' => 2]);

        $this->serialiser
            ->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray) //extractDataArray()
            ->shouldReceive('deserialize')->with($user1Json, 'AppBundle\Entity\User', 'json')->andReturn($user1)
            ->shouldReceive('deserialize')->with($user2Json, 'AppBundle\Entity\User', 'json')->andReturn($user2)
        ;

        $this->tokenStorage
            ->shouldReceive('get')->once()->andReturn($this->sessionToken);

        $this->endpointResponse
            ->shouldReceive('getBody')->andReturn($responseJson);

        $this->client->shouldReceive('get')->with($endpointUrl, [
                'headers' => ['AuthToken' => $this->sessionToken],
            ])->andReturn($this->endpointResponse);

        $actual = $this->object->get($endpointUrl, $expectedResponseType);

        $this->assertEquals($user1, $actual[1]);
        $this->assertEquals($user2, $actual[2]);
    }

    /**
     * @expectedException AppBundle\Service\Client\Exception\NoSuccess
     */
    public function testGetNoSuccess()
    {
        $endpointUrl = '/path/to/endpoint';
        $expectedResponseType = 'array';
        $responseData = ['b'];
        $responseArray = ['success' => false, 'data' => $responseData, 'message' => 'm'];
        $responseJson = json_encode($responseArray);

        $this->serialiser
            ->shouldReceive('deserialize')->with($responseJson, 'array', 'json');

        $this->tokenStorage
            ->shouldReceive('get')->once()->andReturn($this->sessionToken);

        $this->endpointResponse
            ->shouldReceive('getBody')->andReturn($responseJson);

        $this->client->shouldReceive('get')->with($endpointUrl, [
                'headers' => ['AuthToken' => $this->sessionToken],
            ])->andReturn($this->endpointResponse);

        $this->object->get($endpointUrl, $expectedResponseType);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetWrongExpectedType()
    {
        $endpointUrl = '/path/to/endpoint';
        $expectedResponseType = 'InvalidTypeWithNonexistingClass';
        $responseData = [];
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);
        $user1 = m::stub('AppBundle\Entity\User', ['getId' => 1]);
        $user2 = m::stub('AppBundle\Entity\User', ['getId' => 2]);

        $this->serialiser
            ->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray);

        $this->tokenStorage
            ->shouldReceive('get')->once()->andReturn($this->sessionToken);

        $this->endpointResponse
            ->shouldReceive('getBody')->andReturn($responseJson);

        $this->client->shouldReceive('get')->with($endpointUrl, [
                'headers' => ['AuthToken' => $this->sessionToken],
            ])->andReturn($this->endpointResponse);

        $actual = $this->object->get($endpointUrl, $expectedResponseType);

        $this->assertEquals($user1, $actual[1]);
        $this->assertEquals($user2, $actual[2]);
    }

    /**
     * @expectedException AppBundle\Exception\RestClientException
     */
    public function testNetworkExceptionIsLoggedAndReThrown()
    {
        $endpointUrl = '/path/to/endpoint';

        $this->tokenStorage
            ->shouldReceive('get')->once()->andReturn($this->sessionToken);

        $this->endpointResponse
            ->shouldReceive('getBody')->andReturn('whatever');

        $this->logger
            ->shouldReceive('warning')->once();

        $this->client->shouldReceive('get')->with($endpointUrl, [
                'headers' => ['AuthToken' => $this->sessionToken],
            ])->andThrow(new \GuzzleHttp\Exception\TransferException('network failure'));

        $this->object->get($endpointUrl, 'array');
    }

    public function testDelete()
    {
        $endpointUrl = '/path/to/endpoint';
        $responseData = ['b'];
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);

        $this->serialiser
            ->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray);

        $this->tokenStorage
            ->shouldReceive('get')->once()->andReturn($this->sessionToken);

        $this->endpointResponse
            ->shouldReceive('getBody')->andReturn($responseJson);

        $this->client->shouldReceive('delete')->with($endpointUrl, [
                'headers' => ['AuthToken' => $this->sessionToken],
            ])->andReturn($this->endpointResponse);

        $this->assertEquals($responseData, $this->object->delete($endpointUrl));
    }

    public function testXRequestIdForwarded()
    {
        $responseArray = ['success' => true, 'data' => 1];
        $responseJson = json_encode($responseArray);

        $this->serialiser
            ->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray) //extractDataArray()
        ;

        $this->tokenStorage
            ->shouldReceive('get')->once()->andReturn($this->sessionToken);

        $this->endpointResponse
            ->shouldReceive('getBody')->andReturn($responseJson);

        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->headers->set('x-request-id', 'XRI');

        $this->container->shouldReceive('get')->with('request')->andReturn($request);

        $this->logger->shouldReceive('error')->andReturnUsing(function ($e) { echo $e;});

        $this->client->shouldReceive('get')->with('/', [
                'headers' => [
                    'AuthToken' => $this->sessionToken,
                    'X-Request-ID' => 'XRI',

            ],
            ])->andReturn($this->endpointResponse);

        $this->object->get('/', 'array');
    }

    public function testGetHistory()
    {
        $this->client = m::mock('GuzzleHttp\ClientInterface');
        $this->tokenStorage = m::mock('AppBundle\Service\Client\TokenStorage\TokenStorageInterface');
        $this->serialiser = m::mock('JMS\Serializer\SerializerInterface');
        $this->logger = m::mock('Symfony\Bridge\Monolog\Logger');
        $this->clientSecret = 'secret-123';
        $this->sessionToken = 'sessionToken347349r783';
        $this->container = m::mock('Symfony\Component\DependencyInjection\ContainerInterface')
            ->shouldReceive('get')->with('jms_serializer')->andReturn($this->serialiser)
            ->shouldReceive('get')->with('logger')->andReturn($this->logger)
            ->shouldReceive('get')->with('request')->andReturn(null)
            ->shouldReceive('getParameter')->with('kernel.debug')->andReturn(true)
            ->getMock();

        $this->endpointResponse = m::mock('GuzzleHttp\Message\Response');

        $object = new RestClient(
            $this->container,
            $this->client,
            $this->tokenStorage,
            $this->clientSecret
        );
        $object->setLoggedUserId(1);

        $endpointUrl = '/path/to/endpoint';
        $responseData = ['bbbbb'];
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);

        $this->serialiser
            ->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray);

        $this->tokenStorage
            ->shouldReceive('get')->andReturn($this->sessionToken);

        $this->endpointResponse
            ->shouldReceive('getBody')->andReturn($responseJson)
            ->shouldReceive('getStatusCode')->andReturn(200);

        $this->client->shouldReceive('delete')->with($endpointUrl, [
                'headers' => ['AuthToken' => $this->sessionToken],
            ])->andReturn($this->endpointResponse);

        $object->delete($endpointUrl);

        $actual = $object->getHistory();
        $this->assertCount(1, $actual);

        $this->assertEquals($endpointUrl, $actual[0]['url']);
        $this->assertEquals('delete', $actual[0]['method']);
        $this->assertContains($this->sessionToken, $actual[0]['options']);
        $this->assertEquals(200, $actual[0]['responseCode']);
        $this->assertContains('bbbbb', $actual[0]['responseBody']);

        $this->assertTrue($actual[0]['time'] > 0);
        $this->assertTrue($actual[0]['time'] < 1);
    }

    public function tearDown()
    {
        m::close();
    }
}
