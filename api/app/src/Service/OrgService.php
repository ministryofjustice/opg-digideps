<?php

namespace App\Service;

use App\Entity\Deputy;
use App\Entity\User;
use App\Factory\DeputyFactory;
use App\Repository\ClientRepository;
use App\Repository\DeputyRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

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
     * @var DeputyFactory
     */
    private $deputyFactory;

    /**
     * @var DeputyRepository
     */
    private $deputyRepository;

    /**
     * @var array
     */
    private $added;

    public function __construct(
        EntityManagerInterface $em,
        UserRepository $userRepository,
        ClientRepository $clientRepository,
        DeputyRepository $deputyRepository,
        DeputyFactory $deputyFactory
    ) {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->clientRepository = $clientRepository;
        $this->deputyRepository = $deputyRepository;
        $this->deputyFactory = $deputyFactory;
        $this->added = [];
    }

    /**
     * @return User|object|null
     */
    public function getMemberById(string $id)
    {
        return $this->userRepository->find($id);
    }

    public function addUserToUsersClients(User $userWithClients, User $userBeingAdded)
    {
        $clientIds = $this->clientRepository->findAllClientIdsByUser($userWithClients);

        foreach ($clientIds as $clientId) {
            $this->clientRepository->saveUserToClient($userBeingAdded, $clientId);
        }
    }

    /**
     * @param array $csvRow
     *
     * @return Deputy|null
     */
    public function identifyDeputy($csvRow)
    {
        /** @var Deputy|null $deputy */
        $deputy = $this->deputyRepository->findOneBy([
            'deputyUid' => $csvRow['Deputy Uid'],
            'email1' => strtolower($csvRow['Email']),
            'firstname' => $csvRow['Dep Forename'],
            'lastname' => $csvRow['Dep Surname'],
            'address1' => $csvRow['Dep Adrs1'],
            'addressPostcode' => $csvRow['Dep Postcode'],
        ]);

        return $deputy;
    }

    /**
     * @param array $csvRow
     *
     * @return Deputy
     */
    public function createDeputy($csvRow)
    {
        $deputy = $this->deputyFactory->createFromOrgCsv($csvRow);
        $this->em->persist($deputy);
        $this->em->flush();

        $this->added['deputies'][] = $csvRow['Deputy Uid'];

        return $deputy;
    }
}
