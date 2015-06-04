<?php
namespace AppBundle\Service\OAuth;

use GuzzleHttp\Client;
use CommerceGuys\Guzzle\Oauth2\GrantType\RefreshToken;
use CommerceGuys\Guzzle\Oauth2\Oauth2Subscriber;
use AppBundle\Service\OAuth\GrantType as GrantTypeDir;

class OAuth2 
{
    /**
     *
     * @var \CommerceGuys\Guzzle\Oauth2\Oauth2Subscriber $oauth2Subscriber
     */
    private $oauth2Subscriber;
    
    /**
     *
     * @var \GuzzleHttp\Client $oauth2Client
     */
    private $oauth2Client;
    
    /**
     *
     * @var array $config
     */
    private $config;
    
    
    /**
     * 
     * @param integer $clientId
     * @param string  $clientSecret
     */
    public function __construct($baseUrl,$clientId,$clientSecret) 
    {
        $this->oauth2Client = new Client(['base_url' => $baseUrl,
                                          'defaults' => [ 'verify' => false,
                                                          'headers' => [ 'Content-Type' => 'application/json']
                                          ]]);
        
        $this->config = [ 'client_id' => $clientId,
                          'client_secret' => $clientSecret,
                          'token_url' => 'oauth/v2/token'
                        ];
        
        $token = new GrantTypeDir\ClientCredentials($this->oauth2Client, $this->config);
        $refreshToken = new RefreshToken($this->oauth2Client, $this->config);
        
        $this->oauth2Subscriber = new Oauth2Subscriber($token,$refreshToken);
    }
    
    /**
     * Authenticate based on api key
     * 
     * @param string $userApiKey
     * @return \AppBundle\Service\OAuth\OAuth2
     */
    public function setUserApiKey($userApiKey)
    {
       $this->config['password_hash'] = $userApiKey;

        $token = new GrantTypeDir\UserApiKey($this->oauth2Client, $this->config);
        $refreshToken = new RefreshToken($this->oauth2Client, $this->config);

        $this->oauth2Subscriber = new Oauth2Subscriber($token,$refreshToken);
        
        return $this;
    }
    
    /**
     * 
     * @return type
     */
    public function getSubscriber()
    {
        return $this->oauth2Subscriber;
    }
    
}