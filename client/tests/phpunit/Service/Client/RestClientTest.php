<?php

namespace DigidepsTests\Service\Client;

use App\Service\Client\RestClient;
use App\Service\Client\TokenStorage\TokenStorageInterface;
use GuzzleHttp\ClientInterface;
use JMS\Serializer\SerializerInterface;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RestClientTest extends TestCase
{
    /**
     * @var RestClient
     */
    private $object;

    /**
     * @var ClientInterface|MockInterface
     */
    private $client;

    /**
     * @var SerializerInterface|MockInterface
     */
    private $serialiser;

    /**
     * @var TokenStorageInterface|MockInterface
     */
    private $tokenStorage;

    /**
     * @var Logger|MockInterface
     */
    private $logger;

    /**
     * @var ContainerInterface|MockInterface
     */
    private $container;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $sessionToken;

    /**
     * @var ResponseInterface|MockInterface
     */
    private $endpointResponse;

    /**
     * @var HttpClientInterface|MockInterface
     */
    private $phpApiClient;

    public function setUp(): void
    {
        $this->client = m::mock('GuzzleHttp\ClientInterface');
        $this->tokenStorage = m::mock('App\Service\Client\TokenStorage\TokenStorageInterface');
        $this->serialiser = m::mock('JMS\Serializer\SerializerInterface');
        $this->logger = m::mock('Symfony\Bridge\Monolog\Logger');
        $this->clientSecret = 'secret-123';
        $this->sessionToken = 'sessionToken347349r783';

        $this->container = m::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->container->shouldReceive('get')->with('jms_serializer')->andReturn($this->serialiser);
        $this->container->shouldReceive('get')->with('logger')->andReturn($this->logger);
        $this->container->shouldReceive('getParameter')->with('kernel.debug')->andReturn(false);
        $this->container->shouldIgnoreMissing();

        $this->endpointResponse = m::mock('Psr\Http\Message\ResponseInterface');

        $this->phpApiClient = m::mock(HttpClientInterface::class);

        $this->object = new RestClient(
            $this->container,
            $this->client,
            $this->tokenStorage,
            $this->serialiser,
            $this->logger,
            $this->clientSecret,
            $this->phpApiClient
        );

        $this->object->setLoggedUserId(1);
    }

    public function testLogin()
    {
        $credentialsArray = ['username' => 'u', 'password' => 'p'];
        $credentialsJson = json_encode($credentialsArray);
        $loggedUser = m::mock('App\Entity\User')
            ->shouldReceive('getId')->andReturn(1)
            ->getMock();
        $userArray = ['id' => 1, 'firstname' => 'Peter'];
        $userJson = json_encode($userArray);

        $this->serialiser->shouldReceive('serialize')->with($credentialsArray, 'json')->andReturn($credentialsJson);
        $this->serialiser->shouldReceive('deserialize')->with($userJson, 'array', 'json')->andReturn(['success' => true, 'data' => $userArray]);
        $this->serialiser->shouldReceive('deserialize')->with($userJson, 'App\Entity\User', 'json')->andReturn($loggedUser);

        $this->endpointResponse->shouldReceive('getHeader')->with('AuthToken')->andReturn([$this->sessionToken]);
        $this->endpointResponse->shouldReceive('hasHeader')->with('JWT')->andReturn(false);
        $this->endpointResponse->shouldReceive('getBody')->andReturn($userJson);

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

        $this->endpointResponse->shouldReceive('getStatusCode')->andReturn(Response::HTTP_OK);
        $this->endpointResponse->shouldReceive('getBody')->andReturn($responseJson);

        $this->tokenStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->tokenStorage->shouldReceive('get')->with('1-jwt')->once()->andReturn(false);
        $this->tokenStorage->shouldReceive('remove')->once()->with(1);

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
        $loggedUser = m::mock('App\Entity\User');

        $this->serialiser->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray);
        $this->serialiser->shouldReceive('deserialize')->with($userJson, 'App\Entity\User', 'json')->andReturn($loggedUser);

        $this->endpointResponse->shouldReceive('getStatusCode')->andReturn(Response::HTTP_OK);
        $this->endpointResponse->shouldReceive('getBody')->andReturn($responseJson);

        $this->client->shouldReceive('get')->with("user/get-by-token/{$token}", [
            'headers' => ['ClientSecret' => $this->clientSecret],
        ])->andReturn($this->endpointResponse);

        $this->assertEquals($loggedUser, $this->object->loadUserByToken($token));
    }

    public function testRegisterUser()
    {
        $this->logger->shouldReceive('error')->andReturnUsing(function ($e) {
            echo $e;
        });
        $user = m::mock('App\Entity\User');

        $data = ['id' => 1];
        $responseArray = ['success' => true, 'data' => $data];
        $responseJson = json_encode($responseArray);
        /** @var \App\Model\SelfRegisterData $selfRegData */
        $selfRegData = m::mock('App\Model\SelfRegisterData');
        $selfRegDataJson = 'selfRegData.json';

        $this->serialiser->shouldReceive('serialize')->with($selfRegData, 'json', m::any())->andReturn($selfRegDataJson);
        $this->serialiser->shouldReceive('deserialize')->with(json_encode($data), 'App\Entity\User', 'json')->andReturn($user);
        $this->serialiser->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray);

        $this->endpointResponse->shouldReceive('getStatusCode')->andReturn(Response::HTTP_CREATED);
        $this->endpointResponse->shouldReceive('getBody')->andReturn($responseJson);

        $this->client->shouldReceive('post')->with('selfregister', [
            'headers' => ['ClientSecret' => $this->clientSecret],
            'body' => $selfRegDataJson,
        ])->andReturn($this->endpointResponse);

        $this->assertEquals($user, $this->object->registerUser($selfRegData));
    }

    public function testPut()
    {
        $putData = ['id' => 1, 'field' => 'value'];
        $putDataSerialised = json_encode($putData);

        $responseData = ['b'];
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);
        $endpointUrl = '/path/to/endpoint';

        $this->serialiser->shouldReceive('serialize')->with($putData, 'json')->andReturn($putDataSerialised);
        $this->serialiser->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray);

        $this->endpointResponse->shouldReceive('getStatusCode')->andReturn(Response::HTTP_OK);
        $this->endpointResponse->shouldReceive('getBody')->andReturn($responseJson);

        $this->tokenStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->tokenStorage->shouldReceive('get')->with('1-jwt')->once()->andReturn(false);

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

        $this->serialiser->shouldReceive('serialize')->with($postData, 'json')->andReturn($postDataSerialised);
        $this->serialiser->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray);

        $this->endpointResponse->shouldReceive('getStatusCode')->andReturn(Response::HTTP_CREATED);
        $this->endpointResponse->shouldReceive('getBody')->andReturn($responseJson);

        $this->tokenStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->tokenStorage->shouldReceive('get')->with('1-jwt')->once()->andReturn(false);

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
        $jmsGroups = ['j1', 'j2'];

        $this->serialiser
            ->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray)
        ;

        $this->tokenStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->tokenStorage->shouldReceive('get')->with('1-jwt')->once()->andReturn(false);

        $this->endpointResponse->shouldReceive('getStatusCode')->andReturn(Response::HTTP_OK);
        $this->endpointResponse->shouldReceive('getBody')->andReturn($responseJson);

        $this->client->shouldReceive('get')->with($endpointUrl, [
            'headers' => ['AuthToken' => $this->sessionToken],
            'query' => ['groups' => $jmsGroups],
        ])->andReturn($this->endpointResponse);

        $this->assertEquals($responseData, $this->object->get($endpointUrl, $responseType, $jmsGroups));
    }

    public function testGetEntity()
    {
        $endpointUrl = '/path/to/endpoint';
        $expectedResponseType = 'User';
        $responseData = ['b'];
        $responseDataJson = json_encode($responseData);
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);
        $user = m::mock('App\Entity\User');

        $this->serialiser->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray);
        $this->serialiser->shouldReceive('deserialize')->with($responseDataJson, 'App\Entity\User', 'json')->andReturn($user);

        $this->tokenStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->tokenStorage->shouldReceive('get')->with('1-jwt')->once()->andReturn(false);

        $this->endpointResponse->shouldReceive('getStatusCode')->andReturn(Response::HTTP_OK);
        $this->endpointResponse->shouldReceive('getBody')->andReturn($responseJson);

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
        $user1 = m::mock('App\Entity\User');
        $user2 = m::mock('App\Entity\User');

        $user1->shouldReceive('getId')->andReturn(1);
        $user2->shouldReceive('getId')->andReturn(2);

        $this->serialiser->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray); // extractDataArray()
        $this->serialiser->shouldReceive('deserialize')->with($user1Json, 'App\Entity\User', 'json')->andReturn($user1);
        $this->serialiser->shouldReceive('deserialize')->with($user2Json, 'App\Entity\User', 'json')->andReturn($user2);

        $this->tokenStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->tokenStorage->shouldReceive('get')->with('1-jwt')->once()->andReturn(false);

        $this->endpointResponse->shouldReceive('getStatusCode')->andReturn(Response::HTTP_OK);
        $this->endpointResponse->shouldReceive('getBody')->andReturn($responseJson);

        $this->client->shouldReceive('get')->with($endpointUrl, [
            'headers' => ['AuthToken' => $this->sessionToken],
        ])->andReturn($this->endpointResponse);

        $actual = $this->object->get($endpointUrl, $expectedResponseType);

        $this->assertEquals($user1, $actual[1]);
        $this->assertEquals($user2, $actual[2]);
    }

    public function testGetNoSuccess()
    {
        $this->expectException(\App\Service\Client\Exception\NoSuccess::class);

        $endpointUrl = '/path/to/endpoint';
        $expectedResponseType = 'array';
        $responseData = ['b'];
        $responseArray = ['success' => false, 'data' => $responseData, 'message' => 'm'];
        $responseJson = json_encode($responseArray);

        $this->serialiser
            ->shouldReceive('deserialize')->with($responseJson, 'array', 'json');

        $this->tokenStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->tokenStorage->shouldReceive('get')->with('1-jwt')->once()->andReturn(false);

        $this->endpointResponse->shouldReceive('getStatusCode')->andReturn(Response::HTTP_OK);
        $this->endpointResponse->shouldReceive('getBody')->andReturn($responseJson);

        $this->client->shouldReceive('get')->with($endpointUrl, [
            'headers' => ['AuthToken' => $this->sessionToken],
        ])->andReturn($this->endpointResponse);

        $this->object->get($endpointUrl, $expectedResponseType);
    }

    public function testGetWrongExpectedType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $endpointUrl = '/path/to/endpoint';
        $expectedResponseType = 'InvalidTypeWithNonexistingClass';
        $responseData = [];
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);
        $user1 = m::mock('App\Entity\User');
        $user2 = m::mock('App\Entity\User');

        $user1->shouldReceive('getId')->andReturn(1);
        $user2->shouldReceive('getId')->andReturn(2);

        $this->serialiser
            ->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray);

        $this->tokenStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->tokenStorage->shouldReceive('get')->with('1-jwt')->once()->andReturn(false);

        $this->endpointResponse->shouldReceive('getStatusCode')->andReturn(Response::HTTP_OK);
        $this->endpointResponse->shouldReceive('getBody')->andReturn($responseJson);

        $this->client->shouldReceive('get')->with($endpointUrl, [
            'headers' => ['AuthToken' => $this->sessionToken],
        ])->andReturn($this->endpointResponse);

        $actual = $this->object->get($endpointUrl, $expectedResponseType);

        $this->assertEquals($user1, $actual[1]);
        $this->assertEquals($user2, $actual[2]);
    }

    public function testNetworkExceptionIsLoggedAndReThrown()
    {
        $this->expectException(\App\Exception\RestClientException::class);

        $endpointUrl = '/path/to/endpoint';

        $this->tokenStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->tokenStorage->shouldReceive('get')->with('1-jwt')->once()->andReturn(false);

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

        $this->tokenStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->tokenStorage->shouldReceive('get')->with('1-jwt')->once()->andReturn(false);

        $this->endpointResponse->shouldReceive('getStatusCode')->andReturn(Response::HTTP_OK);
        $this->endpointResponse->shouldReceive('getBody')->andReturn($responseJson);

        $this->client->shouldReceive('delete')->with($endpointUrl, [
            'headers' => ['AuthToken' => $this->sessionToken],
        ])->andReturn($this->endpointResponse);

        $this->assertEquals($responseData, $this->object->delete($endpointUrl));
    }

    public function testGetHistory()
    {
        $this->client = m::mock('GuzzleHttp\ClientInterface');
        $this->tokenStorage = m::mock('App\Service\Client\TokenStorage\TokenStorageInterface');
        $this->serialiser = m::mock('JMS\Serializer\SerializerInterface');
        $this->logger = m::mock('Symfony\Bridge\Monolog\Logger');
        $this->clientSecret = 'secret-123';
        $this->sessionToken = 'sessionToken347349r783';
        $this->container = m::mock('Symfony\Component\DependencyInjection\ContainerInterface');

        $this->container->shouldReceive('get')->with('jms_serializer')->andReturn($this->serialiser);
        $this->container->shouldReceive('get')->with('logger')->andReturn($this->logger);
        $this->container->shouldReceive('get')->with('request_stack')->andReturn(null);
        $this->container->shouldReceive('getParameter')->with('kernel.debug')->andReturn(true);

        $object = new RestClient(
            $this->container,
            $this->client,
            $this->tokenStorage,
            $this->serialiser,
            $this->logger,
            $this->clientSecret,
            $this->phpApiClient
        );
        $object->setLoggedUserId(1);

        $endpointUrl = '/path/to/endpoint';
        $responseData = ['bbbbb'];
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);

        $this->serialiser
            ->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray);

        $this->tokenStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->tokenStorage->shouldReceive('get')->with('1-jwt')->once()->andReturn(false);

        $this->endpointResponse->shouldReceive('getBody')->andReturn($responseJson);
        $this->endpointResponse->shouldReceive('getStatusCode')->andReturn(Response::HTTP_OK);

        $this->client->shouldReceive('delete')->with($endpointUrl, [
            'headers' => ['AuthToken' => $this->sessionToken],
        ])->andReturn($this->endpointResponse);

        $object->delete($endpointUrl);

        $actual = $object->getHistory();
        $this->assertCount(1, $actual);

        $this->assertEquals($endpointUrl, $actual[0]['url']);
        $this->assertEquals('delete', $actual[0]['method']);
        $this->assertStringContainsString($this->sessionToken, $actual[0]['options']);
        $this->assertEquals(Response::HTTP_OK, $actual[0]['responseCode']);
        $this->assertStringContainsString('bbbbb', $actual[0]['responseBody']);

        $this->assertTrue($actual[0]['time'] > 0);
        $this->assertTrue($actual[0]['time'] < 1);
    }

    public function tearDown(): void
    {
        m::close();
    }
}
