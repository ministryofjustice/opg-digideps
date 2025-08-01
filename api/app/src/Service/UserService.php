<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Ndr\Ndr;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use App\v2\DTO\InviteeDto;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;

class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ClientRepository $clientRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * Adds a new user to the database.
     *
     * @throws RandomException
     */
    public function addUser(User $loggedInUser, User $userToAdd, ?int $clientId = null): User
    {
        $this->exceptionIfEmailExist($userToAdd->getEmail());

        $userToAdd->recreateRegistrationToken();
        $userToAdd->setCreatedBy($loggedInUser);

        match (true) {
            $loggedInUser->isLayDeputy() => $userToAdd->setRegistrationRoute(User::CO_DEPUTY_INVITE),
            $loggedInUser->hasAdminRole() => $userToAdd->setRegistrationRoute(User::ADMIN_INVITE),
            $loggedInUser->isOrgNamedOrAdmin() => $userToAdd->setRegistrationRoute(User::ORG_ADMIN_INVITE),
        };

        $this->em->persist($userToAdd);
        $this->em->flush();

        $this->addUserToClient($loggedInUser, $userToAdd, $clientId);

        return $userToAdd;
    }

    private function addUserToClient(User $loggedInUser, User $userToAdd, ?int $clientId = null): void
    {
        if ($loggedInUser->isLayDeputy() && !is_null($clientId)) {
            $this->clientRepository->saveUserToClient($userToAdd, $clientId);
        }
    }

    /**
     * @param User $originalUser Original user for comparison checks
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editUser(User $originalUser, User $updatedUser)
    {
        $this
            ->throwExceptionIfUpdatedEmailExists($originalUser, $updatedUser)
            ->throwExceptionIfUserChangesRoleType($originalUser, $updatedUser)
            ->handleNdrStatusUpdate($updatedUser);

        $this->em->flush();
    }

    /**
     * @return UserService
     */
    private function throwExceptionIfUpdatedEmailExists(User $originalUser, User $updatedUser)
    {
        if ($originalUser->getEmail() != $updatedUser->getEmail()) {
            $this->exceptionIfEmailExist($updatedUser->getEmail());
        }

        return $this;
    }

    /**
     * @return UserService
     */
    private function throwExceptionIfUserChangesRoleType(User $originalUser, User $updatedUser)
    {
        $adminBefore = in_array($originalUser->getRoleName(), User::$adminRoles);
        $adminAfter = in_array($updatedUser->getRoleName(), User::$adminRoles);

        if ($adminAfter !== $adminBefore) {
            throw new \RuntimeException('Cannot change realm of user\'s role', 425);
        }

        return $this;
    }

    private function exceptionIfEmailExist($email)
    {
        if ($this->userRepository->findOneBy(['email' => $email])) {
            throw new \RuntimeException("User with email {$email} already exists.", 422);
        }
    }

    private function handleNdrStatusUpdate(User $updatedUser)
    {
        $client = $updatedUser->getFirstClient();
        if (!$updatedUser->isLayDeputy() || !$client instanceof Client) {
            return;
        }

        if ($updatedUser->getNdrEnabled() && !$this->clientHasExistingNdr($client)) {
            $this->createNdrForClient($client);
        }
    }

    /**
     * @return bool
     */
    private function clientHasExistingNdr(Client $client)
    {
        return null !== $client->getNdr();
    }

    /**
     * @return Ndr
     */
    private function createNdrForClient(Client $client)
    {
        $ndr = new Ndr($client);
        $this->em->persist($ndr);

        return $ndr;
    }

    /**
     * Get or add a user. In either case, the user is set to active and associated with the given client.
     *
     * @throws RandomException
     */
    public function getOrAddUser(InviteeDto $invitedDeputyData, User $invitingDeputy, int $deputyUid, int $clientId): User
    {
        /** @var ?User $existingUser */
        $existingUser = $this->userRepository->findOneBy([
            'email' => $invitedDeputyData->email,
        ]);

        if (!is_null($existingUser)) {
            if (is_null($existingUser->getRegistrationToken())) {
                $existingUser->recreateRegistrationToken();
            }

            $this->addUserToClient($invitingDeputy, $existingUser, $clientId);

            // this user needs to be active, otherwise the invited deputy won't be able to use it to sign in
            $existingUser->setActive(true);

            $this->em->persist($existingUser);
            $this->em->flush();

            return $existingUser;
        }

        // check whether the deputy already has an active primary user account, so we know whether this is a secondary
        // user we're adding
        $isPrimary = true;
        $existingUser = $this->userRepository->findOneBy([
            'deputyUid' => $deputyUid,
            'active' => true,
            'isPrimary' => true,
        ]);

        if (!is_null($existingUser)) {
            $isPrimary = false;
        }

        $invitedUser = new User();
        $invitedUser->setIsPrimary($isPrimary);
        $invitedUser->setEmail($invitedDeputyData->email);
        $invitedUser->setFirstname($invitedDeputyData->firstname);
        $invitedUser->setLastname($invitedDeputyData->lastname);
        $invitedUser->setRoleName($invitedDeputyData->roleName);
        $invitedUser->setDeputyUid($deputyUid);
        $invitedUser->setDeputyNo("$deputyUid");
        $invitedUser->setActive(true);
        $invitedUser->setRegistrationRoute(User::CO_DEPUTY_INVITE);

        return $this->addUser($invitingDeputy, $invitedUser, $clientId);
    }
}
