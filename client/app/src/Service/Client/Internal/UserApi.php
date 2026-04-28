<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\Client\Internal;

use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Event\AdminManagerCreatedEvent;
use OPG\Digideps\Frontend\Event\AdminUserCreatedEvent;
use OPG\Digideps\Frontend\Event\CoDeputyInvitedEvent;
use OPG\Digideps\Frontend\Event\DeputyInvitedEvent;
use OPG\Digideps\Frontend\Event\DeputySelfRegisteredEvent;
use OPG\Digideps\Frontend\Event\OrgUserCreatedEvent;
use OPG\Digideps\Frontend\Event\UserActivatedEvent;
use OPG\Digideps\Frontend\Event\UserDeletedEvent;
use OPG\Digideps\Frontend\Event\UserPasswordResetEvent;
use OPG\Digideps\Frontend\Event\UserUpdatedEvent;
use OPG\Digideps\Frontend\EventDispatcher\ObservableEventDispatcher;
use OPG\Digideps\Frontend\Service\Audit\AuditEvents;
use OPG\Digideps\Frontend\Service\Client\RestClientInterface;
use OPG\Digideps\Common\Registration\SelfRegisterData;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserApi
{
    protected const string USER_ENDPOINT = 'user';
    protected const string GET_USER_BY_EMAIL_ENDPOINT = 'user/get-one-by/email/%s';
    protected const string GET_USER_BY_EMAIL_ORG_ADMINS_ENDPOINT = 'user/get-team-names-by-email/%s';
    protected const string USER_BY_ID_ENDPOINT = 'user/%s';
    protected const string RECREATE_USER_TOKEN_ENDPOINT = 'user/recreate-token/%s';
    protected const string DEPUTY_SELF_REGISTER_ENDPOINT = 'selfregister';
    protected const string CREATE_CODEPUTY_ENDPOINT = 'codeputy/add/%d';
    protected const string CLEAR_REGISTRATION_TOKEN_ENDPOINT = 'user/clear-registration-token/%s';
    protected const string GET_PRIMARY_USER_ACCOUNT_ENDPOINT = 'user/get-primary-user-account/%s';
    protected const string GET_PRIMARY_EMAIL = 'user/get-primary-email/%s';

    protected const string UPDATE_USER_CODEPUTY_FLAG_ENDPOINT = 'user/update/codeputyflag/%d';

    /** @var RestClientInterface */
    protected $restClient;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var ObservableEventDispatcher */
    protected $eventDispatcher;

    public function __construct(
        RestClientInterface $restClient,
        TokenStorageInterface $tokenStorage,
        ObservableEventDispatcher $eventDispatcher,
    ) {
        $this->restClient = $restClient;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function createUser(User $userToCreate, array $jmsGroups = ['admin_add_user'])
    {
        $createdUser = $this->restClient->post(self::USER_ENDPOINT, $userToCreate, $jmsGroups, 'User');

        $userCreatedEvent = new AdminUserCreatedEvent($createdUser);
        $this->eventDispatcher->dispatch($userCreatedEvent, AdminUserCreatedEvent::NAME);

        if (User::ROLE_ADMIN_MANAGER === $createdUser->getRoleName()) {
            $this->dispatchAdminManagerCreatedEvent($createdUser);
        }

        return $createdUser;
    }

    public function createOrgUser(User $userToCreate, array $jmsGroups = ['org_team_add'])
    {
        $createdUser = $this->restClient->post(self::USER_ENDPOINT, $userToCreate, $jmsGroups, 'User');

        $userCreatedEvent = new OrgUserCreatedEvent($createdUser);
        $this->eventDispatcher->dispatch($userCreatedEvent, OrgUserCreatedEvent::NAME);

        return $createdUser;
    }

    /**
     * @return User
     */
    public function get(int $id, array $jmsGroups = [])
    {
        return $this->restClient->get(
            sprintf(self::USER_BY_ID_ENDPOINT, $id),
            'User',
            $jmsGroups
        );
    }

    public function getByEmail(string $email, array $jmsGroups = [])
    {
        return $this->restClient->get(
            sprintf(self::GET_USER_BY_EMAIL_ENDPOINT, $email),
            'User',
            $jmsGroups
        );
    }

    public function getByEmailOrgAdmins(string $email, array $jmsGroups = [])
    {
        return $this->restClient->get(
            sprintf(self::GET_USER_BY_EMAIL_ORG_ADMINS_ENDPOINT, $email),
            'User',
            $jmsGroups
        );
    }

    /**
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
     * @param array $jmsGroups
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

        $this->eventDispatcher->dispatch($userUpdatedEvent, UserUpdatedEvent::NAME);

        return $userIdArray;
    }

    public function delete(User $userToDelete, string $trigger)
    {
        $this->restClient->delete(sprintf(self::USER_BY_ID_ENDPOINT, $userToDelete->getId()));

        /** @var User */
        $deletedBy = $this->tokenStorage->getToken()->getUser();

        $userDeletedEvent = new UserDeletedEvent($userToDelete, $deletedBy, $trigger);
        $this->eventDispatcher->dispatch($userDeletedEvent, UserDeletedEvent::NAME);
    }

    /**
     * @return User
     */
    public function recreateToken(string $email)
    {
        return $this->restClient->apiCall(
            'put',
            sprintf(self::RECREATE_USER_TOKEN_ENDPOINT, $email),
            null,
            User::class,
            [],
            false
        );
    }

    public function activate(string $email)
    {
        $activatedUser = $this->recreateToken($email);

        $userActivatedEvent = new UserActivatedEvent($activatedUser);
        $this->eventDispatcher->dispatch($userActivatedEvent, UserActivatedEvent::NAME);
    }

    public function reInviteCoDeputy(string $email, User $loggedInUser)
    {
        $invitedCoDeputy = $this->recreateToken($email);

        $CoDeputyInvitedEvent = new CoDeputyInvitedEvent($invitedCoDeputy, $loggedInUser);
        $this->eventDispatcher->dispatch($CoDeputyInvitedEvent, CoDeputyInvitedEvent::NAME);
    }

    public function reInviteDeputy(string $email)
    {
        $invitedDeputy = $this->recreateToken($email);

        $deputyInvitedEvent = new DeputyInvitedEvent($invitedDeputy);
        $this->eventDispatcher->dispatch($deputyInvitedEvent, DeputyInvitedEvent::NAME);
    }

    public function resetPassword(string $email)
    {
        $passwordResetUser = $this->recreateToken($email);

        $passwordResetEvent = new UserPasswordResetEvent($passwordResetUser);
        $this->eventDispatcher->dispatch($passwordResetEvent, UserPasswordResetEvent::NAME);
    }

    public function selfRegister(SelfRegisterData $selfRegisterData)
    {
        $registeredDeputy = $this->restClient->apiCall(
            'post',
            self::DEPUTY_SELF_REGISTER_ENDPOINT,
            $selfRegisterData,
            User::class,
            [],
            false
        );

        $deputySelfRegisteredEvent = new DeputySelfRegisteredEvent($registeredDeputy);
        $this->eventDispatcher->dispatch($deputySelfRegisteredEvent, DeputySelfRegisteredEvent::NAME);
    }

    public function agreeTermsUse($token)
    {
        return $this->restClient->apiCall('put', 'user/agree-terms-use/' . $token, null, 'raw', [], false);
    }

    public function clearRegistrationToken(string $token)
    {
        return $this->restClient->apiCall(
            'put',
            sprintf(self::CLEAR_REGISTRATION_TOKEN_ENDPOINT, $token),
            null,
            'raw',
            [],
            false
        );
    }

    private function dispatchAdminManagerCreatedEvent(User $createdUser)
    {
        $trigger = AuditEvents::TRIGGER_ADMIN_MANAGER_MANUALLY_CREATED;
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $adminManagerCreatedEvent = new AdminManagerCreatedEvent(
            $trigger,
            $currentUser,
            $createdUser
        );

        $this->eventDispatcher->dispatch($adminManagerCreatedEvent, AdminManagerCreatedEvent::NAME);
    }

    public function returnPrimaryEmail(?int $deputyUid): ?string
    {
        if (is_null($deputyUid)) {
            return null;
        }

        $jsonString = (string) $this->restClient->get(
            sprintf(self::GET_PRIMARY_EMAIL, $deputyUid),
            'raw'
        );

        /** @var ?string $value */
        $value = json_decode($jsonString, true)['data'];

        return $value;
    }

    public function getPrimaryUserAccount(int $deputyUid): User
    {
        return $this->restClient->get(
            sprintf(self::GET_PRIMARY_USER_ACCOUNT_ENDPOINT, $deputyUid),
            'User',
            []
        );
    }

    public function updateUserCodeputyFlagToTrue(int $id, array $jmsGroups = []): void
    {
        $this->restClient->put(
            sprintf(self::UPDATE_USER_CODEPUTY_FLAG_ENDPOINT, $id),
            'User',
            $jmsGroups
        );
    }
}
