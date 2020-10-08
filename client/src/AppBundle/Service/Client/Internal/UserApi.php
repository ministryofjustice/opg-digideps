<?php

namespace AppBundle\Service\Client\Internal;

use AppBundle\Entity\User;
use AppBundle\Service\Client\RestClient;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserApi {
    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(
        RestClient $restClient,
        TokenStorageInterface $tokenStorage
    ) {
        $this->restClient = $restClient;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param array $jmsGroups
     *
     * @return User
     */
    public function getUserWithData(array $jmsGroups = [])
    {
        $jmsGroups[] = 'user';
        $jmsGroups = array_unique($jmsGroups);
        sort($jmsGroups);

        /** @var User */
        $user = $this->tokenStorage->getToken()->getUser();

        return $this->restClient->get('user/' . $user->getId(), 'User', $jmsGroups);
    }
}
