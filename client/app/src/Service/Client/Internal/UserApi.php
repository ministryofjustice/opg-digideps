<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Entity\User;
use App\Event\AdminManagerCreatedEvent;
use App\Event\AdminUserCreatedEvent;
use App\Event\CoDeputyCreatedEvent;
use App\Event\CoDeputyInvitedEvent;
use App\Event\DeputyInvitedEvent;
use App\Event\DeputySelfRegisteredEvent;
use App\Event\OrgUserCreatedEvent;
use App\Event\UserActivatedEvent;
use App\Event\UserDeletedEvent;
use App\Event\UserPasswordResetEvent;
use App\Event\UserUpdatedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Model\SelfRegisterData;
use App\Service\Audit\AuditEvents;
use App\Service\Client\RestClientInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserApi
{
    protected const USER_ENDPOINT = 'user';
    protected const GET_USER_BY_EMAIL_ENDPOINT = 'user/get-one-by/email/%s';
    protected const GET_USER_BY_EMAIL_ORG_ADMINS_ENDPOINT = 'user/get-team-names-by-email/%s';
    protected const USER_BY_ID_ENDPOINT = 'user/%s';
    protected const RECREATE_USER_TOKEN_ENDPOINT = 'user/recreate-token/%s';
    protected const DEPUTY_SELF_REGISTER_ENDPOINT = 'selfregister';
    protected const CREATE_CODEPUTY_ENDPOINT = 'codeputy/add/%s';
    protected const CLEAR_REGISTRATION_TOKEN_ENDPOINT = 'user/clear-registration-token/%s';
    protected const GET_PRIMARY_USER_ACCOUNT_ENDPOINT = 'user/get-primary-user-account/%s';
    protected const GET_PRIMARY_EMAIL = 'user/get-primary-email/%s';

    public function __construct(
        private readonly RestClientInterface $restClient,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObservableEventDispatcher $eventDispatcher,
    ) {
    }

    public function createUser(User $userToCreate, array $jmsGroups = ['admin_add_user']): User
    {
        /** @var User $createdUser */
        $createdUser = $this->restClient->post(self::USER_ENDPOINT, $userToCreate, $jmsGroups, 'User');

        $userCreatedEvent = new AdminUserCreatedEvent($createdUser);
        $this->eventDispatcher->dispatch($userCreatedEvent, AdminUserCreatedEvent::NAME);

        if (User::ROLE_ADMIN_MANAGER === $createdUser->getRoleName()) {
            $this->dispatchAdminManagerCreatedEvent($createdUser);
        }

        return $createdUser;
    }

    public function createOrgUser(User $userToCreate, array $jmsGroups = ['org_team_add']): User
    {
        /** @var User $createdUser */
        $createdUser = $this->restClient->post(self::USER_ENDPOINT, $userToCreate, $jmsGroups, 'User');

        $userCreatedEvent = new OrgUserCreatedEvent($createdUser);
        $this->eventDispatcher->dispatch($userCreatedEvent, OrgUserCreatedEvent::NAME);

        return $createdUser;
    }

    public function get(int $id, array $jmsGroups = []): User
    {
        /** @var User $user */
        $user = $this->restClient->get(
            endpoint: sprintf(self::USER_BY_ID_ENDPOINT, $id),
            expectedResponseType: User::class,
            jmsGroups: $jmsGroups
        );

        return $user;
    }

    public function getByEmail(string $email, array $jmsGroups = []): User
    {
        /** @var User $user */
        $user = $this->restClient->get(
            endpoint: sprintf(self::GET_USER_BY_EMAIL_ENDPOINT, $email),
            expectedResponseType: User::class,
            jmsGroups: $jmsGroups
        );

        return $user;
    }

    public function getByEmailOrgAdmins(string $email, array $jmsGroups = []): User
    {
        /** @var User $user */
        $user = $this->restClient->get(
            sprintf(self::GET_USER_BY_EMAIL_ORG_ADMINS_ENDPOINT, $email),
            User::class,
            $jmsGroups
        );

        return $user;
    }

    public function getUserWithData(array $jmsGroups = []): User
    {
        $jmsGroups[] = 'user';
        $jmsGroups = array_unique($jmsGroups);
        sort($jmsGroups);

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        return $this->restClient->get(
            sprintf(self::USER_BY_ID_ENDPOINT, $user->getId()),
            User::class,
            $jmsGroups
        );
    }

    public function update(User $preUpdateUser, User $postUpdateUser, string $trigger, array $jmsGroups = []): array
    {
        /** @var array $userIdArray */
        $userIdArray = $this->restClient->put(
            endpoint: sprintf(self::USER_BY_ID_ENDPOINT, $preUpdateUser->getId()),
            data: $postUpdateUser,
            jmsGroups: $jmsGroups
        );

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $userUpdatedEvent = new UserUpdatedEvent(
            $preUpdateUser,
            $postUpdateUser,
            $user,
            $trigger
        );

        $this->eventDispatcher->dispatch($userUpdatedEvent, UserUpdatedEvent::NAME);

        return $userIdArray;
    }

    public function delete(User $userToDelete, string $trigger): void
    {
        $this->restClient->delete(sprintf(self::USER_BY_ID_ENDPOINT, $userToDelete->getId()));

        /** @var User $deletedBy */
        $deletedBy = $this->tokenStorage->getToken()->getUser();

        $userDeletedEvent = new UserDeletedEvent($userToDelete, $deletedBy, $trigger);
        $this->eventDispatcher->dispatch($userDeletedEvent, UserDeletedEvent::NAME);
    }

    public function recreateToken(string $email): User
    {
        /** @var User $user */
        $user = $this->restClient->apiCall(
            method: 'put',
            endpoint: sprintf(self::RECREATE_USER_TOKEN_ENDPOINT, $email),
            data: null,
            expectedResponseType: User::class,
            authenticated: false
        );

        return $user;
    }

    public function activate(string $email): void
    {
        $activatedUser = $this->recreateToken($email);

        $userActivatedEvent = new UserActivatedEvent($activatedUser);
        $this->eventDispatcher->dispatch($userActivatedEvent, UserActivatedEvent::NAME);
    }

    public function reInviteCoDeputy(string $email, User $loggedInUser): void
    {
        $invitedCoDeputy = $this->recreateToken($email);

        $CoDeputyInvitedEvent = new CoDeputyInvitedEvent($invitedCoDeputy, $loggedInUser);
        $this->eventDispatcher->dispatch($CoDeputyInvitedEvent, CoDeputyInvitedEvent::NAME);
    }

    public function reInviteDeputy(string $email): void
    {
        $invitedDeputy = $this->recreateToken($email);

        $deputyInvitedEvent = new DeputyInvitedEvent($invitedDeputy);
        $this->eventDispatcher->dispatch($deputyInvitedEvent, DeputyInvitedEvent::NAME);
    }

    public function resetPassword(string $email): void
    {
        $passwordResetUser = $this->recreateToken($email);

        $passwordResetEvent = new UserPasswordResetEvent($passwordResetUser);
        $this->eventDispatcher->dispatch($passwordResetEvent, UserPasswordResetEvent::NAME);
    }

    public function selfRegister(SelfRegisterData $selfRegisterData): void
    {
        $registeredDeputy = $this->restClient->apiCall(
            method: 'post',
            endpoint: self::DEPUTY_SELF_REGISTER_ENDPOINT,
            data: $selfRegisterData,
            expectedResponseType: User::class,
            authenticated: false
        );

        $deputySelfRegisteredEvent = new DeputySelfRegisteredEvent($registeredDeputy);
        $this->eventDispatcher->dispatch($deputySelfRegisteredEvent, DeputySelfRegisteredEvent::NAME);
    }

    public function createCoDeputy(User $invitedCoDeputy, User $invitedByDeputyName, int $clientId): User
    {
        $createdCoDeputy = $this->restClient->post(
            endpoint: sprintf(self::CREATE_CODEPUTY_ENDPOINT, $clientId),
            data: $invitedCoDeputy,
            jmsgroups: ['codeputy'],
            expectedResponseType: User::class
        );

        $coDeputyCreatedEvent = new CoDeputyCreatedEvent($createdCoDeputy, $invitedByDeputyName);
        $this->eventDispatcher->dispatch($coDeputyCreatedEvent, CoDeputyCreatedEvent::NAME);

        return $createdCoDeputy;
    }

    public function agreeTermsUse(string $token): StreamInterface
    {
        /** @var StreamInterface $stream */
        $stream = $this->restClient->apiCall('put', 'user/agree-terms-use/'.$token, null, 'raw', [], false);

        return $stream;
    }

    public function clearRegistrationToken(string $token): StreamInterface
    {
        /** @var StreamInterface $stream */
        $stream = $this->restClient->apiCall(
            method: 'put',
            endpoint: sprintf(self::CLEAR_REGISTRATION_TOKEN_ENDPOINT, $token),
            data: null,
            expectedResponseType: 'raw',
            authenticated: false
        );

        return $stream;
    }

    private function dispatchAdminManagerCreatedEvent(User $createdUser): void
    {
        $trigger = AuditEvents::TRIGGER_ADMIN_MANAGER_MANUALLY_CREATED;

        /** @var User $currentUser */
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

        /** @var string $jsonString */
        $jsonString = $this->restClient->get(
            endpoint: sprintf(self::GET_PRIMARY_EMAIL, $deputyUid),
            expectedResponseType: 'raw'
        );

        $decoded = json_decode($jsonString, true);

        /** @var ?string $value */
        $value = null;

        if (!is_null($decoded)) {
            $value = $decoded['data'];
        }

        return $value;
    }

    public function getPrimaryUserAccount(int $deputyUid): User
    {
        return $this->restClient->get(
            endpoint: sprintf(self::GET_PRIMARY_USER_ACCOUNT_ENDPOINT, $deputyUid),
            expectedResponseType: User::class
        );
    }
}
