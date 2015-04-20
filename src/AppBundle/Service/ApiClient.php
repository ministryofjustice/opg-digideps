<?php
namespace AppBundle\Service;

use JMS\Serializer\SerializerInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Message\RequestInterface as GuzzleRequestInterface;
use AppBundle\Exception\DisplayableException;
use RuntimeException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Security\Core\SecurityContextInterface;

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
    
    /**
     *
     * @var type 
     */
    private $securityContext;
    
     /**
     * @var string
     */
    private $acceptedFormats = ['json']; //xml should work but need to be tested first
    
    
    public function __construct(SerializerInterface $serialiser, array $options)
    {
        // check arguments
        array_map(function($k) use ($options) {
            if (!array_key_exists($k, $options)) {
                throw new \InvalidArgumentException(__METHOD__ . " missing value for $k");
            }
        }, ['base_url', 'endpoints', 'format', 'debug']);
        
        // set internal properties
        $this->serialiser = $serialiser;
        $this->format = $options['format'];
        if (!in_array($this->format, $this->acceptedFormats)) {
            throw new \InvalidArgumentException(
                __CLASS__ . ': '. $this->format . ' not valid. Accepted formats:' . implode(',', $this->acceptedFormats
            ));
        }
        $this->endpoints = $options['endpoints'];
        $this->debug = $options['debug'];
        
        // construct parent (GuzzleClient)
        parent::__construct([ 
            'base_url' =>  $options['base_url'],
            'defaults' => ['headers' => [ 'Content-Type' => 'application/' . $this->format ],
                           'verify' => false
                          ]]);
    }
   
    /**
     * @param string $class
     * @param string $endpoint
     * @param array $options
     * 
     * @return stdClass entity object
     */
    public function getEntity($class, $endpoint, array $options = [])
    {
        $responseArray = $this->deserialiseResponse($this->get($endpoint, $options));
         
        $ret = $this->serialiser->deserialize(json_encode($responseArray['data']), 'AppBundle\\Entity\\' . $class, $this->format);
       
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
        $body = (string)$e->getResponse()->getBody();
        
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
                // add debug data dependign on kernely option
                $debugData = $this->getDebugRequestExceptionData($e);
                
                // try to unserialize response
                try {
                    $responseArray = $this->serialiser->deserialize($e->getResponse()->getBody(), 'array', $this->format);
                } catch (\Exception $e) {
                    
                    throw new RuntimeException("Error from API: malformed message. " . $debugData);
                }
                
                // regognise specific error codes and launche specific exception classes
                switch ($responseArray['code']) {
                    case 404:
                        throw new DisplayableException('Record not found.' . $debugData);
                    default:
                        throw new RuntimeException($responseArray['message'] . ' ' . $debugData);
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
        
        $responseArray = $this->deserialiseResponse($this->post($endpoint, ['body'=>$body]));
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
       
        $responseArray = $this->deserialiseResponse($this->put($endpoint, ['body'=>$body]));
       
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
            
            $methods = [ 'GET', 'DELETE'];

            if(in_array($method,$methods) && array_key_exists('query', $options)){
                foreach($options['query'] as $param){
                    $url = $url.'/'.$param;
                }
                unset($options['query']);
            }
        }
        return parent::createRequest($method, $url, $options);
    }
   
}