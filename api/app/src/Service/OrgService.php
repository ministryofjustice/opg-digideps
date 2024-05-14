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
    private $namedDeputyFactory;

    /**
     * @var DeputyRepository
     */
    private $namedDeputyRepository;

    /**
     * @var array
     */
    private $added;

    public function __construct(
        EntityManagerInterface $em,
        UserRepository $userRepository,
        ClientRepository $clientRepository,
        DeputyRepository $namedDeputyRepository,
        DeputyFactory $namedDeputyFactory
    ) {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->clientRepository = $clientRepository;
        $this->namedDeputyRepository = $namedDeputyRepository;
        $this->namedDeputyFactory = $namedDeputyFactory;
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
    public function identifyNamedDeputy($csvRow)
    {
        /** @var Deputy|null $namedDeputy */
        $namedDeputy = $this->namedDeputyRepository->findOneBy([
            'deputyUid' => $csvRow['Deputy Uid'],
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
     *
     * @return Deputy
     */
    public function createNamedDeputy($csvRow)
    {
        $namedDeputy = $this->namedDeputyFactory->createFromOrgCsv($csvRow);
        $this->em->persist($namedDeputy);
        $this->em->flush();

        $this->added['named_deputies'][] = $csvRow['Deputy Uid'];

        return $namedDeputy;
    }
}
