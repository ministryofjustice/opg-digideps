<?php

namespace AppBundle\Service\Client;

use GuzzleHttp\ClientInterface;
use JMS\Serializer\SerializerInterface;
use AppBundle\Service\Client\TokenStorage\TokenStorageInterface;
use GuzzleHttp\Message\Response as GuzzleResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bridge\Monolog\Logger;
use AppBundle\Exception\DisplayableException;
use AppBundle\Entity\User;
use GuzzleHttp\Message\ResponseInterface;
use AppBundle\Model\SelfRegisterData;

/**
 * Connects to RESTful Server (API)
 * Perform login and logout exchanging and persist token into the given storage
 * 
 */
class RestClient
{
    const HTTP_CODE_AUTHTOKEN_EXPIRED = 419;
    
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

    /**
     * @var string
     */
    private $clientSecret;


    /**
     * Header name holding auth token, returned at login time and re-sent at each requests
     */
    const HEADER_AUTH_TOKEN = 'AuthToken';

    /**
     * Header name holding client secret, to send at login time
     */
    const HEADER_CLIENT_SECRET = 'ClientSecret';

    /**
     * Error Messages
     */
    const ERROR_CONNECT = 'API not available.';
    const ERROR_FORMAT = 'Cannot decode message.';


    public function __construct(
        ClientInterface $client, 
        TokenStorageInterface $tokenStorage, 
        SerializerInterface $serialiser, 
        Logger $logger, 
        $clientSecret
    ) {
        $this->client = $client;
        $this->serialiser = $serialiser;
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
        $this->clientSecret = $clientSecret;
    }


    /**
     * Call /auth/login endpoints passing email and password
     * Stores AuthToken in storage
     * Returns user
     * 
     * @param array $credentials with keys "token" or "email" and "password"
     * 
     * @return User
     */
    public function login(array $credentials)
    {
        $response = $this->rawSafeCall('post', '/auth/login', [
            'body' => $this->toJson($credentials),
            'addClientSecret' => true,
        ]);

        $this->tokenStorage->set($response->getHeader(self::HEADER_AUTH_TOKEN));

        return $this->entityToArray('User', $this->extractDataArray($response));
    }


    /**
     * Call /auth/logout
     */
    public function logout()
    {
        $response = $this->rawSafeCall('post', '/auth/logout', [
            'addAuthToken' => true,
        ]);

        return $this->extractDataArray($response);
    }

    
    /**
     * Finds user by email
     * 
     * @param string $token
     * @return \AppBundle\Entity\User $user
     * @throws UsernameNotFoundException
     */
    public function loadUserByToken($token)
    {
        $response = $this->rawSafeCall('get', 'user/get-by-token/' . $token, [
            'addAuthToken' => false,
            'addClientSecret' => true,
        ]);
        
        $responseArray = $this->extractDataArray($response);
        
        return $this->entityToArray('User', $responseArray);
    }

    /**
     * @param User $user
     * @param string $type
     * 
     * @return array
     */
    public function userRecreateToken(User $user, $type)
    {
        $response = $this->rawSafeCall('put', 'user/recreate-token/' .  $user->getEmail() . '/' . $type, [
            'addAuthToken' => false, 
            'addClientSecret' => true,
        ]);
        
        return $this->extractDataArray($response);
    }
    
    
    /**
     * Call POST /selfregister passing client secret
     * 
     * @param SelfRegisterData $selfRegData
     * 
     * @return array
     */
    public function registerUser(SelfRegisterData $selfRegData)
    {
        $response = $this->rawSafeCall('post', 'selfregister', [
            'addAuthToken' => false, 
            'addClientSecret' => true,
            'body' => $this->toJson($selfRegData)
        ]);
        
        return $this->extractDataArray($response);
    }
    
    /**
     * @param string $endpoint e.g. /user
     * @param string|object $bodyorEntity HTTP body. json_encoded string or entity (that will JMS-serialised)
     * @param array $options keys: deserialise_group
     * 
     * @return string response body
     */
    public function put($endpoint, $bodyorEntity, array $options = [])
    {
        $body = $this->toJson($bodyorEntity, $options);
        $response = $this->rawSafeCall('put', $endpoint, [
            'body' => $body,
            'addAuthToken' => true,
        ]);

        return $this->extractDataArray($response);
    }

    
    /**
     * @param string $endpoint e.g. /user
     * @param string|object $bodyorEntity HTTP body. json_encoded string or entity (that will JMS-serialised)
     * @param array $options keys: deserialise_group
     * 
     * @return string response body
     */
    public function post($endpoint, $bodyorEntity, array $options = [])
    {
        $body = $this->toJson($bodyorEntity, $options);

        $response = $this->rawSafeCall('post', $endpoint, [
            'body' => $body,
            'addAuthToken' => true,
        ]);

        return $this->extractDataArray($response);
    }


    /**
     * @param string $endpoint e.g. /user
     * @param string $expectedResponseType Entity class to deserialise response into 
     *                e.g. "Account" (AppBundle\Entity\ prefix not needed) 
     *                or "Account[]" to deseialise into an array of entities
     * @return mixed $expectedResponseType type
     */
    public function get($endpoint, $expectedResponseType, array $options = [])
    {
        $response = $this->rawSafeCall('get', $endpoint, [
            'addAuthToken' => true,
        ]);

        $responseArray = $this->extractDataArray($response);
        if ($expectedResponseType == 'array') {
            return $responseArray;
        } else if (substr($expectedResponseType, -2) == '[]') {
            return $this->entitiesToArray('AppBundle\\Entity\\' . $expectedResponseType, $responseArray);
        } else if (class_exists('AppBundle\\Entity\\' . $expectedResponseType)) {
            return $this->entityToArray($expectedResponseType, $responseArray);
        } else {
            throw new \InvalidArgumentException(__METHOD__ . ": invalid type of expected response, $expectedResponseType given.");
        }

        return $responseArray;
    }

    
    /**
     * @param string $endpoint e.g. /user
     * 
     * @return string response body
     */
    public function delete($endpoint, array $options = [])
    {
        $response = $this->rawSafeCall('delete', $endpoint, [
           'addAuthToken' => true,
        ]);

        return $this->extractDataArray($response);
    }


    /**
     * Performs HTTP client call
     * // TODO refactor into  rawSafeCallWithAuthToken and rawSafeCallWithClientSecret
     * 
     * In case of connect/HTTP failure:
     * - throws DisplayableException using self::ERROR_CONNECT as a message, keeping exception code
     * - logs the full error message with with warning priority
     * 
     * @return ResponseInterface
     */
    private function rawSafeCall($method, $url, $options)
    {
        if (!method_exists($this->client, $method)) {
            throw new \InvalidArgumentException("Method $method does not exist on " . get_class($this->client));
        }

        // process special header options
        if (!empty($options['addAuthToken'])) {
            $options['headers'][self::HEADER_AUTH_TOKEN] = $this->tokenStorage->get();
        }
        unset($options['addAuthToken']);
        
        if (!empty($options['addClientSecret'])) {
            $options['headers'][self::HEADER_CLIENT_SECRET] = $this->clientSecret;
        }
        unset($options['addClientSecret']);
        
        
        try {
            return $this->client->$method($url, $options);
        } catch (\Exception $e) {
            $this->logger->warning('RestClient | ' . $url . ' | ' . $e->getMessage());
            throw new DisplayableException(self::ERROR_CONNECT, $e->getCode());
        }
    }


    /**
     * Return the 'data' array from the response
     * 
     * @param type $class
     * @param ResponseInterface $response
     * 
     * @return array content of "data" key from response
     */
    private function extractDataArray(ResponseInterface $response)
    {
        //TODO validate $response->getStatusCode()

        $data = $this->serialiser->deserialize($response->getBody(), 'array', 'json');
        if (empty($data['success'])) {
            throw new \RuntimeException('Endpoint failed with message.' . $data['message']);
        }

        return $data['data'];
    }


    /**
     * @param string $class full class name of the class to deserialise to
     * @param array $data "data" returned from the RESTful server
     * 
     * @return Object of type $class
     */
    private function entityToArray($class, array $data)
    {
        $class = (strpos($class, 'AppBundle') !== false) 
                 ? $class : 'AppBundle\\Entity\\' . $class;
        
        return $this->serialiser->deserialize(json_encode($data), $class, 'json');
    }


    /**
     * @param string $class full class name of the class to deserialise to
     * @param array $data "data" returned from the RESTful server
     * 
     * @return array of type $class
     */
    private function entitiesToArray($class, array $data)
    {
        $expectedResponseType = substr($class, 0, -2);
        $ret = [];
        foreach ($data as $row) {
            $entity = $this->entityToArray($expectedResponseType, $row);
            $ret[$entity->getId()] = $entity;
        }

        return $ret;
    }


    /**
     * //TODO use for other calls ?
     * @param string $mixed json_encoded string or Doctrine Entity (it will be serialised before posting)
     * @param array $options
     * @return type
     */
    private function toJson($mixed, array $options = [])
    {
        if (is_object($mixed)) {

            $context = \JMS\Serializer\SerializationContext::create()
                ->setSerializeNull(true);

            if (!empty($options['deserialise_group'])) {
                $context->setGroups([$options['deserialise_group']]);
            }
            return $this->serialiser->serialize($mixed, 'json', $context);
        } else if (is_array($mixed)) {
            return $this->serialiser->serialize($mixed, 'json');
        }

        return $mixed;
    }


    /**
     * @return array of calls, for debug reasons (e.g. symfony debug toolbar)
     */
    public function getHistory()
    {
        return [];
    }

}