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
        $endpoint = 'user/get-by-token/hybrid/' . $token;
        
        if ($this->env == 'admin') {
            $endpoint = 'user/get-by-token/admin/'. $token;
        } elseif (in_array($this->env, [ 'develop', 'staging', 'ci', 'prod'])) {
            $endpoint = 'user/get-by-token/deputy/'. $token;
        } 

        return $this->apiclient->getEntity('User', $endpoint);
    }

}