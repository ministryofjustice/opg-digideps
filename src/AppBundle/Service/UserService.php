<?php

namespace AppBundle\Service;

use AppBundle\Entity\Repository\TeamRepository;
use AppBundle\Entity\Repository\UserRepository;
use AppBundle\Entity\User;
use AppBundle\Exception\NotFound;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\BankAccount as ReportBankAccount;

class UserService
{
    /** @var EntityRepository */
    protected $userRepository;

    /** @var EntityRepository */
    protected $teamRepository;

    public function __construct(
        UserRepository $userRepository,
        TeamRepository $teamRepository,
        EntityManager $em
    )
    {
        $this->userRepository = $userRepository;
        $this->teamRepository = $teamRepository;
        $this->_em = $em;
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
        $this->checkUserEmail($userToAdd);

        if ($loggedInUser->isPaAdministrator()) {
            $this->addPaUser($loggedInUser, $userToAdd, $data);
        }

        $userToAdd->setRegistrationDate(new \DateTime());

        $userToAdd->recreateRegistrationToken();

        $this->hardDeleteExistingUser($userToAdd);

        $this->_em->persist($userToAdd);
        $this->_em->flush();
    }

    /**
     * Adds a new pa user to the database
     *
     * @param User $loggedInUser
     * @param User $userToAdd
     * @param $data
     */
    private function addPaUser(User $loggedInUser, User $userToAdd, $data)
    {
        $userToAdd->ensureRoleNameSet();
        $userToAdd->generatePaTeam($loggedInUser, $data);

        if ($loggedInUser->isPaNamedDeputy() &&
            !empty($data['pa_team_name']) &&
            $userToAdd->getTeams()->isEmpty()
        ) {
            $team = $userToAdd->getTeams()->first()->setTeamName($data['pa_team_name']);
            $this->_em->flush($team);
        }

        $isPaMemberBeingCreated = in_array($userToAdd->getRoleName(), [User::ROLE_PA_ADMIN, User::ROLE_PA_TEAM_MEMBER]);
        if ($isPaMemberBeingCreated) {
            // add to creator's team
            if ($team = $loggedInUser->getTeams()->first()) {
                $userToAdd->addTeam($team);
                $this->_em->flush($team);
            }

            //copy clients
            foreach($loggedInUser->getClients() as $client) {

                $userToAdd->addClient($client);
            }
        }
    }

    /**
     * Update a user. Checks that the email is not in use then persists the entity
     *
     * @param User $originalUser Original user for comparison checks
     * @param User $userToEdit The user to edit
     */
    public function editUser(User $originalUser, User $userToEdit)
    {
        if (empty($userToEdit->getRoleName())) {
            $userToEdit->setRoleName(User::ROLE_PA_TEAM_MEMBER);
        }

        if($originalUser->getEmail() != $userToEdit->getEmail()) {
            $this->checkUserEmail($userToEdit);

            $this->hardDeleteExistingUser($userToEdit);
        }

        $this->_em->flush($userToEdit);
    }

    public function editPaUser(User $originalUser, User $userToEdit)
    {
        if (empty($userToEdit->getRoleName())) {
            $userToEdit->setRoleName(User::ROLE_PA_TEAM_MEMBER);
        }

        $this->editUser($originalUser, $userToEdit);
    }

    /**
     * @param User $user
     */
    private function checkUserEmail(User $user)
    {
        if ($this->userRepository->findOneBy(['email' => $user->getEmail()])) {
            throw new \RuntimeException("PA User with email {$user->getEmail()} already exists.", 422);
        }
    }

    /**
     * @param User $user
     */
    private function hardDeleteExistingUser(User $user)
    {
        $existingSoftDeletedUser = $this->userRepository->findUnfilteredOneBy(['email' => $user->getEmail()]);
        if ($existingSoftDeletedUser != null) {
            // delete soft deleted user a second time to hard delete it
            $this->_em->remove($existingSoftDeletedUser);
            $this->_em->flush($existingSoftDeletedUser);
        }
    }
}
