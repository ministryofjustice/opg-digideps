<?php

namespace AppBundle\Service;

use AppBundle\Service\ApiClient;

class UserService
{
    /**
     * @var ApiClient
     */
    private $apiclient;

    /**
     * @var string $env
     */
    private $env;


    public function __construct(ApiClient $apiclient, $env)
    {
        $this->apiclient = $apiclient;
        $this->env = $env;
    }

    /**
     * Finds user by email
     * 
     * @param string $token
     * @return \AppBundle\Entity\User $user
     * @throws UsernameNotFoundException
     */
    public function loadUserByToken($token)
    {
        $endpoint = 'find_user_by_token';
        
        if ($this->env == 'admin') {
            $endpoint = 'find_user_by_token_admin';
        } elseif (in_array($this->env, [ 'develop', 'staging', 'ci', 'prod'])) {
            $endpoint = 'find_user_by_token_deputy';    
        } 

        return $this->apiclient->getEntity('User', $endpoint, [ 'parameters' => [ 'token' => $token]]);
    }

}