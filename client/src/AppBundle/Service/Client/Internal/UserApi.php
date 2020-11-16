<?php declare(strict_types=1);

namespace AppBundle\Service\Client\Internal;

use AppBundle\Entity\User;
use AppBundle\Event\CoDeputyInvitedEvent;
use AppBundle\Event\DeputyInvitedEvent;
use AppBundle\Event\UserPasswordResetEvent;
use AppBundle\Event\UserTokenRecreatedEvent;
use AppBundle\Event\UserCreatedEvent;
use AppBundle\Event\UserDeletedEvent;
use AppBundle\Event\UserUpdatedEvent;
use AppBundle\Service\Client\RestClientInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserApi
{
    private const USER_ENDPOINT = 'user';
    private const GET_USER_BY_EMAIL_ENDPOINT = 'user/get-one-by/email/%s';
    private const USER_BY_ID_ENDPOINT = 'user/%s';
    private const RECREATE_USER_TOKEN_ENDPOINT = 'user/recreate-token/%s';

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

    public function create(User $userToCreate, array $jmsGroups = ['admin_add_user'])
    {
        $createdUser = $this->restClient->post(self::USER_ENDPOINT, $userToCreate, $jmsGroups, 'User');

        $userCreatedEvent = new UserCreatedEvent($createdUser);
        $this->eventDispatcher->dispatch(UserCreatedEvent::NAME, $userCreatedEvent);

        return $createdUser;
    }

    /**
     * @param int $id
     * @param array $jmsGroups
     * @return User
     */
    public function get(int $id, array $jmsGroups = [])
    {
        return $this->restClient->get(sprintf(self::USER_BY_ID_ENDPOINT, $id), 'User', $jmsGroups);
    }

    public function getByEmail(string $email)
    {
        return $this->restClient->get(sprintf(self::GET_USER_BY_EMAIL_ENDPOINT, $email), 'User');
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
            sprintf(self::USER_BY_ID_ENDPOINT, $user->getId()),
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
    public function update(User $preUpdateUser, User $postUpdateUser, string $trigger, $jmsGroups = [])
    {
        $response = $this->restClient->put(
            sprintf(self::USER_BY_ID_ENDPOINT, $preUpdateUser->getId()),
            $postUpdateUser,
            $jmsGroups
        );

        $userUpdatedEvent = new UserUpdatedEvent(
            $preUpdateUser,
            $postUpdateUser,
            $this->tokenStorage->getToken()->getUser(),
            $trigger
        );

        $this->eventDispatcher->dispatch(UserUpdatedEvent::NAME, $userUpdatedEvent);

        return $response;
    }

    /**
     * @param User $userToDelete
     * @param string $trigger
     */
    public function delete(User $userToDelete, string $trigger)
    {
        $this->restClient->delete(sprintf(self::USER_BY_ID_ENDPOINT, $userToDelete->getId()));

        /** @var User */
        $deletedBy = $this->tokenStorage->getToken()->getUser();

        $userDeletedEvent = new UserDeletedEvent($userToDelete, $deletedBy, $trigger);
        $this->eventDispatcher->dispatch(UserDeletedEvent::NAME, $userDeletedEvent);
    }

    /**
     * @param string $email
     * @param string $type
     */
    public function resetPassword(string $email)
    {
        $passwordResetUser = $this->recreateToken($email);

        $passwordResetEvent = new UserPasswordResetEvent($passwordResetUser);
        $this->eventDispatcher->dispatch(UserPasswordResetEvent::NAME, $passwordResetEvent);
    }

    /**
     * @param string $email
     * @return User
     */
    public function recreateToken(string $email)
    {
        return $this->restClient->apiCall(
            'put',
            sprintf(self::RECREATE_USER_TOKEN_ENDPOINT, $email),
            null,
            'User',
            [],
            false
        );
    }

    public function inviteCoDeputy(string $email, User $loggedInUser)
    {
        $invitedCoDeputy = $this->recreateToken($email);

        $CoDeputyInvitedEvent = new CoDeputyInvitedEvent($invitedCoDeputy, $loggedInUser);
        $this->eventDispatcher->dispatch(CoDeputyInvitedEvent::NAME, $CoDeputyInvitedEvent);
    }

    public function inviteDeputy(string $email)
    {
        $invitedDeputy = $this->recreateToken($email);

        $deputyInvitedEvent = new DeputyInvitedEvent($invitedDeputy);
        $this->eventDispatcher->dispatch(DeputyInvitedEvent::NAME, $deputyInvitedEvent);
    }
}
