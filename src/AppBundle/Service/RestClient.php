<?php
namespace AppBundle\Service;

use GuzzleHttp\Client as GuzzleClient;

class RestClient extends GuzzleClient
{
    private $endpoints;
    
    /**
     * Initialize guzzle and set base url
     * 
     * @param array $api
     */
    public function __construct($api)
    {
        $config = [ 'base_url' =>  $api['base_url']  ];
        parent::__construct($config);
       
        //endpoints array
        $this->endpoints = $api['endpoints'];
    }
    
    /**
     * Get Url
     * 
     * @param string $name
     * @return boolean
     */
    public function getUrl($name)
    {
        //check if this url already exist in our array map
        if(!array_key_exists($name, $this->endpoints)){
            return false;
        }
        return $this->endpoints[$name];
    }
}