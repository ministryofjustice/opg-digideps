<?php

namespace App\Service\Client;

use App\Entity\CourtOrder;
use App\Entity\User;
use App\Exception as AppException;
use App\Model\SelfRegisterData;
use App\Service\Client\TokenStorage\RedisStorage;
use App\Service\JWT\JWTService;
use App\Service\RequestIdLoggerProcessor;
use Firebase\JWT\JWT;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Lcobucci\JWT\Validation\ConstraintViolation;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Connects to RESTful Server (API)
 * Perform login and logout exchanging and persist token into the given storage.
 */
class RestClient implements RestClientInterface
{
    public const HTTP_CODE_AUTHTOKEN_EXPIRED = 419;

    /**
     * Keep here a list of options for the methods
     * Needed on the rawSafeCall.
     *
     * @var array
     */
    protected static $availableOptions = ['addAuthToken', 'addClientSecret', 'deserialise_groups'];

    /**
     * @var array
     */
    protected $history;

    /**
     * @var bool
     */
    protected $saveHistory;

    /**
     * @var int
     */
    protected $userId;

    /**
     * @var int set at the class level for the next request
     */
    protected $timeout = 0;

    /**
     * Header name holding auth token, returned at login time and re-sent at each requests.
     */
    public const HEADER_AUTH_TOKEN = 'AuthToken';

    /**
     * Header name holding client secret, to send at login time.
     */
    public const HEADER_CLIENT_SECRET = 'ClientSecret';

    /**
     * Header name holding auth token, returned at login time and re-sent at each requests.
     */
    public const HEADER_JWT = 'JWT';

    /**
     * Error Messages.
     */
    public const ERROR_CONNECT = 'API returned an exception';
    public const ERROR_NO_SUCCESS = 'Endpoint failed with message %s';
    public const ERROR_FORMAT = 'Cannot decode endpoint response';

    public function __construct(
        protected ContainerInterface $container,
        protected ClientInterface $client,
        protected RedisStorage $redisStorage,
        protected SerializerInterface $serializer,
        protected LoggerInterface $logger,
        protected string $clientSecret,
        protected ParameterBagInterface $params,
        protected HttpClientInterface $openInternetClient,
        protected JWTService $JWTService
    ) {
        $this->saveHistory = $params->get('kernel.debug');
        $this->history = [];
    }

    /**
     * Call /auth/login endpoints passing email and password
     * Stores AuthToken in storage
     * Returns user.
     *
     * @param array $credentials with keys "token" or "email" and "password"
     *
     * @return User
     */
    public function login(array $credentials)
    {
        try {
            $response = $this->apiCall('post', '/auth/login', $credentials, 'response', [], false);
        } catch (AppException\RestClientException $e) {
            if (423 == $e->getCode()) {
                throw new TooManyLoginAttemptsAuthenticationException($e->getData()['data']);
            } else {
                throw new BadCredentialsException('Invalid credentials.', 498);
            }
        }

        /** @var User */
        $user = $this->arrayToEntity('User', $this->extractDataArray($response));
        $authToken = $response->getHeader(RestClient::HEADER_AUTH_TOKEN)[0];

        // Temporarily scoping this to super admins until we're happy with the flow
        if ($response->hasHeader(self::HEADER_JWT) && User::ROLE_SUPER_ADMIN === $user->getRoleName()) {
            try {
                $jwt = $response->getHeader(self::HEADER_JWT)[0];
                $jwtHeaders = $this->JWTService->getJWTHeaders($jwt);

                // Get public key from API
                $jwkResponse = $this->openInternetClient->request('GET', $jwtHeaders['jku']);
                $jwks = json_decode($jwkResponse->getContent(), true);

                $decoded = $this->JWTService->decodeAndVerifyWithJWK($jwt, $jwks);
                $subjectUrn = $decoded->claims()->get('sub');

                // Move to secure cookie in next iteration
                $this->redisStorage->set(sprintf('%s-jwt', $subjectUrn), $jwt);
            } catch (ConstraintViolation $e) {
                // Swallow expired token errors for now and just log - implement once we're rolling JWT to all users
                $this->logger->warning(sprintf('JWT expired: %s', $e->getMessage()));
            } catch (\Throwable $e) {
                // Add steps for refreshing JWT if expired here
                $jwtDecodeFailureReason = sprintf('Failed to decode JWT - %s', $e->getMessage());
                $this->logger->warning($jwtDecodeFailureReason);

                throw new \RuntimeException('Problems decoding JWT - try again');
            }
        }

        return [$user, $authToken];
    }

    /**
     * Call /auth/logout.
     */
    public function logout()
    {
        $responseArray = $this->apiCall('post', '/auth/logout', null, 'array');

        // remove AuthToken
        $this->redisStorage->reset();

        return $responseArray;
    }

    /**
     * Finds user by email.
     *
     * @TODO consider replace this call with ->get() using the new last param
     *
     * @param string $token
     *
     * @return User $user
     */
    public function loadUserByToken($token)
    {
        return $this->apiCall('get', 'user/get-by-token/'.$token, null, 'User', [], false);
    }

    /**
     * @param string $token
     */
    public function agreeTermsUse($token)
    {
        $this->apiCall('put', 'user/agree-terms-use/'.$token, null, 'raw', [], false);
    }

    /**
     * @param string $endpoint             e.g. /user
     * @param string $expectedResponseType Entity class to deserialise response into
     *                                     e.g. "Account" (App\Entity\ prefix not needed)
     *                                     or "Account[]" to deseialise into an array of entities
     * @param array  $jmsGroups            deserialise_groups
     * @param array  $optionsOverride      e.g. ['addAuthToken' => false]
     *
     * @return mixed $expectedResponseType type
     */
    public function get($endpoint, $expectedResponseType, $jmsGroups = [], $optionsOverride = [])
    {
        $options = [];
        if ($jmsGroups) {
            $options['query']['groups'] = $jmsGroups;
        }

        // guzzle 6 does not append query groups and params in the string.
        // TODO add $queryParams as a method param (Replace last if not used) and avoid using endpoint with query string

        /** @var array */
        $url = parse_url($endpoint);

        if (!empty($url['query'])) {
            parse_str($url['query'], $additionalQs);
            $options['query'] = isset($options['query']) ? $options['query'] : [];
            $options['query'] += $additionalQs;
        }

        return $this->apiCall('get', $endpoint, null, $expectedResponseType, $optionsOverride + [
                'addAuthToken' => true,
            ] + $options);
    }

    /**
     * @template T of object
     * @param class-string<T> $deserializationClass
     * @return T
     */
    public function getAndDeserialize(string $endpoint, string $deserializationClass): object
    {
        /** @var array $resultArray */
        $resultArray = $this->get($endpoint, 'array');

        /** @var T $deserializedObject */
        $deserializedObject = $this->serializer->deserialize(json_encode($resultArray, JSON_THROW_ON_ERROR), $deserializationClass, 'json');

        return $deserializedObject;
    }

    /**
     * @param string              $endpoint  e.g. /user
     * @param string|object|array $mixed     HTTP body. json_encoded string or entity (that will JMS-serialised)
     * @param array               $jmsGroups deserialise_groups
     *
     * @return string response body
     */
    public function put($endpoint, $mixed, array $jmsGroups = [])
    {
        $options = [];

        if ($jmsGroups) {
            $options['deserialise_groups'] = $jmsGroups;
        }

        return $this->apiCall('put', $endpoint, $mixed, 'array', $options);
    }

    /**
     * @param string              $endpoint  e.g. /user
     * @param string|object|array $mixed     HTTP body. json_encoded string or entity (that will JMS-serialised)
     * @param array               $jmsGroups deserialise_groups
     *
     * @return string response body
     */
    public function post($endpoint, $mixed, array $jmsGroups = [], $expectedResponseType = 'array', $options = [])
    {
        if ($jmsGroups) {
            $options['deserialise_groups'] = $jmsGroups;
        }

        return $this->apiCall('post', $endpoint, $mixed, $expectedResponseType, $options);
    }

    public function delete(string $endpoint): ?array
    {
        $response = $this->rawSafeCall('delete', $endpoint, [
            'addClientSecret' => false,
            'addAuthToken' => true,
        ]);

        if (Response::HTTP_NO_CONTENT === $response->getStatusCode()) {
            return null;
        }

        return $this->extractDataArray($response);
    }

    /**
     * Call POST /selfregister passing client secret.
     *
     * @return User
     */
    public function registerUser(SelfRegisterData $selfRegData)
    {
        return $this->apiCall('post', 'selfregister', $selfRegData, 'User', [], false);
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param string $expectedResponseType
     * @param array  $options
     *
     * @throws \InvalidArgumentException
     */
    public function apiCall($method, $endpoint, $data, $expectedResponseType, $options = [], $authenticated = true)
    {
        if ($data) {
            $options['body'] = $this->toJson($data, $options);
        }

        $response = $this->rawSafeCall($method, $endpoint, $options + [
                'addClientSecret' => !$authenticated,
                'addAuthToken' => $authenticated,
            ]);
        if ('raw' == $expectedResponseType) {
            return $response->getBody();
        }

        if ('response' == $expectedResponseType) {
            return $response;
        }

        if (Response::HTTP_NO_CONTENT === $response->getStatusCode()) {
            return;
        }

        $responseArray = $this->extractDataArray($response);

        if ('array' == $expectedResponseType) {
            return $responseArray;
        } elseif ('[]' == substr($expectedResponseType, -2)) {
            return $this->arrayToEntities($expectedResponseType, $responseArray);
        } elseif (class_exists($expectedResponseType)) {
            return $this->arrayToEntity($expectedResponseType, $responseArray ?: []);
        } elseif (class_exists('App\\Entity\\'.$expectedResponseType)) {
            return $this->arrayToEntity($expectedResponseType, $responseArray ?: []);
        } else {
            throw new \InvalidArgumentException(__METHOD__.": invalid type of expected response, $expectedResponseType given.");
        }
    }

    /**
     * Performs HTTP client call
     * // TODO refactor into  rawSafeCallWithAuthToken and rawSafeCallWithClientSecret.
     *
     * In case of connect/HTTP failure:
     * - throws DisplayableException using self::ERROR_CONNECT as a message, keeping exception code
     * - logs the full error message with with warning priority
     *
     * @return ResponseInterface
     */
    private function rawSafeCall($method, $url, $options)
    {
        // add AuthToken if user is logged
        if (!empty($options['addAuthToken']) && $loggedUserId = $this->getLoggedUserId()) {
            $options['headers'][self::HEADER_AUTH_TOKEN] = $this->redisStorage->get($loggedUserId);
            $jwt = $this->redisStorage->get(sprintf('%s:%s-jwt', 'urn:opg:digideps:users', $loggedUserId));

            if ($jwt) {
                $options['headers'][self::HEADER_JWT] = $jwt;
            }
        }

        if (!empty($options['addClientSecret'])) {
            $options['headers'][self::HEADER_CLIENT_SECRET] = $this->clientSecret;
        }

        // remove internal options, not recognised by guzzle
        foreach (self::$availableOptions as $ao) {
            unset($options[$ao]);
            unset($options[$ao]);
            unset($options[$ao]);
        }

        // forward X-Request-Id to the API calls
        $reqId = RequestIdLoggerProcessor::getRequestIdFromContainer($this->container);
        if ($reqId) {
            $options['headers']['X-Request-ID'] = $reqId;
        }

        if ($this->timeout) {
            $options['timeout'] = $this->timeout;
        }

        $start = microtime(true);
        try {
            $response = $this->client->$method($url, $options);
            $this->logRequest($url, $method, $start, $options, $response);

            return $response;
        } catch (RequestException $e) {
            // request exception contains a body, that gets decoded and passed to RestClientException
            $this->logger->warning('RestClient | RequestException | '.$url.' | '.$e->getMessage());

            $response = $e->getResponse();

            $this->logRequest($url, $method, $start, $options, $response);

            $data = [];

            try {
                if ($response instanceof ResponseInterface) {
                    $body = strval($response->getBody());
                    $data = $this->serializer->deserialize($body, 'array', 'json');
                }
            } catch (\Throwable $e) {
                $this->logger->warning('RestClient |  '.$url.' | '.$e->getMessage());
            }

            throw new AppException\RestClientException($e->getMessage(), $e->getCode(), $data);
        } catch (TransferException $e) {
            $this->logger->warning('RestClient | '.$url.' | '.$e->getMessage());

            throw new AppException\RestClientException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Return the 'data' array from the response.
     *
     * @return array content of "data" key from response
     */
    private function extractDataArray(ResponseInterface $response)
    {
        // TODO validate $response->getStatusCode()

        try {
            $data = $this->serializer->deserialize(strval($response->getBody()), 'array', 'json');
        } catch (\Throwable $e) {
            $this->logger->error(__METHOD__.': '.$e->getMessage().'. Api responded with invalid JSON. [BODY START]: '.$response->getBody().'[END BODY]');
            throw new Exception\JsonDecodeException(self::ERROR_FORMAT.':'.$response->getBody());
        }

        if (empty($data['success'])) {
            throw new Exception\NoSuccess(sprintf(self::ERROR_NO_SUCCESS, $data['message'] ?? 'no message received'));
        }

        return $data['data'];
    }

    /**
     * @param string $class full class name of the class to deserialise to
     * @param array  $data  "data" returned from the RESTful server
     *
     * @return object of type $class
     */
    private function arrayToEntity($class, array $data)
    {
        $fullClassName = (str_contains($class, 'App')) ? $class : 'App\\Entity\\'.$class;

        /** @var string */
        $data = json_encode($data);

        return $this->serializer->deserialize($data, $fullClassName, 'json');
    }

    /**
     * @param string $class full class name of the class to deserialise to
     * @param array  $data  "data" returned from the RESTful server
     *
     * @return array of type $class
     */
    public function arrayToEntities(string $class, array $data)
    {
        $fullClassName = (str_contains($class, 'App')) ? $class : 'App\\Entity\\'.$class;

        $expectedResponseType = substr($fullClassName, 0, -2);
        $ret = [];
        foreach ($data as $row) {
            $entity = $this->arrayToEntity($expectedResponseType, $row);

            if (!method_exists($entity, 'getId')) {
                throw new \RuntimeException('Cannot deserialise entities without an ID');
            }

            $ret[$entity->getId()] = $entity;
        }

        return $ret;
    }

    /**
     * //TODO use for other calls ?
     *
     * @param mixed $mixed json_encoded string or Doctrine Entity (it will be serialised before posting)
     *
     * @return string
     */
    private function toJson($mixed, array $options = [])
    {
        $ret = $mixed;
        if (is_object($mixed)) {
            $context = SerializationContext::create()
                ->setSerializeNull(true);

            if (!empty($options['deserialise_groups'])) {
                $context->setGroups($options['deserialise_groups']);
            }

            if (!empty($options['deserialise_groups'])) {
                $context->setGroups($options['deserialise_groups']);
            }

            $ret = $this->serializer->serialize($mixed, 'json', $context);
        } elseif (is_array($mixed)) {
            $ret = $this->serializer->serialize($mixed, 'json');
        }

        return $ret;
    }

    /**
     * @param string            $url
     * @param string            $method
     * @param float             $start
     * @param array             $options
     * @param ResponseInterface $response
     */
    private function logRequest($url, $method, $start, $options, ResponseInterface $response = null)
    {
        if (!$this->saveHistory) {
            return;
        }

        $this->history[] = [
            'url' => $url,
            'method' => $method,
            'time' => microtime(true) - $start,
            'options' => print_r($options, true),
            'responseCode' => $response ? $response->getStatusCode() : null,
            'responseBody' => $response ? print_r(json_decode((string) $response->getBody(), true), true) : $response,
            'responseRaw' => $response ? (string) $response->getBody() : 'n.a.',
        ];
    }

    /**
     * @return array of calls, for debug reasons (e.g. symfony debug toolbar)
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @param int $timeout in seconds
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @param int $userId
     */
    public function setLoggedUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return int|bool
     */
    private function getLoggedUserId()
    {
        if ($this->userId) {
            return $this->userId;
        } else {
            $token = $this->redisStorage->getToken();

            if (!is_null($token)) {
                $user = $token->getUser();

                if ($user instanceof User) {
                    return $user->getId();
                }
            }
        }

        return false;
    }

    private function debugJsonString($jsonString)
    {
        echo '<pre>'.json_encode(json_decode($jsonString), JSON_PRETTY_PRINT).'</pre>';
    }
}
