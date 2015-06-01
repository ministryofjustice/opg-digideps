<?php
namespace AppBundle\Service\OAuth\GrantType;

use CommerceGuys\Guzzle\Oauth2\GrantType\GrantTypeBase;
use CommerceGuys\Guzzle\Oauth2\AccessToken;

class UserApiKey extends GrantTypeBase
{
   protected $grantType = 'http://digideps.local';
   
   /**
     * @inheritdoc
     */
    protected function getRequired()
    {
        return array_merge(parent::getRequired(), ['password_hash']);
    }
    
    public function getToken() 
    {
        $config = $this->config->toArray();

        $body = $config;
        $body['grant_type'] = $this->grantType;
        unset($body['token_url'], $body['auth_location']);
        
        $requestOptions = [];

        if ($config['auth_location'] !== 'body') {
            $requestOptions['auth'] = [$config['client_id'], $config['client_secret']];
            unset($body['client_id'], $body['client_secret']);
        }

        $requestOptions['query'] = $body;

        if ($additionalOptions = $this->getAdditionalOptions()) {
            $requestOptions = array_merge_recursive($requestOptions, $additionalOptions);
        }
       
        $response = $this->client->get($config['token_url'], $requestOptions);
        $data = $response->json();
        
        return new AccessToken($data['access_token'], $data['token_type'], $data);
    }
}