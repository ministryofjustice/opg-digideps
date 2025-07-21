<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Entity\User;
use App\Service\Client\RestClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DeputyApi
{
    private const CREATE_DEPUTY_FROM_USER_ENDPOINT = 'deputy/add';
    private const FIND_ALL_DEPUTY_COURT_ORDERS = 'v2/deputy/%s/courtorders';

    public function __construct(
        private readonly RestClientInterface $restClient,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function createDeputyFromUser(User $currentUser)
    {
        return $this->restClient->post(self::CREATE_DEPUTY_FROM_USER_ENDPOINT, $currentUser);
    }

    public function findAllDeputyCourtOrdersForCurrentDeputy(): ?array
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();
        if (($currentUser instanceof User) !== true) {
            $this->logger->error('Unable to get correct instance of User via TokenStorage');

            return null;
        }

        $uri = sprintf(self::FIND_ALL_DEPUTY_COURT_ORDERS, $currentUser->getDeputyUid());

        return $this->restClient->get($uri, 'array');
    }
}
