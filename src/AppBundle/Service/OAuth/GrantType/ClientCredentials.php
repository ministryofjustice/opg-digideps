<?php
namespace AppBundle\Service\OAuth\GrantType;

use CommerceGuys\Guzzle\Oauth2\GrantType\GrantTypeBase;
use CommerceGuys\Guzzle\Oauth2\AccessToken;

/**
 * Client credentials grant type.
 *
 * @link http://tools.ietf.org/html/rfc6749#section-4.4
 */
class ClientCredentials extends GrantTypeBase
{
    protected $grantType = 'client_credentials';
    
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
