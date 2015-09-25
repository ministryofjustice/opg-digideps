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

/**
 * Connects to RESTful Server (API)
 * Perform login and logout exchanging and persist token into the given storage
 * 
 */
class RestClient
{
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
    
    const HEADER_AUTH_TOKEN = 'AuthToken';
    const HEADER_CLIENT_SECRET = 'ClientSecret';
    
    const STORAGE_KEY = 'token';
    const FORMAT = 'json';
    const ERROR_CONNECT = 'API not available.';
    const ERROR_FORMAT = 'Cannot decode message.';
    
    
    public function __construct(ContainerInterface $container, $clientSecret) {
        $this->client = $container->get('guzzleJsonHttpClient');
        $this->serialiser = $container->get('jms_serializer');
        $this->tokenStorage = $container->get('redisTokenStorage');
        $this->logger = $container->get('logger');
        $this->clientSecret = $clientSecret;
    }
    
    /**
     * Call /auth/login endpoints passing email and password
     * Stores AuthToken in redis
     * 
     * @param string $email
     * @param string $password
     * 
     * @return User
     */
    public function login($email, $password)
    {
        $response = $this->safeClientCall('post', '/auth/login', [
            'body' => $this->serialiser->serialize(['email' => $email, 'password' => $password], self::FORMAT),
            'headers' => [
                self::HEADER_CLIENT_SECRET => $this->clientSecret
            ]
        ]);

        $this->tokenStorage->set(self::STORAGE_KEY, $response->getHeader(self::HEADER_AUTH_TOKEN));
        
        return $this->dataToEntity('AppBundle\Entity\User', $this->responseToData($response));
    }
    
    public function logout()
    {
        $this->safeClientCall('post', '/auth/logout', [
            'headers' => [
                self::HEADER_AUTH_TOKEN => $this->tokenStorage->get(self::STORAGE_KEY)
            ]
        ]);
    }
    
    /**
     * Performs HTTP client call
     * 
     * In case of connect/HTTP failure:
     * - throws DisplayableException using self::ERROR_CONNECT as a message
     * - logs the full error message with with emergency priority
     */
    private function safeClientCall($method, $url, $options)
    {
        if (!method_exists($this->client, $method)) {
            throw new \InvalidArgumentException("Method $method does not exist on " . get_class($this->client));
        }
        try {
            return $this->client->$method($url, $options);
        } catch (\Exception $e) {
            $this->logger->emergency('RestClient | ' . $url .' | '.$e->getMessage());
            throw new DisplayableException(self::ERROR_CONNECT);
        }
    }
    
    /**
     * Return th4 array 
     * 
     * @param type $class
     * @param GuzzleResponse $response
     */
    private function responseToData(GuzzleResponse $response)
    {
        //TODO validate $response->getStatusCode()
        
        $data = $this->serialiser->deserialize($response->getBody(), 'array', self::FORMAT);
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
    private function dataToEntity($class, array $data)
    {
        return $this->serialiser->deserialize(json_encode($data), $class, self::FORMAT);
    }
    
    /**
     * @param string $class full class name of the class to deserialise to
     * @param array $data "data" returned from the RESTful server
     * 
     * @return array of Objects of type $class
     */
    public function dataToEntities($class, array $data)
    {
        $ret = [];

        foreach ($data as $row) {
            $entity = $this->serialiser->deserialize(json_encode($row), $class, self::FORMAT);
            $ret[$entity->getId()] = $entity;
        }

        return $ret;
    }

}