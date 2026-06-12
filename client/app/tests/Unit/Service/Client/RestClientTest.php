<?php

namespace Tests\OPG\Digideps\Frontend\Unit\Service\Client;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use JMS\Serializer\SerializerInterface;
use Mockery as m;
use Mockery\MockInterface;
use OPG\Digideps\Common\Registration\SelfRegisterData;
use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Exception\RestClientException;
use OPG\Digideps\Frontend\Service\Client\Exception\NoSuccess;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use OPG\Digideps\Frontend\Service\Client\TokenStorage\RedisStorage;
use OPG\Digideps\Frontend\Service\JWT\JWTService;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class RestClientTest extends TestCase
{
    use ProphecyTrait;

    private RestClient $object;
    private ClientInterface&MockInterface $client;
    private SerializerInterface&MockInterface $serialiser;
    private RedisStorage&MockInterface $redisStorage;
    private LoggerInterface&MockInterface $logger;
    private ContainerInterface&MockInterface $container;
    private string $clientSecret;
    private string $sessionToken;
    private ResponseInterface&MockInterface $endpointResponse;
    private JWTService $jwtService;
    private ParameterBagInterface&MockInterface $parameterBag;

    public function setUp(): void
    {
        $this->client = m::mock(ClientInterface::class);
        $this->redisStorage = m::mock(RedisStorage::class);
        $this->serialiser = m::mock(SerializerInterface::class);
        $this->logger = m::mock(LoggerInterface::class);
        $this->clientSecret = 'secret-123';
        $this->sessionToken = 'sessionToken347349r783';
        $this->parameterBag = m::mock(ParameterBagInterface::class);

        $this->container = m::mock(ContainerInterface::class);
        $this->container->shouldReceive('get')->with('jms_serializer')->andReturn($this->serialiser);
        $this->container->shouldReceive('get')->with('logger')->andReturn($this->logger);
        $this->container->shouldIgnoreMissing();

        $this->endpointResponse = m::mock(ResponseInterface::class);
        $this->parameterBag->shouldReceive('get')->with('kernel.debug')->andReturn(false);
        $this->jwtService = m::mock(JWTService::class);

        $this->object = new RestClient(
            $this->container,
            $this->client,
            $this->redisStorage,
            $this->serialiser,
            $this->logger,
            $this->clientSecret,
            $this->parameterBag,
            $this->jwtService
        );

        $this->object->setLoggedUserId(1);
    }

    public function testLogin()
    {
        $credentialsArray = ['username' => 'u', 'password' => 'p'];
        $credentialsJson = json_encode($credentialsArray);
        $loggedUser = m::mock(User::class)
            ->shouldReceive('getId')->andReturn(1)
            ->getMock();
        $userArray = ['id' => 1, 'firstname' => 'Peter'];
        $userJson = json_encode($userArray);

        $this->serialiser->shouldReceive('serialize')->with($credentialsArray, 'json')->andReturn($credentialsJson);
        $this->serialiser->shouldReceive('deserialize')->with($userJson, 'array', 'json')->andReturn(['success' => true, 'data' => $userArray]);
        $this->serialiser->shouldReceive('deserialize')->with($userJson, User::class, 'json')->andReturn($loggedUser);

        $this->endpointResponse->shouldReceive('getHeader')->with('AuthToken')->andReturn([$this->sessionToken]);
        $this->endpointResponse->shouldReceive('hasHeader')->with('JWT')->andReturn(false);
        $this->endpointResponse->shouldReceive('getBody')->andReturn($userJson);

        $this->client
            ->shouldReceive('request')->with('post', '/auth/login', [
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
            ->shouldReceive('request')->with('post', '/auth/logout', [
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
        $loggedUser = m::mock(User::class);

        $this->serialiser->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray);
        $this->serialiser->shouldReceive('deserialize')->with($userJson, User::class, 'json')->andReturn($loggedUser);

        $this->endpointResponse->shouldReceive('getStatusCode')->andReturn(Response::HTTP_OK);
        $this->endpointResponse->shouldReceive('getBody')->andReturn($responseJson);

        $this->client->shouldReceive('request')->with('get', "user/get-by-token/{$token}", [
            'headers' => ['ClientSecret' => $this->clientSecret],
        ])->andReturn($this->endpointResponse);

        $this->assertEquals($loggedUser, $this->object->loadUserByToken($token));
    }

    public function testRegisterUser()
    {
        $this->logger->shouldReceive('error')->andReturnUsing(function ($e) {
            echo $e;
        });
        $user = m::mock(User::class);

        $data = ['id' => 1];
        $responseArray = ['success' => true, 'data' => $data];
        $responseJson = json_encode($responseArray);
        /** @var SelfRegisterData $selfRegData */
        $selfRegData = m::mock(SelfRegisterData::class);
        $selfRegDataJson = 'selfRegData.json';

        $this->serialiser->shouldReceive('serialize')->with($selfRegData, 'json', m::any())->andReturn($selfRegDataJson);
        $this->serialiser->shouldReceive('deserialize')->with(json_encode($data), User::class, 'json')->andReturn($user);
        $this->serialiser->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray);

        $this->endpointResponse->shouldReceive('getStatusCode')->andReturn(Response::HTTP_CREATED);
        $this->endpointResponse->shouldReceive('getBody')->andReturn($responseJson);

        $this->client->shouldReceive('request')->with('post', 'selfregister', [
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

        $this->client->shouldReceive('request')->with('put', $endpointUrl, [
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

        $this->client->shouldReceive('request')->with('post', $endpointUrl, [
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

        $this->client->shouldReceive('request')->with('get', $endpointUrl, [
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
        $user = m::mock(User::class);

        $this->serialiser->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray);
        $this->serialiser->shouldReceive('deserialize')->with($responseDataJson, User::class, 'json')->andReturn($user);

        $this->redisStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->redisStorage->shouldReceive('get')->with('urn:opg:digideps:users:1-jwt')->once()->andReturn(false);

        $this->endpointResponse->shouldReceive('getStatusCode')->andReturn(Response::HTTP_OK);
        $this->endpointResponse->shouldReceive('getBody')->andReturn($responseJson);

        $this->client->shouldReceive('request')->with('get', $endpointUrl, [
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
        $user1 = m::mock(User::class);
        $user2 = m::mock(User::class);

        $user1->shouldReceive('getId')->andReturn(1);
        $user2->shouldReceive('getId')->andReturn(2);

        $this->serialiser->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray); // extractDataArray()
        $this->serialiser->shouldReceive('deserialize')->with($user1Json, User::class, 'json')->andReturn($user1);
        $this->serialiser->shouldReceive('deserialize')->with($user2Json, User::class, 'json')->andReturn($user2);

        $this->redisStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->redisStorage->shouldReceive('get')->with('urn:opg:digideps:users:1-jwt')->once()->andReturn(false);

        $this->endpointResponse->shouldReceive('getStatusCode')->andReturn(Response::HTTP_OK);
        $this->endpointResponse->shouldReceive('getBody')->andReturn($responseJson);

        $this->client->shouldReceive('request')->with('get', $endpointUrl, [
            'headers' => ['AuthToken' => $this->sessionToken],
        ])->andReturn($this->endpointResponse);

        $actual = $this->object->get($endpointUrl, $expectedResponseType);

        $this->assertEquals($user1, $actual[1]);
        $this->assertEquals($user2, $actual[2]);
    }

    public function testGetNoSuccess()
    {
        $this->expectException(NoSuccess::class);

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

        $this->client->shouldReceive('request')->with('get', $endpointUrl, [
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
        $user1 = m::mock(User::class);
        $user2 = m::mock(User::class);

        $user1->shouldReceive('getId')->andReturn(1);
        $user2->shouldReceive('getId')->andReturn(2);

        $this->serialiser
            ->shouldReceive('deserialize')->with($responseJson, 'array', 'json')->andReturn($responseArray);

        $this->redisStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->redisStorage->shouldReceive('get')->with('urn:opg:digideps:users:1-jwt')->once()->andReturn(false);

        $this->endpointResponse->shouldReceive('getStatusCode')->andReturn(Response::HTTP_OK);
        $this->endpointResponse->shouldReceive('getBody')->andReturn($responseJson);

        $this->client->shouldReceive('request')->with('get', $endpointUrl, [
            'headers' => ['AuthToken' => $this->sessionToken],
        ])->andReturn($this->endpointResponse);

        $actual = $this->object->get($endpointUrl, $expectedResponseType);

        $this->assertEquals($user1, $actual[1]);
        $this->assertEquals($user2, $actual[2]);
    }

    public function testNetworkExceptionIsLoggedAndReThrown()
    {
        $this->expectException(RestClientException::class);

        $endpointUrl = '/path/to/endpoint';

        $this->redisStorage->shouldReceive('get')->with('1')->once()->andReturn($this->sessionToken);
        $this->redisStorage->shouldReceive('get')->with('urn:opg:digideps:users:1-jwt')->once()->andReturn(false);

        $this->endpointResponse
            ->shouldReceive('getBody')->andReturn('whatever');

        $this->logger
            ->shouldReceive('warning')->once();

        $this->client->shouldReceive('request')->with('get', $endpointUrl, [
            'headers' => ['AuthToken' => $this->sessionToken],
        ])->andThrow(new TransferException('network failure'));

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

        $this->client->shouldReceive('request')->with('delete', $endpointUrl, [
            'headers' => ['AuthToken' => $this->sessionToken],
        ])->andReturn($this->endpointResponse);

        $this->assertEquals($responseData, $this->object->delete($endpointUrl));
    }

    public function testGetHistory()
    {
        $this->client = m::mock(ClientInterface::class);
        $this->redisStorage = m::mock(RedisStorage::class);
        $this->serialiser = m::mock(SerializerInterface::class);
        $this->logger = m::mock(LoggerInterface::class);
        $this->clientSecret = 'secret-123';
        $this->sessionToken = 'sessionToken347349r783';
        $this->container = m::mock(ContainerInterface::class);
        $this->parameterBag = m::mock(ParameterBagInterface::class);

        $this->container->shouldReceive('get')->with('jms_serializer')->andReturn($this->serialiser);
        $this->container->shouldReceive('get')->with('logger')->andReturn($this->logger);

        $requestStackMock = m::mock(RequestStack::class);
        $requestStackMock->shouldReceive('getCurrentRequest')->andReturn(null);
        $this->container
            ->shouldReceive('has')->with('request_stack')->andReturn(true);
        $this->container
            ->shouldReceive('get')->with('request_stack')->andReturn($requestStackMock);

        $this->parameterBag->shouldReceive('get')->with('kernel.debug')->andReturn(true);

        $object = new RestClient(
            $this->container,
            $this->client,
            $this->redisStorage,
            $this->serialiser,
            $this->logger,
            $this->clientSecret,
            $this->parameterBag,
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

        $this->client->shouldReceive('request')->with('delete', $endpointUrl, [
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
        $jwtService = self::prophesize(JWTService::class);

        $clientSecret = 'aSecret';
        $sessionToken = 'someToken123';

        $expectedLoggedInUser = m::mock(User::class)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('getRolename')->andReturn('ROLE_SUPER_ADMIN')
            ->getMock();
        $userArray = ['id' => 1, 'firstname' => 'Peter'];
        $userJson = json_encode($userArray);

        $encodedJWT = 'not-real-jwt';

        $parameterBag = self::prophesize(ParameterBagInterface::class);

        $container = $this->prophesize(ContainerInterface::class);

        $request = new Request();
        $request->headers->set('x-aws-request-id', 'THIS_IS_THE_REQUEST_ID');

        // Create a mock for RequestStack
        $requestStackMock = $this->prophesize(RequestStack::class);
        $requestStackMock->getCurrentRequest()->willReturn($request);

        $container->has('request_stack')->willReturn(true);
        $container->get('request_stack')->willReturn($requestStackMock);
        $sut = new RestClient(
            $container->reveal(),
            $client->reveal(),
            $redisStorage->reveal(),
            $serializer->reveal(),
            $logger->reveal(),
            $clientSecret,
            $parameterBag->reveal(),
            $jwtService->reveal()
        );

        $credentialsArray = ['username' => 'u', 'password' => 'p'];
        $credentialsJson = json_encode($credentialsArray);
        $serializer->serialize($credentialsArray, 'json')->willReturn($credentialsJson);
        $serializer->deserialize($userJson, 'array', 'json')->willReturn(['success' => true, 'data' => $userArray]);
        $serializer->deserialize($userJson, User::class, 'json')->willReturn($expectedLoggedInUser);

        $loginResponse = new GuzzleResponse(200, ['AuthToken' => $sessionToken, 'JWT' => [0 => $encodedJWT]], $userJson);

        $client->request(
            'post',
            '/auth/login',
            Argument::that(function (array $options) use ($credentialsJson, $clientSecret) {
                // assert critical values
                return isset($options['body'], $options['headers']['ClientSecret'])
                    && $options['body'] === $credentialsJson
                    && $options['headers']['ClientSecret'] === $clientSecret;
            })
        )->willReturn($loginResponse);

        $jwtService->getUrn($encodedJWT)->shouldBeCalled()->willReturn('urn:opg:digideps:users:1');

        $redisStorage->set('urn:opg:digideps:users:1-jwt', $encodedJWT)->shouldBeCalled();

        $logger->warning(Argument::any())->shouldNotBeCalled();

        [$actualUser, $actualAuthToken] = $sut->login($credentialsArray);

        $this->assertEquals($expectedLoggedInUser, $actualUser);
        $this->assertEquals($sessionToken, $actualAuthToken);
    }

    public function tearDown(): void
    {
        m::close();
    }
}
