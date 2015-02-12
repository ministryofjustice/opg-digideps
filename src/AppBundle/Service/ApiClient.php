<?php
namespace AppBundle\Service;

use JMS\Serializer\SerializerInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Message\RequestInterface as GuzzleRequestInterface;;
use GuzzleHttp\Exception\ServerException;

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
    private $jsonSerializer;
    
     /**
     * @var string
     */
    private $format;
    
    
    public function __construct(SerializerInterface $jsonSerializer, array $options)
    {
        // check arguments
        array_map(function($k) use ($options) {
            if (!array_key_exists($k, $options)) {
                throw new \InvalidArgumentException(__METHOD__ . " missing value for $k");
            }
        }, ['base_url', 'endpoints', 'format', 'debug']);
        
        // set internal properties
        $this->jsonSerializer = $jsonSerializer;
        $this->format = $options['format'];
        $this->endpoints = $options['endpoints'];
        $this->debug = $options['debug'];
        
        // construct parent (GuzzleClient)
        parent::__construct([ 
            'base_url' =>  $options['base_url'],
            'defaults' => ['headers' => [ 'Content-Type' => 'application/' . $this->format ] ],
         ]);
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
        $response = $this->get($endpoint, $options);
        $responseString = $response->json();
        
        if(!is_array($responseString)){
            $responseArray = $this->jsonSerializer->deserialize($response->getBody(), 'array', $this->format);
        }else{
            $responseArray = $responseString;
        }
        
        $ret = $this->jsonSerializer->deserialize(json_encode($responseArray['data']), 'AppBundle\\Entity\\' . $class, 'json');
        
        return $ret;
    }
    
    /**
     * @param ServerException $e
     * @return string
     */
    private function getDebugDataFromGuzzleServerException(ServerException $e)
    {
        $ret = [];
        
        $url = $e->getRequest()->getUrl();
        $body = (string)$e->getResponse()->getBody();
        
        $ret[] = "Url: $url";
        $ret[] = "Response body: $body";
        $ret[] = "Exception trace: " . $e->getTraceAsString();
        if ($e->getRequest()->getMethod() == 'POST') {
            $ret[] = 'Request: ' . $e->getRequest()->getBody();
        }
        
        return implode('.', $ret);
    }
    
    
    /**
     * Override Guzzleclient send() to re-throw exception using the encoded message from the API
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
            if ($e instanceof ServerException) {
                $url = $e->getRequest()->getUrl();
                $body = (string)$e->getResponse()->getBody();
                
                $message = '';
                if (empty($body)) {
                    $message = "Empty response from API.";
                } else if ($responseArray = json_decode($body, true) && empty($responseArray['success'])) {
                    $message = 'Error from API: ' . $responseArray['message'];
                } 
                
                if ($this->debug) {
                    $message .= $this->getDebugDataFromGuzzleServerException($e);
                }
                
                throw new \RuntimeException("Error from API: $message");
            }
            
            throw new \RuntimeException("Generic error from API: " . $e->getMessage());
        } 
        
    }
    
    
    /**
     * @param string $class
     * @param string $endpoint
     * @param array $options
     * 
     * @return stdClass[] array of entity objects
     */
    public function getEntities($class, $endpoint, $options = [])
    {
        $request = $this->createRequest('GET', $endpoint, $options);
        $response = $this->send($request);
        $responseString = $response->json();
        
        if(!is_array($responseString)){
            $responseArray = $this->jsonSerializer->deserialize($response->getBody(),'array',$this->format);
        }else{
            $responseArray = $responseString;
        }
        
        $ret = [];
        foreach ($responseArray['data'] as $row) { 
            $ret[] = $this->jsonSerializer->deserialize(json_encode($row), 'AppBundle\\Entity\\' . $class, 'json');
        }
        
        return $ret;
    }
    
    
    /**
     * @param string $endpoint
     * @param string $bodyorEntity json_encoded string or Doctrine Entity (it will be serialised before posting)
     * 
     * @return array response
     */
    public function postC($endpoint, $bodyorEntity)
    {
        if (is_object($bodyorEntity)) {
            $bodyorEntity = $this->jsonSerializer->serialize($bodyorEntity, 'json');
        }
        $responseBody = $this->post($endpoint, ['body'=>$bodyorEntity])->getBody();
        
        $responseArray = json_decode($responseBody, 1);
        
        return $responseArray['data'];
    }
    
    /**
     * @param string $endpoint
     * @param string $bodyorEntity json_encoded string or Doctrine Entity (it will be serialised before posting)
     * 
     * @return array response
     */
    public function putC($endpoint, $bodyorEntity)
    {
        if (is_object($bodyorEntity)) {
            $bodyorEntity = $this->jsonSerializer->serialize($bodyorEntity, 'json');
        }
        $responseBody = $this->put($endpoint, ['body'=>$bodyorEntity])->getBody();
        
        $responseArray = json_decode($responseBody, 1);
        
        return $responseArray['data'];
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
            
            if($method == 'GET' && array_key_exists('query', $options)){
                foreach($options['query'] as $param){
                    $url = $url.'/'.$param;
                }
                unset($options['query']);
            }
        }
        
        return parent::createRequest($method, $url, $options);
    }
   
}