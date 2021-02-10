<?php declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Entity\User;
use App\Event\CoDeputyCreatedEvent;
use App\Event\CoDeputyInvitedEvent;
use App\Event\DeputyInvitedEvent;
use App\Event\DeputySelfRegisteredEvent;
use App\Event\OrgUserCreatedEvent;
use App\Event\UserActivatedEvent;
use App\Event\UserPasswordResetEvent;
use App\Event\UserTokenRecreatedEvent;
use App\Event\AdminUserCreatedEvent;
use App\Event\UserDeletedEvent;
use App\Event\UserUpdatedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Model\SelfRegisterData;
use App\Service\Client\RestClientInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserApi
{
    protected const USER_ENDPOINT = 'user';
    protected const GET_USER_BY_EMAIL_ENDPOINT = 'user/get-one-by/email/%s';
    protected const GET_USER_BY_EMAIL_ORG_ADMINS_ENDPOINT = 'user/get-team-names-by-email/%s';
    protected const USER_BY_ID_ENDPOINT = 'user/%s';
    protected const RECREATE_USER_TOKEN_ENDPOINT = 'user/recreate-token/%s';
    protected const DEPUTY_SELF_REGISTER_ENDPOINT = 'selfregister';
    protected const CREATE_CODEPUTY_ENDPOINT = 'codeputy/add';

    /**  @var RestClientInterface */
    protected $restClient;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var ObservableEventDispatcher */
    protected $eventDispatcher;

    public function __construct(
        RestClientInterface $restClient,
        TokenStorageInterface $tokenStorage,
        ObservableEventDispatcher $eventDispatcher
    ) {
        $this->restClient = $restClient;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function createAdminUser(User $userToCreate, array $jmsGroups = ['admin_add_user'])
    {
        $createdUser = $this->restClient->post(self::USER_ENDPOINT, $userToCreate, $jmsGroups, 'User');

        $userCreatedEvent = new AdminUserCreatedEvent($createdUser);
        $this->eventDispatcher->dispatch(AdminUserCreatedEvent::NAME, $userCreatedEvent);

        return $createdUser;
    }

    public function createOrgUser(User $userToCreate, array $jmsGroups = ['org_team_add'])
    {
        $createdUser = $this->restClient->post(self::USER_ENDPOINT, $userToCreate, $jmsGroups, 'User');

        $userCreatedEvent = new OrgUserCreatedEvent($createdUser);
        $this->eventDispatcher->dispatch(OrgUserCreatedEvent::NAME, $userCreatedEvent);

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

    /**
     * @param string $email
     * @return mixed
     */
    public function getByEmail(string $email)
    {
        return $this->restClient->get(sprintf(self::GET_USER_BY_EMAIL_ENDPOINT, $email), 'User');
    }

    /**
     * @param string $email
     * @return mixed
     */
    public function getByEmailOrgAdmins(string $email)
    {
        return $this->restClient->get(sprintf(self::GET_USER_BY_EMAIL_ORG_ADMINS_ENDPOINT, $email), 'User');
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
        $userIdArray = $this->restClient->put(
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

        return $userIdArray;
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

    /**
     * @param string $email
     * @param string $type
     */
    public function activate(string $email)
    {
        $activatedUser = $this->recreateToken($email);

        $userActivatedEvent = new UserActivatedEvent($activatedUser);
        $this->eventDispatcher->dispatch(UserActivatedEvent::NAME, $userActivatedEvent);
    }

    /**
     * @param string $email
     * @param User $loggedInUser
     */
    public function reInviteCoDeputy(string $email, User $loggedInUser)
    {
        $invitedCoDeputy = $this->recreateToken($email);

        $CoDeputyInvitedEvent = new CoDeputyInvitedEvent($invitedCoDeputy, $loggedInUser);
        $this->eventDispatcher->dispatch(CoDeputyInvitedEvent::NAME, $CoDeputyInvitedEvent);
    }

    /**
     * @param string $email
     */
    public function reInviteDeputy(string $email)
    {
        $invitedDeputy = $this->recreateToken($email);

        $deputyInvitedEvent = new DeputyInvitedEvent($invitedDeputy);
        $this->eventDispatcher->dispatch(DeputyInvitedEvent::NAME, $deputyInvitedEvent);
    }

    /**
     * @param string $email
     */
    public function resetPassword(string $email)
    {
        $passwordResetUser = $this->recreateToken($email);

        $passwordResetEvent = new UserPasswordResetEvent($passwordResetUser);
        $this->eventDispatcher->dispatch(UserPasswordResetEvent::NAME, $passwordResetEvent);
    }

    /**
     * @param SelfRegisterData $selfRegisterData
     */
    public function selfRegister(SelfRegisterData $selfRegisterData)
    {
        $registeredDeputy = $this->restClient->apiCall(
            'post',
            self::DEPUTY_SELF_REGISTER_ENDPOINT,
            $selfRegisterData,
            'User',
            [],
            false
        );

        $deputySelfRegisteredEvent = new DeputySelfRegisteredEvent($registeredDeputy);
        $this->eventDispatcher->dispatch(DeputySelfRegisteredEvent::NAME, $deputySelfRegisteredEvent);
    }

    /**
     * @param User $invitedCoDeputy
     * @param User $invitedByDeputyName
     * @return User
     */
    public function createCoDeputy(User $invitedCoDeputy, User $invitedByDeputyName)
    {
        $createdCoDeputy = $this->restClient->post(
            self::CREATE_CODEPUTY_ENDPOINT,
            $invitedCoDeputy,
            ['codeputy'],
            'User'
        );

        $coDeputyCreatedEvent = new CoDeputyCreatedEvent($createdCoDeputy, $invitedByDeputyName);
        $this->eventDispatcher->dispatch(CoDeputyCreatedEvent::NAME, $coDeputyCreatedEvent);

        return $createdCoDeputy;
    }

    /**
     * @return User[]
     */
    public function getActiveLays()
    {
        return $this->restClient->get(
            self::GET_ACTIVE_LAYS,
            'User[]',
            ['user', 'user-clients']
        );
    }
}
