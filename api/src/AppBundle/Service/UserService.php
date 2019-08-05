<?php

namespace AppBundle\Service;

use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Repository\TeamRepository;
use AppBundle\Entity\Repository\UserRepository;
use AppBundle\Entity\Team;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class UserService
{
    /** @var EntityRepository */
    private $userRepository;

    /** @var EntityRepository */
    private $teamRepository;

    /** @var EntityManager */
    private $em;

    /**
     * @var OrgService
     */
    private $orgService;

    public function __construct(
        EntityManager $em,
        OrgService $orgService
    ) {
        $this->userRepository = $em->getRepository(User::class);
        $this->teamRepository = $em->getRepository(Team::class);
        $this->em = $em;
        $this->orgService = $orgService;
    }

    /**
     * Adds a new user to the database
     *
     * @param User $loggedInUser
     * @param User $userToAdd
     * @param $data
     */
    public function addUser(User $loggedInUser, User $userToAdd, $data)
    {
        $this->exceptionIfEmailExist($userToAdd->getEmail());

        // generate org team name
        if ($loggedInUser->isOrgNamedDeputy() &&
            !empty($data['pa_team_name']) &&
            $this->getTeams()->isEmpty()
        ) {
            $this->getTeams()->first()->setTeamName($data['pa_team_name']);
        }

        $userToAdd->setRegistrationDate(new \DateTime());
        $userToAdd->recreateRegistrationToken();
        $this->em->persist($userToAdd);
        $this->em->flush();

        $this->orgService->addUserToUsersClients($loggedInUser, $userToAdd);

        if ($loggedInUser->isOrgNamedOrAdmin() && $userToAdd->isDeputyOrg()) {
            $this->orgService->addUserToUsersTeams($loggedInUser, $userToAdd);
        }
    }

    /**
     * @param User $originalUser Original user for comparison checks
     * @param User $updatedUser
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editUser(User $originalUser, User $updatedUser)
    {
        $this
            ->throwExceptionIfUpdatedEmailExists($originalUser, $updatedUser)
            ->handleNdrStatusUpdate($updatedUser);

        $this->em->flush();
    }

    /**
     * @param User $originalUser
     * @param User $updatedUser
     * @return UserService
     */
    private function throwExceptionIfUpdatedEmailExists(User $originalUser, User $updatedUser)
    {
        if ($originalUser->getEmail() != $updatedUser->getEmail()){
            $this->exceptionIfEmailExist($updatedUser->getEmail());
        }

        return $this;
    }

    /**
     * @param $email
     */
    private function exceptionIfEmailExist($email)
    {
        if ($this->userRepository->findOneBy(['email' => $email])) {
            throw new \RuntimeException("User with email {$email} already exists.", 422);
        }
    }

    /**
     * @param User $updatedUser
     */
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
     * @param Client $client
     * @return bool
     */
    private function clientHasExistingNdr(Client $client)
    {
        return (null !== $client->getNdr()) ? true : false;
    }

    /**
     * @param Client $client
     */
    private function createNdrForClient(Client $client)
    {
        $ndr = new Ndr($client);
        $this->em->persist($ndr);
    }
}
