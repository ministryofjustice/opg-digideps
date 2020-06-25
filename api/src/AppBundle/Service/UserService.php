<?php

namespace AppBundle\Service;

use AppBundle\Entity\Client;
use AppBundle\Entity\CourtOrder;
use AppBundle\Entity\CourtOrderDeputy;
use AppBundle\Entity\CourtOrderDeputyAddress;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Repository\CourtOrderRepository;
use AppBundle\Entity\Repository\TeamRepository;
use AppBundle\Entity\Repository\UserRepository;
use AppBundle\Entity\Team;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class UserService
{
    /** @var UserRepository */
    private $userRepository;

    /** @var TeamRepository */
    private $teamRepository;

    /** @var CourtOrderRepository  */
    private $courtOrderRepository;

    /** @var EntityManagerInterface */
    private $em;

    /**
     * @var OrgService
     */
    private $orgService;

    public function __construct(
        EntityManagerInterface $em,
        OrgService $orgService
    ) {
        $this->userRepository = $em->getRepository(User::class);
        $this->teamRepository = $em->getRepository(Team::class);
        $this->courtOrderRepository = $em->getRepository(CourtOrder::class);
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
            ->throwExceptionIfUserChangesRoleType($originalUser, $updatedUser)
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
     * @param User $originalUser
     * @param User $updatedUser
     * @return UserService
     */
    private function throwExceptionIfUserChangesRoleType(User $originalUser, User $updatedUser)
    {
        $adminBefore = in_array($originalUser->getRoleName(), User::$adminRoles);
        $adminAfter  = in_array($updatedUser->getRoleName(), User::$adminRoles);

        if ($adminAfter !== $adminBefore) {
            throw new \RuntimeException('Cannot change realm of user\'s role', 425);
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
            $ndr = $this->createNdrForClient($client);

            if (!$this->courtOrderExistsForCase($client->getCaseNumber())) {
                $this->attachCaseToNewCourtOrder($updatedUser, $client, $ndr);
            }
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
     * @return Ndr
     */
    private function createNdrForClient(Client $client)
    {
        $ndr = new Ndr($client);
        $this->em->persist($ndr);

        return $ndr;
    }

    /**
     * @param string $caseNumber
     * @return bool
     */
    private function courtOrderExistsForCase(string $caseNumber): bool
    {
        return null !== $this->courtOrderRepository->findOneBy(['caseNumber' => $caseNumber]);
    }

    /**
     * @param User $updatedUser
     * @param Client $client
     * @param Ndr $ndr
     */
    private function attachCaseToNewCourtOrder(User $updatedUser, Client $client, Ndr $ndr): void
    {
        $courtOrder = $this->createCourtOrder($client, $ndr);
        $courtOrderDeputy = $this->createCourtOrderDeputy($updatedUser, $courtOrder);
        $this->createCourtOrderDeputyAddress($updatedUser, $courtOrderDeputy);

        $ndr->setCourtOrder($courtOrder);
    }

    /**
     * @param Client|null $client
     * @param Ndr $ndr
     * @return CourtOrder
     */
    private function createCourtOrder(?Client $client, Ndr $ndr): CourtOrder
    {
        $courtOrder = new CourtOrder();
        $courtOrder
            ->setCaseNumber($client->getCaseNumber())
            ->setClient($client);

        $this->em->persist($courtOrder);

        return $courtOrder;
    }

    /**
     * @param User $updatedUser
     * @param CourtOrder $courtOrder
     * @return CourtOrderDeputy
     */
    private function createCourtOrderDeputy(User $updatedUser, CourtOrder $courtOrder): CourtOrderDeputy
    {
        $courtOrderDeputy = new CourtOrderDeputy();
        $courtOrderDeputy
            ->setUser($updatedUser)
            ->setCourtOrder($courtOrder)
            ->setFirstname($updatedUser->getFirstname())
            ->setSurname($updatedUser->getLastname())
            ->setEmail($updatedUser->getEmail())
            ->setDeputyNumber($updatedUser->getDeputyNo());

        $this->em->persist($courtOrderDeputy);

        return $courtOrderDeputy;
    }

    /**
     * @param User $updatedUser
     * @param CourtOrderDeputy $courtOrderDeputy
     */
    private function createCourtOrderDeputyAddress(User $updatedUser, CourtOrderDeputy $courtOrderDeputy): void
    {
        $courtOrderAddress = new CourtOrderDeputyAddress();
        $courtOrderAddress
            ->setDeputy($courtOrderDeputy)
            ->setAddressLine1($updatedUser->getAddress1())
            ->setAddressLine2($updatedUser->getAddress2())
            ->setAddressLine3($updatedUser->getAddress3())
            ->setPostcode($updatedUser->getAddressPostcode())
            ->setCountry($updatedUser->getAddressCountry());

        $this->em->persist($courtOrderAddress);
    }
}
