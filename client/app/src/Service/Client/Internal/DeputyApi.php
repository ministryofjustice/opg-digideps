<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Entity\User;
use App\Service\Client\RestClient;
use App\Service\Client\RestClientInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DeputyApi
{
    private const CREATE_DEPUTY_FROM_USER_ENDPOINT = 'deputy/add';

    /** @var RestClient */
    private $restClient;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        RestClientInterface $restClient,
        TokenStorageInterface $tokenStorage
    ) {
        $this->restClient = $restClient;
        $this->tokenStorage = $tokenStorage;
    }

    public function createDeputyFromUser(User $currentUser)
    {
        return $this->restClient->post(self::CREATE_DEPUTY_FROM_USER_ENDPOINT, $currentUser);
    }
}
