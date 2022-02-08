<?php

namespace App\Service\Client;

use App\Entity\User;
use App\Exception as AppException;
use App\Model\SelfRegisterData;
use App\Service\Client\TokenStorage\TokenStorageInterface;
use App\Service\RequestIdLoggerProcessor;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use InvalidArgumentException;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface as SecurityTokenStorage;
use Throwable;

/**
 * Connects to RESTful Server (API)
 * Perform login and logout exchanging and persist token into the given storage.
 */
class RestClient implements RestClientInterface
{
    const HTTP_CODE_AUTHTOKEN_EXPIRED = 419;

    /**
     * Keep here a list of options for the methods
     * Needed on the rawSafeCall.
     *
     * @var array
     */
    protected static $availableOptions = ['addAuthToken', 'addClientSecret', 'deserialise_groups'];

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var SerializerInterface
     */
    protected $serialiser;

    /**
     * Used to keep the user auth token.
     * UserId is used as a key.
     *
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var array
     */
    protected $history;

    /**
     * @var bool
     */
    protected $saveHistory;

    /**
     * @var ContainerInterface
     */
    protected $container;

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
    const HEADER_AUTH_TOKEN = 'AuthToken';

    /**
     * Header name holding client secret, to send at login time.
     */
    const HEADER_CLIENT_SECRET = 'ClientSecret';

    /**
     * Header name holding auth token, returned at login time and re-sent at each requests.
     */
    const HEADER_JWT = 'JWT';

    /**
     * Error Messages.
     */
    const ERROR_CONNECT = 'API returned an exception';
    const ERROR_NO_SUCCESS = 'Endpoint failed with message %s';
    const ERROR_FORMAT = 'Cannot decode endpoint response';

    public function __construct(
        ContainerInterface $container,
        ClientInterface $client,
        TokenStorageInterface $tokenStorage,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        string $clientSecret
    ) {
        $this->client = $client;
        $this->container = $container;
        $this->tokenStorage = $tokenStorage;
        $this->serialiser = $serializer;
        $this->logger = $logger;
        $this->clientSecret = $clientSecret;

        $this->saveHistory = $container->getParameter('kernel.debug');
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
        $response = $this->apiCall('post', '/auth/login', $credentials, 'response', [], false);

        /** @var User */
        $user = $this->arrayToEntity('User', $this->extractDataArray($response));

        // store auth token
        $tokenVal = $response->getHeader(self::HEADER_AUTH_TOKEN);
        $tokenVal = is_array($tokenVal) && !empty($tokenVal[0]) ? $tokenVal[0] : null;

        $jwt = $response->getHeader(self::HEADER_JWT);
        $jwtVal = $jwt[0] ?? null;

        $this->tokenStorage->set($user->getId(), $tokenVal);
        $this->tokenStorage->set(sprintf('%s-jwt', $user->getId()), $jwtVal);

        return $user;
    }

    /**
     * Call /auth/logout.
     */
    public function logout()
    {
        $responseArray = $this->apiCall('post', '/auth/logout', null, 'array');

        // remove AuthToken
        $this->tokenStorage->remove($this->getLoggedUserId());

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
        //TODO add $queryParams as a method param (Replace last if not used) and avoid using endpoing with query string

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

    /**
     * @param string $endpoint e.g. /user
     *
     * @return string response body
     */
    public function delete($endpoint)
    {
        return $this->apiCall('delete', $endpoint, null, 'array', [
            'addAuthToken' => true,
        ]);
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
     * @param mixed  $data
     * @param string $expectedResponseType
     * @param array  $options
     *
     * @throws InvalidArgumentException
     *
     * @return mixed
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
        } elseif (class_exists('App\\Entity\\'.$expectedResponseType)) {
            return $this->arrayToEntity($expectedResponseType, $responseArray ?: []);
        } else {
            throw new InvalidArgumentException(__METHOD__.": invalid type of expected response, $expectedResponseType given.");
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
            $options['headers'][self::HEADER_AUTH_TOKEN] = $this->tokenStorage->get($loggedUserId);
            $options['headers'][self::HEADER_JWT] = $this->tokenStorage->get(sprintf('%s-jwt', $loggedUserId));
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
                    $data = $this->serialiser->deserialize($body, 'array', 'json');
                }
            } catch (Throwable $e) {
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
        //TODO validate $response->getStatusCode()

        try {
            $data = $this->serialiser->deserialize(strval($response->getBody()), 'array', 'json');
        } catch (Throwable $e) {
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

        return $this->serialiser->deserialize($data, $fullClassName, 'json');
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
                throw new RuntimeException('Cannot deserialise entities without an ID');
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

            $ret = $this->serialiser->serialize($mixed, 'json', $context);
        } elseif (is_array($mixed)) {
            $ret = $this->serialiser->serialize($mixed, 'json');
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
            /** @var SecurityTokenStorage */
            $tokenStorage = $this->container->get('security.token_storage');
            $token = $tokenStorage->getToken();

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
