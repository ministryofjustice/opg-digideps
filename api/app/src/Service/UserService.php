<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Ndr\Ndr;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

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
     */
    public function addUser(User $loggedInUser, User $userToAdd, ?int $clientId)
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

        if ($loggedInUser->isLayDeputy() && !is_null($clientId)) {
            $this->addUserToUsersClients($userToAdd, $clientId);
        }
    }

    private function addUserToUsersClients($userToAdd, ?int $clientId)
    {
        $this->clientRepository->saveUserToClient($userToAdd, $clientId);
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
}
