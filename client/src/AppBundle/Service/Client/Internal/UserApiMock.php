<?php declare(strict_types=1);


namespace AppBundle\Service\Client\Internal;

use AppBundle\Entity\User;
use AppBundle\Service\Client\RestClientInterface;
use AppBundle\TestHelpers\UserHelpers;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserApiMock extends UserApi
{
    public function __construct(RestClientInterface $restClient, TokenStorageInterface $tokenStorage)
    {
        parent::__construct($restClient, $tokenStorage);
    }

    /**
     * @param array $jmsGroups
     *
     * @return User
     */
    public function getUserWithData(array $jmsGroups = [])
    {
        return UserHelpers::createUser();
    }

    /**
     * @param string $userId
     * @param array $userData
     * @param array $jmsGroups
     * @return mixed
     */
    public function put(string $userId, array $userData, $jmsGroups = [])
    {
        return UserHelpers::createUser($userData);
    }
}
