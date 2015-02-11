<?php
namespace AppBundle\Service;

use JMS\Serializer\SerializerInterface;
use GuzzleHttp\Client as GuzzleClient;

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
    
    
    public function __construct(SerializerInterface $jsonSerializer, $format, $api)
    {
        $config = [ 'base_url' =>  $api['base_url'],
                    'defaults' => ['headers' => [ 'Content-Type' => 'application/json' ] ],
                  ];
        
        parent::__construct($config);
         
        $this->jsonSerializer = $jsonSerializer;
        $this->format = $format;
        
        //endpoints array
        $this->endpoints = $api['endpoints'];
    }
   
    private function checkResponseArray($responseArray)
    {
         if (empty($responseArray)) {
            throw new \RuntimeException("No json response from the client. Response: ");
        }
        if (empty($responseArray['success'])) {
            throw new \Exception("The API returned an error" . $responseArray['message']);
        }
    }
    
    
    public function getEntity($class, $endpoint, $options = [])
    {
        $response = $this->get($endpoint, $options);
        $responseString = $response->json();
        
        if(!is_array($responseString)){
            $responseArray = $this->jsonSerializer->deserialize($response->getBody(), 'array', $this->format);
        }else{
            $responseArray = $responseString;
        }
        
        $this->checkResponseArray($responseArray);
        
        $ret = $this->jsonSerializer->deserialize(json_encode($responseArray['data']), 'AppBundle\\Entity\\' . $class, 'json');
        
        return $ret;
    }
    
    
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
        
        $this->checkResponseArray($responseArray);
        
        $ret = array();
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
        
         $this->checkResponseArray($responseArray);
        
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