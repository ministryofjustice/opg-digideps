<?php

namespace AppBundle\Service\Client;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Exception\RequestException;
use JMS\Serializer\SerializerInterface;
use AppBundle\Service\Client\TokenStorage\TokenStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bridge\Monolog\Logger;
use AppBundle\Exception as AppException;
use AppBundle\Entity\User;
use GuzzleHttp\Message\ResponseInterface;
use AppBundle\Model\SelfRegisterData;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Connects to RESTful Server (API)
 * Perform login and logout exchanging and persist token into the given storage.
 */
class RestClient
{
    const HTTP_CODE_AUTHTOKEN_EXPIRED = 419;

    /**
     * Keep here a list of options for the methods
     * Needed on the rawSafeCall.
     * 
     * @var array
     */
    private static $availableOptions = ['addAuthToken', 'addClientSecret', 'deserialise_groups'];

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var SerializerInterface
     */
    private $serialiser;

    /**
     * Used to keep the user auth token.
     * UserId is used as a key.
     * 
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var array
     */
    private $history;

    /**
     * @var bool
     */
    private $saveHistory;

    /**
     * @var SecurityContext
     */
    private $container;

    /**
     * @var int
     */
    private $userId;

    /**
     * Header name holding auth token, returned at login time and re-sent at each requests.
     */
    const HEADER_AUTH_TOKEN = 'AuthToken';

    /**
     * Header name holding client secret, to send at login time.
     */
    const HEADER_CLIENT_SECRET = 'ClientSecret';

    /**
     * Error Messages.
     */
    const ERROR_CONNECT = 'API returned an exception.';
    const ERROR_NO_SUCCESS = 'Endpoint failed with message %s';
    const ERROR_FORMAT = 'Cannot decode endpoint response';

    public function __construct(
        ContainerInterface $container,
        ClientInterface $client,
        TokenStorageInterface $tokenStorage,
        $clientSecret
    ) {
        $this->client = $client;
        $this->container = $container;
        $this->serialiser = $container->get('jms_serializer');
        $this->tokenStorage = $tokenStorage;
        $this->logger = $container->get('logger');
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
        $user = $this->arrayToEntity('User', $this->extractDataArray($response));
        // store auth token
        $this->tokenStorage->set($user->getId(), $response->getHeader(self::HEADER_AUTH_TOKEN));

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
     * @param string $token
     *
     * @return \AppBundle\Entity\User $user
     *
     * @throws UsernameNotFoundException
     */
    public function loadUserByToken($token)
    {
        return $this->apiCall('get', 'user/get-by-token/'.$token, null, 'User', [], false);
    }

    /**
     * @param string $email
     * @param string $type
     * 
     * @return \AppBundle\Entity\User
     */
    public function userRecreateToken($email, $type)
    {
        return $this->apiCall('put', 'user/recreate-token/'.$email.'/'.$type, null, 'User', [], false);
    }

    /**
     * @param string $endpoint             e.g. /user
     * @param string $expectedResponseType Entity class to deserialise response into
     *                                     e.g. "Account" (AppBundle\Entity\ prefix not needed)
     *                                     or "Account[]" to deseialise into an array of entities
     *
     * @return mixed $expectedResponseType type
     */
    public function get($endpoint, $expectedResponseType, $jmsGroups = [])
    {
        $options = [];
        if ($jmsGroups) {
            $options['query']['groups'] = $jmsGroups;
        }

        return $this->apiCall('get', $endpoint, null, $expectedResponseType, [
                'addAuthToken' => true,
            ] + $options);
    }

    /**
     * @param string              $endpoint e.g. /user
     * @param string|object|array $mixed    HTTP body. json_encoded string or entity (that will JMS-serialised)
     * @param array               $jmsGroups  deserialise_groups
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
     * @param string        $endpoint e.g. /user
     * @param string|object $mixed    HTTP body. json_encoded string or entity (that will JMS-serialised)
     * @param array         $jmsGroups  deserialise_groups
     * 
     * @return string response body
     */
    public function post($endpoint, $mixed, array $jmsGroups = [])
    {
        $options = [];
        if ($jmsGroups) {
            $options['deserialise_groups'] = $jmsGroups;
        }

        return $this->apiCall('post', $endpoint, $mixed, 'array', $options);
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
     * @param SelfRegisterData $selfRegData
     * 
     * @return \AppBundle\Entity\User
     */
    public function registerUser(SelfRegisterData $selfRegData)
    {
        return $this->apiCall('post', 'selfregister', $selfRegData, 'User', [], false);
    }

    /**
     * @param type $method
     * @param type $endpoint
     * @param type $data
     * @param type $expectedResponseType
     * @param type $options
     *
     * @return type
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

        if ($expectedResponseType == 'raw') {
            return  $response->getBody();
        }

        if ($expectedResponseType == 'response') {
            return $response;
        }

        $responseArray = $this->extractDataArray($response);
        if ($expectedResponseType == 'array') {
            return $responseArray;
        } elseif (substr($expectedResponseType, -2) == '[]') {
            return $this->arrayToEntitities('AppBundle\\Entity\\'.$expectedResponseType, $responseArray);
        } elseif (class_exists('AppBundle\\Entity\\'.$expectedResponseType)) {
            return $this->arrayToEntity($expectedResponseType, $responseArray);
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
            $options['headers'][self::HEADER_AUTH_TOKEN] = $this->tokenStorage->get($loggedUserId);
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
        if (($request = $this->container->get('request')) && $request->headers->has('x-request-id')) {
            $options['headers']['X-Request-ID'] = $request->headers->get('x-request-id');
        }

        $start = microtime(true);
        try {
            $response = $this->client->$method($url, $options);

            $this->logRequest($url, $method, $start, $options, $response);

            return $response;
        } catch (RequestException $e) {
            // request exception contains a body, that gets decoded and passed to RestClientException
            $this->logger->warning('RestClient | Api not running ? | '.$url.' | '.$e->getMessage());

            $this->logRequest($url, $method, $start, $options, $e->getResponse());

            $data = [];

            try {
                $data = $e->getResponse() ? $this->serialiser->deserialize($e->getResponse()->getBody(), 'array', 'json') : [];
            } catch (\Exception $e) {
                $this->logger->warning('RestClient |  '.$url.' | '.$e->getMessage());
            }

            throw new AppException\RestClientException(self::ERROR_CONNECT, $e->getCode(), $data);
        } catch (TransferException $e) {
            $this->logger->warning('RestClient | '.$url.' | '.$e->getMessage());

            throw new AppException\RestClientException(self::ERROR_CONNECT, $e->getCode());
        }
    }

    /**
     * Return the 'data' array from the response.
     * 
     * @param type              $class
     * @param ResponseInterface $response
     * 
     * @return array content of "data" key from response
     */
    private function extractDataArray(ResponseInterface $response)
    {
        //TODO validate $response->getStatusCode()

        try {
            $data = $this->serialiser->deserialize($response->getBody(), 'array', 'json');
        } catch (\Exception $e) {
            $this->logger->error(__METHOD__.': '.$e->getMessage().'. Api responded with invalid JSON. Body: '.$response->getBody());
            throw new Exception\JsonDecodeException(self::ERROR_FORMAT);
        }

        if (empty($data['success'])) {
            throw new Exception\NoSuccess(sprintf(self::ERROR_NO_SUCCESS, $data['message']));
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
        $fullClassName = (strpos($class, 'AppBundle') !== false)
                 ? $class : 'AppBundle\\Entity\\'.$class;

        return $this->serialiser->deserialize(json_encode($data), $fullClassName, 'json');
    }

    /**
     * @param string $class full class name of the class to deserialise to
     * @param array  $data  "data" returned from the RESTful server
     * 
     * @return array of type $class
     */
    private function arrayToEntitities($class, array $data)
    {
        $expectedResponseType = substr($class, 0, -2);
        $ret = [];
        foreach ($data as $row) {
            $entity = $this->arrayToEntity($expectedResponseType, $row);
            $ret[$entity->getId()] = $entity;
        }

        return $ret;
    }

    /**
     * //TODO use for other calls ?
     *
     * @param string $mixed   json_encoded string or Doctrine Entity (it will be serialised before posting)
     * @param array  $options
     *
     * @return type
     */
    private function toJson($mixed, array $options = [])
    {
        $ret = $mixed;
        if (is_object($mixed)) {
            $context = \JMS\Serializer\SerializationContext::create()
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
     * @param string $url
     * @param string $method
     * @param string $start
     * @param type   $response
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
        $this->client->setDefaultOption('timeout', $timeout);

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
     * @return int
     */
    private function getLoggedUserId()
    {
        if ($this->userId) {
            return $this->userId;
        } elseif (
            ($token = $this->container->get('security.context')->getToken())
            && ($token->getUser() instanceof User)
        ) {
            return $token->getUser()->getId();
        }

        return false;
    }

    private function debugJsonString($jsonString)
    {
        echo '<pre>'.json_encode(json_decode($jsonString), JSON_PRETTY_PRINT).'</pre>';
    }
}
