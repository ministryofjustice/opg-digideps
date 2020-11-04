<?php declare(strict_types=1);

namespace AppBundle\Service\Client\Internal;

use AppBundle\Entity\User;
use AppBundle\Event\UserUpdatedEvent;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\RestClientInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserApi
{
    private const USER_ENDPOINT = 'user';

    /**  @var RestClientInterface */
    private $restClient;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        RestClientInterface $restClient,
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->restClient = $restClient;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
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
     * @param User $preUpdateUser
     * @param User $postUpdateUser
     * @param array $jmsGroups
     * @param string $trigger
     * @return mixed
     */
    public function update(User $preUpdateUser, User $postUpdateUser, $jmsGroups = [], string $trigger)
    {
        $response = $this->restClient->put(
            sprintf('%s/%s', self::USER_ENDPOINT, $preUpdateUser->getId()),
            $postUpdateUser,
            $jmsGroups
        );

        $userUpdatedEvent = new UserUpdatedEvent(
            $preUpdateUser,
            $postUpdateUser,
            $this->tokenStorage->getToken()->getUser(),
            $trigger
        );

        $this->eventDispatcher->dispatch($userUpdatedEvent, UserUpdatedEvent::NAME);

        return $response;
    }
}
