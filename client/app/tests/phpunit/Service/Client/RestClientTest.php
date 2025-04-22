<?php

namespace DigidepsTests\Service\Client;

use App\Service\Client\RestClient;
use App\Service\Client\TokenStorage\RedisStorage;
use App\Service\JWT\JWTService;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use JMS\Serializer\SerializerInterface;
use Lcobucci\JWT\Token;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RestClientTest extends TestCase
{
    use ProphecyTrait;

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
     * @var RedisStorage|MockInterface
     */
    private $redisStorage;

    /**
     * @var LoggerInterface|MockInterface
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
    private $openInternetClient;

    /**
     * @var HttpClientInterface|MockInterface
     */
    private $jwtService;

    private m\LegacyMockInterface|ParameterBagInterface|MockInterface $parameterBag;

    public function setUp(): void
    {
        $this->client = m::mock('GuzzleHttp\ClientInterface');
        $this->redisStorage = m::mock(RedisStorage::class);
        $this->serialiser = m::mock('JMS\Serializer\SerializerInterface');
        $this->logger = m::mock('Psr\Log\LoggerInterface');
        $this->clientSecret = 'secret-123';
        $this->sessionToken = 'sessionToken347349r783';
        $this->parameterBag = m::mock(ParameterBagInterface::class);

        $this->container = m::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->container->shouldReceive('get')->with('jms_serializer')->andReturn($this->serialiser);
        $this->container->shouldReceive('get')->with('logger')->andReturn($this->logger);
        $this->container->shouldIgnoreMissing();

        $this->parameterBag->shouldReceive('get')->with('kernel.debug')->andReturn(false);

        $this->endpointResponse = m::mock('Psr\Http\Message\ResponseInterface');

        $this->openInternetClient = m::mock(HttpClientInterface::class);
        $this->jwtService = m::mock(JWTService::class);

        $this->object = new RestClient(
            $this->container,
            $this->client,
            $this->redisStorage,
            $this->serialiser,
            $this->logger,
            $this->clientSecret,
            $this->parameterBag,
            $this->openInternetClient,
            $this->jwtService
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

        $this->logger
            ->shouldReceive('warning')->never();

        [$user, $authToken] = $this->object->login($credentialsArray);

        $this->assertEquals($loggedUser, $user);
        $this->assertEquals($this->sessionToken, $authToken);
    }

    public function testLogout()
    {
        $responseData = 'ok';
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);

        $this->endpointResponse->shouldReceive('getStatusCode')->andReturn(Response::HTTP_OK);
        $this->endpointResponse->shouldReceive('getBody')->andReturn($responseJson);

        $this->redisStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->redisStorage->shouldReceive('get')->with('urn:opg:digideps:users:1-jwt')->once()->andReturn(false);
        $this->redisStorage->shouldReceive('reset')->once();

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

        $this->redisStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->redisStorage->shouldReceive('get')->with('urn:opg:digideps:users:1-jwt')->once()->andReturn(false);

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

        $this->redisStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->redisStorage->shouldReceive('get')->with('urn:opg:digideps:users:1-jwt')->once()->andReturn(false);

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

        $this->redisStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->redisStorage->shouldReceive('get')->with('urn:opg:digideps:users:1-jwt')->once()->andReturn(false);

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

        $this->redisStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->redisStorage->shouldReceive('get')->with('urn:opg:digideps:users:1-jwt')->once()->andReturn(false);

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

        $this->redisStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->redisStorage->shouldReceive('get')->with('urn:opg:digideps:users:1-jwt')->once()->andReturn(false);

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

        $this->redisStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->redisStorage->shouldReceive('get')->with('urn:opg:digideps:users:1-jwt')->once()->andReturn(false);

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

        $this->redisStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->redisStorage->shouldReceive('get')->with('urn:opg:digideps:users:1-jwt')->once()->andReturn(false);

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

        $this->redisStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->redisStorage->shouldReceive('get')->with('urn:opg:digideps:users:1-jwt')->once()->andReturn(false);

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

        $this->redisStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->redisStorage->shouldReceive('get')->with('urn:opg:digideps:users:1-jwt')->once()->andReturn(false);

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
        $this->redisStorage = m::mock(RedisStorage::class);
        $this->serialiser = m::mock('JMS\Serializer\SerializerInterface');
        $this->logger = m::mock('Psr\Log\LoggerInterface');
        $this->clientSecret = 'secret-123';
        $this->sessionToken = 'sessionToken347349r783';
        $this->container = m::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->parameterBag = m::mock(ParameterBagInterface::class);

        $this->container->shouldReceive('get')->with('jms_serializer')->andReturn($this->serialiser);
        $this->container->shouldReceive('get')->with('logger')->andReturn($this->logger);
        $this->container->shouldReceive('get')->with('request_stack')->andReturn(null);
        $this->parameterBag->shouldReceive('get')->with('kernel.debug')->andReturn(true);

        $object = new RestClient(
            $this->container,
            $this->client,
            $this->redisStorage,
            $this->serialiser,
            $this->logger,
            $this->clientSecret,
            $this->parameterBag,
            $this->openInternetClient,
            $this->jwtService
        );
        $object->setLoggedUserId(1);

        $endpointUrl = '/path/to/endpoint';
        $responseData = ['bbbbb'];
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);

        $this->serialiser
            ->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray);

        $this->redisStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->redisStorage->shouldReceive('get')->with('urn:opg:digideps:users:1-jwt')->once()->andReturn(false);

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

    public function testJWTReturnedWhenSuperAdminLogsIn()
    {
        $client = self::prophesize(Client::class);
        $redisStorage = self::prophesize(RedisStorage::class);
        $serializer = self::prophesize(SerializerInterface::class);
        $logger = self::prophesize(LoggerInterface::class);
        $container = self::prophesize(ContainerInterface::class);
        $jwtService = self::prophesize(JWTService::class);

        $clientSecret = 'aSecret';
        $sessionToken = 'someToken123';

        $expectedLoggedInUser = m::mock('App\Entity\User')
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('getRolename')->andReturn('ROLE_SUPER_ADMIN')
            ->getMock();
        $userArray = ['id' => 1, 'firstname' => 'Peter'];
        $userJson = json_encode($userArray);

        [$jwks, $jwtHeaders, $jwtClaims] = $this->generateValidJwtJwkArrays();

        $encodedJWT = JWTService::base64EncodeJWT($jwtHeaders, $jwtClaims);

        $mockResponseJson = json_encode($jwks, JSON_THROW_ON_ERROR);
        $mockResponse = new MockResponse($mockResponseJson, [
            'http_code' => 200,
            'response_headers' => ['Content-Type: application/json'],
        ]);

        $parameterBag = self::prophesize(ParameterBagInterface::class);

        $openInternetClient = new MockHttpClient($mockResponse);

        $sut = new RestClient(
            $container->reveal(),
            $client->reveal(),
            $redisStorage->reveal(),
            $serializer->reveal(),
            $logger->reveal(),
            $clientSecret,
            $parameterBag->reveal(),
            $openInternetClient,
            $jwtService->reveal()
        );

        $credentialsArray = ['username' => 'u', 'password' => 'p'];
        $credentialsJson = json_encode($credentialsArray);
        $serializer->serialize($credentialsArray, 'json')->willReturn($credentialsJson);
        $serializer->deserialize($userJson, 'array', 'json')->willReturn(['success' => true, 'data' => $userArray]);
        $serializer->deserialize($userJson, 'App\Entity\User', 'json')->willReturn($expectedLoggedInUser);

        $loginResponse = new GuzzleResponse(200, ['AuthToken' => $sessionToken, 'JWT' => [0 => $encodedJWT]], $userJson);

        $client->post('/auth/login', [
            'body' => $credentialsJson,
            'headers' => ['ClientSecret' => $clientSecret],
        ])->willReturn($loginResponse);

        $redisStorage->set('urn:opg:digideps:users:1-jwt', $encodedJWT)->shouldBeCalled();

        $jwtService->getJWTHeaders($encodedJWT)->shouldBeCalled()->willReturn($jwtHeaders);
        $jwtService->decodeAndVerifyWithJWK($encodedJWT, $jwks)->shouldBeCalled()->willReturn(
            new Token\Plain(
                new Token\DataSet([], ''),
                new Token\DataSet($jwtClaims, ''),
                new Token\Signature('', '')
            )
        );

        $logger->warning(Argument::any())->shouldNotBeCalled();

        [$actualUser, $actualAuthToken] = $sut->login($credentialsArray);

        $this->assertEquals($expectedLoggedInUser, $actualUser);
        $this->assertEquals($sessionToken, $actualAuthToken);
    }

    private function generateValidJwtJwkArrays()
    {
        $jwks = [
            'keys' => [
                0 => [
                    'kty' => 'RSA',
                    'n' => 'wxzA2VTIuogiRQT1DVPYrBc4GZmS5eR6UXawTXCWB8vXKT-2TXRcb8r5esVmzOspqpU7k9jFEhI-upEx15Ok7VG7kAuvJ8k17PV4iJryw14YIwWet7hVFkVzlFn_yUVULwOXsCn6bZi3ZKbV4C9p5xtyB1QiZkoEVzvtp88r_T1f9kA1a8lIeTFrrVV-xV6kReCUSu9Ctlx-Ev6Gi66siW_81_5hV-BvUmzFskVAca6O92EKxTW764EoIxWGZYJ2v1j-eZkGk2-OdsFY5OdIqPEo8Hm0U5KwsY5CsDOpHPVEMJnQLFBJuq7bHve-DqUtl2QcJnDUcDKUnXuqKGJ-HQ',
                    'e' => 'AQAB',
                    'kid' => '45ed51b79f00b11d47100b9cc7092ef2819da72df0fc0be8f89824a779973bc0',
                    'alg' => 'RS256',
                    'use' => 'sig',
                ],
            ],
        ];

        $jwtHeaders = [
            'jku' => 'https://digideps.local/v2/.well-known/jwks.json',
            'typ' => 'JWT',
            'alg' => 'RS256',
            'kid' => '45ed51b79f00b11d47100b9cc7092ef2819da72df0fc0be8f89824a779973bc0',
        ];

        $jwtClaims = [
            'aud' => 'registration_service',
            'iat' => strtotime('now'),
            'exp' => strtotime('+1 hour'),
            'nbf' => strtotime('-10 seconds'),
            'iss' => 'digideps',
            'sub' => 'urn:opg:digideps:users:1',
            'role' => 'ROLE_SUPER_ADMIN',
        ];

        return [$jwks, $jwtHeaders, $jwtClaims];
    }

    public function tearDown(): void
    {
        m::close();
    }
}
