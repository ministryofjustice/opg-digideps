<?php

namespace Tests\OPG\Digideps\Frontend\Unit\Service\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use JMS\Serializer\SerializerInterface;
use OPG\Digideps\Common\Registration\SelfRegisterData;
use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Exception\RestClientException;
use OPG\Digideps\Frontend\Service\Client\Exception\NoSuccess;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use OPG\Digideps\Frontend\Service\Client\TokenStorage\RedisStorage;
use OPG\Digideps\Frontend\Service\JWT\JWTService;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class RestClientTest extends TestCase
{
    private RestClient $object;
    private ClientInterface&MockObject $client;
    private SerializerInterface&MockObject $serialiser;
    private RedisStorage&MockObject $redisStorage;
    private LoggerInterface&MockObject $logger;
    private ContainerInterface&MockObject $container;
    private string $clientSecret;
    private string $sessionToken;
    private ResponseInterface&MockObject $endpointResponse;
    private JWTService $jwtService;
    private ParameterBagInterface&MockObject $parameterBag;

    public function setUp(): void
    {
        $this->client = $this->createMock(ClientInterface::class);
        $this->redisStorage = $this->createMock(RedisStorage::class);
        $this->serialiser = $this->createMock(SerializerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->clientSecret = 'secret-123';
        $this->sessionToken = 'sessionToken347349r783';
        $this->parameterBag = $this->createMock(ParameterBagInterface::class);

        $this->container = $this->createMock(ContainerInterface::class);
        $this->container->method('get')->with('jms_serializer')->willReturn($this->serialiser);
        $this->container->method('get')->with('logger')->willReturn($this->logger);

        $this->endpointResponse = $this->createMock(ResponseInterface::class);
        $this->parameterBag->method('get')->with('kernel.debug')->willReturn(false);
        $this->jwtService = $this->createMock(JWTService::class);

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

    public function testLogin(): void
    {
        $credentialsArray = ['username' => 'u', 'password' => 'p'];
        $credentialsJson = json_encode($credentialsArray);
        $loggedUser = $this->createMock(User::class)->method('getId')->willReturn(1);
        $userArray = ['id' => 1, 'firstname' => 'Peter'];
        $userJson = json_encode($userArray);

        $this->serialiser->method('serialize')->with($credentialsArray, 'json')->willReturn($credentialsJson);
        $this->serialiser->method('deserialize')->willReturnMap([
            [$userJson, User::class, 'json', null, $loggedUser],
            [$userJson, 'array', 'json', null, ['success' => true, 'data' => $userArray]],
        ]);

        $this->endpointResponse->method('getHeader')->with('AuthToken')->willReturn([$this->sessionToken]);
        $this->endpointResponse->method('hasHeader')->with('JWT')->willReturn(false);
        $this->endpointResponse->method('getBody')->willReturn(Utils::streamFor($userJson));

        $this->client
            ->method('request')->with('post', '/auth/login', [
                'body' => $credentialsJson,
                'headers' => ['ClientSecret' => $this->clientSecret],
            ])->willReturn($this->endpointResponse);

        $this->logger->expects($this->never())->method('warning');

        [$user, $authToken] = $this->object->login($credentialsArray);

        $this->assertEquals($loggedUser, $user);
        $this->assertEquals($this->sessionToken, $authToken);
    }

    public function testLogout(): void
    {
        $responseData = 'ok';
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);

        $this->endpointResponse->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $this->endpointResponse->method('getBody')->willReturn(Utils::streamFor($responseJson));

        $this->redisStorage->expects($this->exactly(2))->method('get')->willReturnMap([
            [1, $this->sessionToken],
            ['urn:opg:digideps:users:1-jwt', false],
        ]);
        $this->redisStorage->expects($this->once())->method('reset');

        $this->serialiser->method('deserialize')->with($responseJson, 'array', 'json')->willReturn($responseArray);

        $this->client
            ->method('request')->with('post', '/auth/logout', [
                'headers' => ['AuthToken' => $this->sessionToken],
            ])->willReturn($this->endpointResponse);

        $this->assertEquals($responseData, $this->object->logout());
    }

    public function testLoadUserByToken(): void
    {
        $token = 'user-token-123';
        $userArray = ['id' => 1, 'firstname' => 'Peter'];
        $userJson = json_encode($userArray);
        $responseArray = ['success' => true, 'data' => $userArray];
        $responseJson = json_encode($responseArray);
        $loggedUser = $this->createMock(User::class);

        $this->serialiser->method('deserialize')->willReturnMap([
            [$userJson, User::class, 'json', null, $loggedUser],
            [$responseJson, 'array', 'json', null, $responseArray],
        ]);

        $this->endpointResponse->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $this->endpointResponse->method('getBody')->willReturn(Utils::streamFor($responseJson));

        $this->client->method('request')->with('get', "user/get-by-token/{$token}", [
            'headers' => ['ClientSecret' => $this->clientSecret],
        ])->willReturn($this->endpointResponse);

        $this->assertEquals($loggedUser, $this->object->loadUserByToken($token));
    }

    public function testRegisterUser(): void
    {
        $this->logger->method('error')->willReturnCallback(function ($e) {
            echo $e;
        });
        $user = $this->createMock(User::class);

        $data = ['id' => 1];
        $responseArray = ['success' => true, 'data' => $data];
        $responseJson = json_encode($responseArray);
        $selfRegData = $this->createMock(SelfRegisterData::class);
        $selfRegDataJson = 'selfRegData.json';

        $this->serialiser->method('deserialize')->willReturnMap([
            [json_encode($data), User::class, 'json', null, $user],
            [$responseJson, 'array', 'json', null, $responseArray],
        ]);

        $this->serialiser->method('serialize')->with($selfRegData, 'json', new IsType(IsType::TYPE_OBJECT))->willReturn($selfRegDataJson);

        $this->endpointResponse->method('getStatusCode')->willReturn(Response::HTTP_CREATED);
        $this->endpointResponse->method('getBody')->willReturn(Utils::streamFor($responseJson));

        $this->client->method('request')->with('post', 'selfregister', [
            'headers' => ['ClientSecret' => $this->clientSecret],
            'body' => $selfRegDataJson,
        ])->willReturn($this->endpointResponse);

        $this->assertEquals($user, $this->object->registerUser($selfRegData));
    }

    public function testPut(): void
    {
        $putData = ['id' => 1, 'field' => 'value'];
        $putDataSerialised = json_encode($putData);

        $responseData = ['b'];
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);
        $endpointUrl = '/path/to/endpoint';

        $this->serialiser->method('serialize')->with($putData, 'json')->willReturn($putDataSerialised);
        $this->serialiser->method('deserialize')->with($responseJson, 'array', 'json')->willReturn($responseArray);

        $this->endpointResponse->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $this->endpointResponse->method('getBody')->willReturn(Utils::streamFor($responseJson));

        $this->redisStorage->expects($this->exactly(2))->method('get')->willReturnMap([
            [1, $this->sessionToken],
            ['urn:opg:digideps:users:1-jwt', false],
        ]);

        $this->client->method('request')->with('put', $endpointUrl, [
            'headers' => ['AuthToken' => $this->sessionToken],
            'body' => $putDataSerialised,
        ])->willReturn($this->endpointResponse);

        $this->assertEquals($responseData, $this->object->put($endpointUrl, $putData, []));
    }

    public function testPost(): void
    {
        $postData = ['id' => 1, 'field' => 'value'];
        $postDataSerialised = json_encode($postData);

        $responseData = ['b'];
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);
        $endpointUrl = '/path/to/endpoint';

        $this->serialiser->method('serialize')->with($postData, 'json')->willReturn($postDataSerialised);
        $this->serialiser->method('deserialize')->with($responseJson, 'array', 'json')->willReturn($responseArray);

        $this->endpointResponse->method('getStatusCode')->willReturn(Response::HTTP_CREATED);
        $this->endpointResponse->method('getBody')->willReturn(Utils::streamFor($responseJson));

        $this->redisStorage->expects($this->exactly(2))->method('get')->willReturnMap([
            [1, $this->sessionToken],
            ['urn:opg:digideps:users:1-jwt', false],
        ]);

        $this->client->method('request')->with('post', $endpointUrl, [
            'headers' => ['AuthToken' => $this->sessionToken],
            'body' => $postDataSerialised,
        ])->willReturn($this->endpointResponse);

        $this->assertEquals($responseData, $this->object->post($endpointUrl, $postData, []));
    }

    public function testGetArray(): void
    {
        $endpointUrl = '/path/to/endpoint';
        $responseType = 'array';
        $responseData = ['b'];
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);
        $jmsGroups = ['j1', 'j2'];

        $this->serialiser->method('deserialize')->with($responseJson, 'array', 'json')->willReturn($responseArray);

        $this->redisStorage->expects($this->exactly(2))->method('get')->willReturnMap([
            [1, $this->sessionToken],
            ['urn:opg:digideps:users:1-jwt', false],
        ]);

        $this->endpointResponse->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $this->endpointResponse->method('getBody')->willReturn(Utils::streamFor($responseJson));

        $this->client->method('request')->with('get', $endpointUrl, [
            'headers' => ['AuthToken' => $this->sessionToken],
            'query' => ['groups' => $jmsGroups],
        ])->willReturn($this->endpointResponse);

        $this->assertEquals($responseData, $this->object->get($endpointUrl, $responseType, $jmsGroups));
    }

    public function testGetEntity(): void
    {
        $endpointUrl = '/path/to/endpoint';
        $expectedResponseType = 'User';
        $responseData = ['b'];
        $responseDataJson = json_encode($responseData);
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);
        $user = $this->createMock(User::class);

        $this->serialiser->method('deserialize')->willReturnMap([
            [$responseJson, 'array', 'json', null, $responseArray],
            [$responseDataJson, User::class, 'json', null, $user],
        ]);

        $this->redisStorage->expects($this->exactly(2))->method('get')->willReturnMap([
            [1, $this->sessionToken],
            ['urn:opg:digideps:users:1-jwt', false],
        ]);

        $this->endpointResponse->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $this->endpointResponse->method('getBody')->willReturn(Utils::streamFor($responseJson));

        $this->client->method('request')->with('get', $endpointUrl, [
            'headers' => ['AuthToken' => $this->sessionToken],
        ])->willReturn($this->endpointResponse);

        $this->assertEquals($user, $this->object->get($endpointUrl, $expectedResponseType));
    }

    public function testGetEntities(): void
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
        $user1 = $this->createMock(User::class);
        $user2 = $this->createMock(User::class);

        $user1->method('getId')->willReturn(1);
        $user2->method('getId')->willReturn(2);

        $this->serialiser->method('deserialize')->willReturnMap([
            [$responseJson, 'array', 'json', null, $responseArray],
            [$user1Json, User::class, 'json', null, $user1],
            [$user2Json, User::class, 'json', null, $user2],
        ]);

        $this->redisStorage->expects($this->exactly(2))->method('get')->willReturnMap([
            [1, $this->sessionToken],
            ['urn:opg:digideps:users:1-jwt', false],
        ]);

        $this->endpointResponse->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $this->endpointResponse->method('getBody')->willReturn(Utils::streamFor($responseJson));

        $this->client->method('request')->with('get', $endpointUrl, [
            'headers' => ['AuthToken' => $this->sessionToken],
        ])->willReturn($this->endpointResponse);

        $actual = $this->object->get($endpointUrl, $expectedResponseType);

        $this->assertEquals($user1, $actual[1]);
        $this->assertEquals($user2, $actual[2]);
    }

    public function testGetNoSuccess(): void
    {
        $this->expectException(NoSuccess::class);

        $endpointUrl = '/path/to/endpoint';
        $expectedResponseType = 'array';
        $responseData = ['b'];
        $responseArray = ['success' => false, 'data' => $responseData, 'message' => 'm'];
        $responseJson = json_encode($responseArray);

        $this->serialiser
            ->method('deserialize')->with($responseJson, 'array', 'json');

        $this->redisStorage->expects($this->exactly(2))->method('get')->willReturnMap([
            [1, $this->sessionToken],
            ['urn:opg:digideps:users:1-jwt', false],
        ]);

        $this->endpointResponse->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $this->endpointResponse->method('getBody')->willReturn(Utils::streamFor($responseJson));

        $this->client->method('request')->with('get', $endpointUrl, [
            'headers' => ['AuthToken' => $this->sessionToken],
        ])->willReturn($this->endpointResponse);

        $this->object->get($endpointUrl, $expectedResponseType);
    }

    public function testGetWrongExpectedType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $endpointUrl = '/path/to/endpoint';
        $expectedResponseType = 'InvalidTypeWithNonexistingClass';
        $responseData = [];
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);
        $user1 = $this->createMock(User::class);
        $user2 = $this->createMock(User::class);

        $user1->method('getId')->willReturn(1);
        $user2->method('getId')->willReturn(2);

        $this->serialiser
            ->method('deserialize')->with($responseJson, 'array', 'json')->willReturn($responseArray);

        $this->redisStorage->expects($this->exactly(2))->method('get')->willReturnMap([
            [1, $this->sessionToken],
            ['urn:opg:digideps:users:1-jwt', false],
        ]);

        $this->endpointResponse->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $this->endpointResponse->method('getBody')->willReturn(Utils::streamFor($responseJson));

        $this->client->method('request')->with('get', $endpointUrl, [
            'headers' => ['AuthToken' => $this->sessionToken],
        ])->willReturn($this->endpointResponse);

        $actual = $this->object->get($endpointUrl, $expectedResponseType);

        $this->assertEquals($user1, $actual[1]);
        $this->assertEquals($user2, $actual[2]);
    }

    public function testNetworkExceptionIsLoggedAndReThrown(): void
    {
        $this->expectException(RestClientException::class);

        $endpointUrl = '/path/to/endpoint';

        $this->redisStorage->expects($this->exactly(2))->method('get')->willReturnMap([
            [1, $this->sessionToken],
            ['urn:opg:digideps:users:1-jwt', false],
        ]);

        $this->endpointResponse->method('getBody');
        $this->logger->expects($this->once())->method('warning');

        $this->client->method('request')->with('get', $endpointUrl, [
            'headers' => ['AuthToken' => $this->sessionToken],
        ])->willThrowException(new TransferException('network failure'));

        $this->object->get($endpointUrl, 'array');
    }

    public function testDelete(): void
    {
        $endpointUrl = '/path/to/endpoint';
        $responseData = ['b'];
        $responseArray = ['success' => true, 'data' => $responseData];
        $responseJson = json_encode($responseArray);

        $this->serialiser->method('deserialize')->with($responseJson, 'array', 'json')->willReturn($responseArray);
        $this->redisStorage->expects($this->exactly(2))->method('get')->willReturnMap([
            [1, $this->sessionToken],
            ['urn:opg:digideps:users:1-jwt', false],
        ]);

        $this->endpointResponse->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $this->endpointResponse->method('getBody')->willReturn(Utils::streamFor($responseJson));

        $this->client->method('request')->with('delete', $endpointUrl, [
            'headers' => ['AuthToken' => $this->sessionToken],
        ])->willReturn($this->endpointResponse);

        $this->assertEquals($responseData, $this->object->delete($endpointUrl));
    }

    public function testGetHistory(): void
    {
        $this->client = $this->createMock(ClientInterface::class);
        $this->redisStorage = $this->createMock(RedisStorage::class);
        $this->serialiser = $this->createMock(SerializerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->clientSecret = 'secret-123';
        $this->sessionToken = 'sessionToken347349r783';
        $this->container = $this->createMock(ContainerInterface::class);
        $this->parameterBag = $this->createMock(ParameterBagInterface::class);

        $requestStackMock = $this->createMock(RequestStack::class);
        $requestStackMock->method('getCurrentRequest')->willReturn(null);
        $this->container->method('has')->with('request_stack')->willReturn(true);

        $this->container->method('get')->willReturnMap([
            ['jms_serializer', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->serialiser],
            ['logger', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->logger],
            ['request_stack', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $requestStackMock],
        ]);

        $this->parameterBag->method('get')->with('kernel.debug')->willReturn(true);

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

        $this->serialiser->method('deserialize')->with($responseJson, 'array', 'json')->willReturn($responseArray);
        $this->redisStorage->expects($this->exactly(2))->method('get')->willReturnMap([
            [1, $this->sessionToken],
            ['urn:opg:digideps:users:1-jwt', false],
        ]);

        $this->endpointResponse->method('getBody')->willReturn(Utils::streamFor($responseJson));
        $this->endpointResponse->method('getStatusCode')->willReturn(Response::HTTP_OK);

        $this->client->method('request')->with('delete', $endpointUrl, [
            'headers' => ['AuthToken' => $this->sessionToken],
        ])->willReturn($this->endpointResponse);

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

    public function testJWTReturnedWhenSuperAdminLogsIn(): void
    {
        $client = $this->createMock(Client::class);
        $redisStorage = $this->createMock(RedisStorage::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $jwtService = $this->createMock(JWTService::class);

        $clientSecret = 'aSecret';
        $sessionToken = 'someToken123';

        $expectedLoggedInUser = $this->createMock(User::class);
        $expectedLoggedInUser->method('getId')->willReturn(1);
        $expectedLoggedInUser->method('getRolename')->willReturn('ROLE_SUPER_ADMIN');
        $userArray = ['id' => 1, 'firstname' => 'Peter'];
        $userJson = json_encode($userArray);

        $encodedJWT = 'not-real-jwt';

        $parameterBag = $this->createMock(ParameterBagInterface::class);

        $container = $this->createMock(ContainerInterface::class);

        $request = new Request();
        $request->headers->set('x-aws-request-id', 'THIS_IS_THE_REQUEST_ID');

        // Create a mock for RequestStack
        $requestStackMock = $this->createMock(RequestStack::class);
        $requestStackMock->method('getCurrentRequest')->willReturn($request);

        $container->method('has')->with('request_stack')->willReturn(true);
        $container->method('get')->with('request_stack')->willReturn($requestStackMock);
        $sut = new RestClient(
            $container,
            $client,
            $redisStorage,
            $serializer,
            $logger,
            $clientSecret,
            $parameterBag,
            $jwtService
        );

        $credentialsArray = ['username' => 'u', 'password' => 'p'];
        $credentialsJson = json_encode($credentialsArray);
        $serializer->method('serialize')->with($credentialsArray, 'json', null)->willReturn($credentialsJson);
        $serializer->method('deserialize')->willReturnMap([
            [$userJson, 'array', 'json', null, ['success' => true, 'data' => $userArray]],
            [$userJson, User::class, 'json', null, $expectedLoggedInUser],
        ]);

        $loginResponse = new GuzzleResponse(200, ['AuthToken' => $sessionToken, 'JWT' => [0 => $encodedJWT]], $userJson);

        $client->method('request')->willReturnCallback(function (string $method, string $path, array $options) use ($loginResponse, $clientSecret, $credentialsJson) {
            $this->assertSame('post', $method);
            $this->assertSame('/auth/login', $path);
            $this->assertTrue(
                isset($options['body'], $options['headers']['ClientSecret'])
                && $options['body'] === $credentialsJson
                && $options['headers']['ClientSecret'] === $clientSecret
            );
            return $loginResponse;
        });

        $jwtService->expects($this->atLeastOnce())->method('getUrn')->with($encodedJWT)->willReturn('urn:opg:digideps:users:1');

        $redisStorage->expects($this->atLeastOnce())->method('set')->with('urn:opg:digideps:users:1-jwt', $encodedJWT);

        $logger->expects($this->never())->method('warning');

        [$actualUser, $actualAuthToken] = $sut->login($credentialsArray);

        $this->assertEquals($expectedLoggedInUser, $actualUser);
        $this->assertEquals($sessionToken, $actualAuthToken);
    }
}
