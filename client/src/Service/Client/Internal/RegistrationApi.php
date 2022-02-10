<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Service\Client\TokenStorage\RedisStorage;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RegistrationApi
{
    private HttpClientInterface $registrationApiClient;
    private Security $security;
    private RedisStorage $redisStorage;

    public function __construct(HttpClientInterface $registrationApiClient, Security $security, RedisStorage $redisStorage)
    {
        $this->registrationApiClient = $registrationApiClient;
        $this->security = $security;
        $this->redisStorage = $redisStorage;
    }

    public function getMyRequestInfo()
    {
        $user = $this->security->getUser();
        $jwt = $this->redisStorage->get($user->getId().'-jwt');
        $response = $this->registrationApiClient->request('GET', '/', ['auth_bearer' => $jwt]);

        return $response->getContent();
    }
}
