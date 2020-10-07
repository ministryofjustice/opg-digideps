<?php

namespace AppBundle\Service;

use AppBundle\Entity as EntityDir;
use AppBundle\Entity\NamedDeputy;
use AppBundle\Entity\Repository\ClientRepository;
use AppBundle\Entity\Repository\TeamRepository;
use AppBundle\Entity\Repository\UserRepository;
use AppBundle\Entity\Repository\NamedDeputyRepository;
use AppBundle\Entity\User;
use AppBundle\Factory\NamedDeputyFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class OrgService
{
    public const DEFAULT_ORG_NAME = 'Your Organisation';

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var ClientRepository
     */
    private $clientRepository;

    /**
     * @var TeamRepository
     */
    private $teamRepository;

    /**
     * @var NamedDeputyFactory
     */
    private $namedDeputyFactory;

    /**
     * @var EntityDir\Repository\NamedDeputyRepository
     */
    private $namedDeputyRepository;

    /**
     * @var array
     */
    private $added;

    /**
     * @param EntityManagerInterface $em
     * @param UserRepository $userRepository
     * @param ClientRepository $clientRepository
     * @param TeamRepository $teamRepository
     * @param NamedDeputyRepository $namedDeputyRepository
     * @param NamedDeputyFactory $namedDeputyFactory
     */
    public function __construct(
        EntityManagerInterface $em,
        UserRepository $userRepository,
        ClientRepository $clientRepository,
        TeamRepository $teamRepository,
        NamedDeputyRepository $namedDeputyRepository,
        NamedDeputyFactory $namedDeputyFactory
    ) {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->clientRepository = $clientRepository;
        $this->teamRepository = $teamRepository;
        $this->namedDeputyRepository = $namedDeputyRepository;
        $this->namedDeputyFactory = $namedDeputyFactory;
        $this->added = [];
    }

    /**
     * @param User $userCreator
     * @param string $id
     *
     * @throws AccessDeniedException if user not part of the team the creator user belongs to
     *
     * @return User|null|object
     *
     */
    public function getMemberById(User $userCreator, string $id)
    {
        $user = $this->userRepository->find($id);
        if (!array_key_exists($id, $userCreator->getMembersInAllTeams())) {
            throw new AccessDeniedException('User not part of the same team');
        }

        return $user;
    }

    /**
     * @param User $userWithTeams
     * @param User $userBeingAdded
     */
    public function addUserToUsersTeams(User $userWithTeams, User $userBeingAdded)
    {
        $teamIds = $this->teamRepository->findAllTeamIdsByUser($userWithTeams);

        foreach ($teamIds as $teamId) {
            $this->clientRepository->saveUserToTeam($userBeingAdded, $teamId);
        }
    }

    /**
     * @param User $userWithClients
     * @param User $userBeingAdded
     */
    public function addUserToUsersClients(User $userWithClients, User $userBeingAdded)
    {
        $clientIds = $this->clientRepository->findAllClientIdsByUser($userWithClients);

        foreach ($clientIds as $clientId) {
            $this->clientRepository->saveUserToClient($userBeingAdded, $clientId);
        }
    }

    /**
     * Delete $user from all the teams $loggedInUser belongs to
     * Also removes the user, if doesn't belong to any team any longer
     *
     * @param User $loggedInUser
     * @param User $user
     *
     * @throws OptimisticLockException
     */
    public function removeUserFromTeamsOf(User $loggedInUser, User $user)
    {
        // remove user from teams the logged-user (operation performer) belongs to
        foreach ($loggedInUser->getTeams() as $team) {
            $user->getTeams()->removeElement($team);
        }

        // remove client that also belongs to the creator
        // (equivalent to remove client from all the teams of the creator)
        foreach ($loggedInUser->getClients() as $client) {
            $client->removeUser($user);
        }

        // remove user if belonging to no teams
        if (count($user->getTeams()) === 0) {
            $this->em->remove($user);
        }

        $this->em->flush();
    }

    /**
     * @param array $csvRow
     * @return NamedDeputy|null
     */
    public function identifyNamedDeputy($csvRow)
    {
        $deputyNo = EntityDir\User::padDeputyNumber($csvRow['Deputy No']);

        /** @var NamedDeputy|null $namedDeputy */
        $namedDeputy = $this->namedDeputyRepository->findOneBy([
            'deputyNo' => $deputyNo,
            'email1' => strtolower($csvRow['Email']),
            'firstname' => $csvRow['Dep Forename'],
            'lastname' => $csvRow['Dep Surname'],
            'address1' => $csvRow['Dep Adrs1'],
            'addressPostcode' => $csvRow['Dep Postcode'],
        ]);

        return $namedDeputy;
    }

    /**
     * @param array $csvRow
     * @return EntityDir\NamedDeputy
     */
    public function createNamedDeputy($csvRow)
    {
        $deputyNo = EntityDir\User::padDeputyNumber($csvRow['Deputy No']);

        $namedDeputy = $this->namedDeputyFactory->createFromOrgCsv($csvRow);
        $this->em->persist($namedDeputy);
        $this->em->flush();

        $this->added['named_deputies'][] = $deputyNo;

        return $namedDeputy;
    }
}
