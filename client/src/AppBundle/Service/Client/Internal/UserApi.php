<?php declare(strict_types=1);

namespace AppBundle\Service\Client\Internal;

use AppBundle\Entity\User;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\RestClientInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserApi
{
    private const USER_ENDPOINT = 'user/';
    /**
     * @var RestClientInterface
     */
    private $restClient;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(
        RestClientInterface $restClient,
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

        return $this->restClient->get(
            sprintf('%s/%s', self::USER_ENDPOINT, $user->getId()),
            'User',
            $jmsGroups
        );
    }

    /**
     * @param string $userId
     * @param array $userData
     * @param array $jmsGroups
     * @return mixed
     */
    public function put(string $userId, array $userData, $jmsGroups = [])
    {
        return $this->restClient->put(
            sprintf('%s/%s', self::USER_ENDPOINT, $userId),
            $userData,
            $jmsGroups
        );
    }
}
