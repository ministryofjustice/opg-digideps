<?php
namespace AppBundle\Service;

use JMS\Serializer\SerializerInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Message\RequestInterface as GuzzleRequestInterface;
use AppBundle\Exception\DisplayableException;
use RuntimeException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\ResponseInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ApiClient extends GuzzleClient
{
    /**
     * endpoints map
     *
     * @var array
     */
    private $endpoints;

    /**
     * @var SerializerInterface
     */
    private $serialiser;

     /**
     * @var string
     */
    private $format;

     /**
      * If true, prints more info on exception
     * @var boolean
     */
    private $debug;

    private $session;
    
    private $redis;
    
    private $memcached;
    
    /**
     * OAuth2 Subscriber
     * 
     * @var type 
     */
    private $subscriber;
    
    private $options;


     /**
     * @var string
     */
    private $acceptedFormats = ['json']; //xml should work but need to be tested first


    public function __construct(ContainerInterface $container, array $options)
    {
        $this->serialiser = $container->get('jms_serializer');
        $this->redis = $container->get('snc_redis.default');
        $this->memcached = $container->get('oauth.memcached');
        $this->session = $container->get('session');
        
        $oauth2Client = $container->get('oauth2Client');
        $this->subscriber = $oauth2Client->getSubscriber();
        
        // check arguments
        array_map(function($k) use ($options) {
            if (!array_key_exists($k, $options)) {
                throw new \InvalidArgumentException(__METHOD__ . " missing value for $k");
            }
        }, ['base_url', 'endpoints', 'format', 'debug']);

        // set internal properties
        $this->format = $options['format'];
        if (!in_array($this->format, $this->acceptedFormats)) {
            throw new \InvalidArgumentException(
                __CLASS__ . ': '. $this->format . ' not valid. Accepted formats:' . implode(',', $this->acceptedFormats
            ));
        }
        $this->endpoints = $options['endpoints'];
        $this->debug = $options['debug'];
        $this->options = $options;
       
        //lets get session id
        $sessionId = $this->session->getId();

        //if session has not started then start it
        if(empty($sessionId)){
            $this->session->start();
        }
        
        $config = $this->getGuzzleClientConfig($oauth2Client);
        
        parent::__construct($config);
    }

    /**
     * @param string $class
     * @param string $endpoint
     * @param array $options
     *
     * @return stdClass entity object
     */
    public function getEntity($class, $endpoint, array $options = [], $debug = false)
    {

        /*if($endpoint == 'find_by_email'){
             print_r($this->get($endpoint, $options)->getBody()->getContents()); die;
        }*/
        if ($debug) {
            error_log($endpoint);
            error_log(__LINE__);
            error_log(print_r($options,1));
        }
        $response = $this->get($endpoint, $options, $debug); 
        
        if ($debug) {
            //doesn't reach this
            error_log(__LINE__);
        }
        $responseArray = $this->deserialiseResponse($response);
        $ret = $this->serialiser->deserialize(json_encode($responseArray['data']), 'AppBundle\\Entity\\' . $class, $this->format);
        if ($debug) {
            error_log(__LINE__);
        }
        return $ret;
    }

    /**
     * @param RequestException $e
     * @return string
     */
    private function getDebugRequestExceptionData(RequestException $e)
    {
        if (!$this->debug) {
            return '';
        }

        $ret = [];

        $url = $e->getRequest()->getUrl();
        $body = $e->getResponse() ? (string)$e->getResponse()->getBody() : '[No body found in response]';

        $ret[] = "Url: $url";
        $ret[] = "Response body: $body";
        $ret[] = "Exception trace: " . $e->getTraceAsString();
        if ($e->getRequest()->getMethod() == 'POST') {
            $ret[] = 'Request: ' . $e->getRequest()->getBody();
        }

        return 'Debug informations (only displayed when kernel.debug=true):' . implode(', ', $ret);
    }


    /**
     * Override send() to recognise and re-throw error messages in a more understandable format
     *
     * @param GuzzleRequestInterface $request
     *
     * @throws \RuntimeException
     */
    public function send(GuzzleRequestInterface $request)
    {
        try {
            return parent::send($request);
        } catch (\Exception $e) {

            if ($e instanceof RequestException) {

                // try to unserialize response
                $response = $e->getResponse();
                if (!$response instanceof ResponseInterface) {
                    throw new RuntimeException("No response from API. " . $this->getDebugRequestExceptionData($e));
                }
                try {
                    $responseArray = $this->serialiser->deserialize($response->getBody(), 'array', $this->format);
                } catch (\Exception $e) {
                    throw new RuntimeException("Error from API: malformed message. " . $this->getDebugRequestExceptionData($e));
                }

                // regognise specific error codes and launche specific exception classes
                
                if(!isset($responseArray['code'])){
                   $responseArray['code'] = 401;
                   //$responseArray['message'] = isset($responseArray['error_description']) ? $responseArray['error_description']: $responseArray['message'];
                   
                   if(isset($responseArray['error_description'])){
                       $responseArray['message'] = $responseArray['error_description'];
                   }elseif(!isset($responseArray['message'])){
                       $responseArray['message'] = null;
                   }
                }

                switch ($responseArray['code']) {
                    case 404:
                        throw new DisplayableException('Record not found.' . $this->getDebugRequestExceptionData($e));
                    default:
                        throw new RuntimeException($responseArray['message'] . ' ' . $this->getDebugRequestExceptionData($e));
                }
            }

            throw new RuntimeException($e->getMessage() ?: 'Generic error from API');
        }

    }

    /**
     * @param Response $response
     *
     * @return object result of deserialisation
     */
    private function deserialiseResponse($response)
    {
        try {
            $ret = $this->serialiser->deserialize($response->getBody(), 'array', $this->format);
        } catch (\JMS\Serializer\Exception\RuntimeException $e) {
            $msg = 'Cannot deserialise response.';
            if ($this->debug) {
                $msg .= 'Body:' . $response->getBody();
            }
            throw new RuntimeException(
                $e->getMessage() . '.'
                . ($this->debug ? 'Body:' . $response->getBody() : '')
            );
        }

        return $ret;
    }

    /**
     * @param string $class
     * @param string $endpoint
     * @param array $options
     *
     * @return stdClass[] array of entity objects, indexed by PK
     */
    public function getEntities($class, $endpoint, $options = [])
    {
        $responseArray = $this->deserialiseResponse($this->get($endpoint, $options));

        $ret = [];

        foreach ($responseArray['data'] as $row) {
            $entity = $this->serialiser->deserialize(json_encode($row), 'AppBundle\\Entity\\' . $class, 'json');
            $ret[$entity->getId()] = $entity;
        }

        return $ret;
    }


    /**
     * @param string $endpoint
     * @param string $bodyorEntity json_encoded string or Doctrine Entity (it will be serialised before posting)
     * @param string $options serialise group (indicated by @Groups annotation in the client entity)
     *
     * @return array response
     */
    public function postC($endpoint, $bodyorEntity, array $options = [])
    {
        $body = $this->serialiseBodyOrEntity($bodyorEntity, $options);

        if(isset($options['deserialise_group'])){
            unset($options['deserialise_group']);
        }
        $options['body'] = $body;

        $responseArray = $this->deserialiseResponse($this->post($endpoint, $options));
        return $responseArray['data'];
    }

    /**
     * @param string $endpoint
     * @param string $bodyorEntity json_encoded string or Doctrine Entity (it will be serialised before posting)
     *
     * @return array response
     */
    public function putC($endpoint, $bodyorEntity, array $options = [])
    {
        $body = $this->serialiseBodyOrEntity($bodyorEntity, $options);
        
        if(isset($options['deserialise_group'])){
            unset($options['deserialise_group']);
        }

        $options['body'] = $body;

        $responseArray = $this->deserialiseResponse($this->put($endpoint, $options));

        return $responseArray['data'];
    }

    /**
     *
     * @param string $bodyorEntity json_encoded string or Doctrine Entity (it will be serialised before posting)
     * @param array $options
     * @return type
     */
    private function serialiseBodyOrEntity($bodyorEntity, array $options)
    {
        if (is_object($bodyorEntity)) {

            $context = \JMS\Serializer\SerializationContext::create()
                    ->setSerializeNull(true);

            if (!empty($options['deserialise_group'])) {
                $context->setGroups([$options['deserialise_group']]);
            }
            return $this->serialiser->serialize($bodyorEntity, 'json', $context);
        }

        return $bodyorEntity;
    }

    /**
     * Search through our route map and if this route exists then use that
     *
     * @param string $method
     * @param string $url
     * @param array $options
     * @return type
     */
    public function createRequest($method, $url = null, array $options = array())
    {

        if (!empty($url) && array_key_exists($url, $this->endpoints)) {

            $url = $this->endpoints[$url];

            $methods = [ 'GET', 'DELETE', 'PUT', 'POST'];

            if(in_array($method,$methods) && array_key_exists('parameters', $options)){

                foreach($options['parameters'] as $param){
                    $url = $url.'/'.$param;
                }
                unset($options['parameters']);
            }
        }
        return parent::createRequest($method, $url, $options);
    }
    
    /**
     * @param type $oauth2Client
     * @return array $config
     */
    private function getGuzzleClientConfig($oauth2Client)
    {
        $config = [ 'base_url' =>  $this->options['base_url'],
                    'defaults' => ['headers' => [ 'Content-Type' => 'application/' . $this->format ],
                                   'verify' => false ]];
        
        // construct parent (GuzzleClient)
        if($this->options['use_oauth2'] && ($this->options['use_redis'] || $this->options['use_memcached'])){
            $this->updateSubscriber($oauth2Client);
            
            $config['defaults']['auth'] = 'oauth2';
            $config['defaults']['subscribers'] = [ $this->subscriber ];
        }
        
        // use HTTP 1.0 to avoid "cURL error 56: Problem (2) in the Chunked-Encoded data"
        $config['defaults']['version'] = 1.0;
        
        return $config;
    }
    
    /**
     * Update Oauth subscriber
     * 
     * @return type
     */
    private function updateSubscriber($oauth2Client)
    {
        $sessionId = $this->session->getId();
        
        if($this->options['use_redis']){
            $accessToken = unserialize($this->redis->get($sessionId.'_access_token'));
            $credentials = unserialize($this->redis->get($sessionId.'_user_credentials'));
        }elseif($this->options['use_memcached']){
            $accessToken = $this->memcached->get($sessionId.'_access_token'); 
            $credentials = $this->memcached->get($sessionId.'_user_credentials');
        }
        
        if(!empty($credentials['email']) && !empty($credentials['password']) && (empty($accessToken) || !is_object($accessToken->getRefreshToken()))){
                $oauth2Client->setUserCredentials($credentials['email'],$credentials['password']);
                $this->subscriber = $oauth2Client->getSubscriber();
         }
            
        if(empty($accessToken) || $accessToken->isExpired()){
            $newAccessToken = $this->subscriber->getAccessToken();
       
            if($this->options['use_redis']){
                $this->redis->set($this->session->getId().'_access_token',serialize($newAccessToken));   
            }elseif($this->options['use_memcached']){
                $this->memcached->set($this->session->getId().'_access_token',$newAccessToken); 
            }
            $accessToken = $newAccessToken;
        }
        $this->subscriber->setAccessToken($accessToken);
        
        return $this->subscriber;
    }
}