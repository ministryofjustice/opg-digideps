<?php
namespace AppBundle\Service;

use GuzzleHttp\Client as GuzzleClient;

class RestClient extends GuzzleClient
{
    /**
     * endpoints map
     * 
     * @var array
     */
    private $endpoints;
    
    /**
     * Initialize guzzle and set base url
     * 
     * @param array $api
     */
    public function __construct($api)
    {
        $config = [ 'base_url' =>  $api['base_url'],
                    'defaults' => ['headers' => [ 'Content-Type' => 'application/json' ] ],
                  ];
        
        parent::__construct($config);
       
        //endpoints array
        $this->endpoints = $api['endpoints'];
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
        if(!empty($url) && array_key_exists($url, $this->endpoints)){
            $url = $this->endpoints[$url];
            
            if(array_key_exists('query', $options)){
                foreach($options['query'] as $param){
                    $url = $url.'/'.$param;
                }
                unset($options['query']);
            }
        }
        return parent::createRequest($method, $url, $options);
    }
}