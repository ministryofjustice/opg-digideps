<?php

namespace App\Service;

use App\Entity as EntityDir;
use App\Entity\NamedDeputy;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use App\Repository\NamedDeputyRepository;
use App\Entity\User;
use App\Factory\NamedDeputyFactory;
use Doctrine\ORM\EntityManagerInterface;
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
     * @param NamedDeputyRepository $namedDeputyRepository
     * @param NamedDeputyFactory $namedDeputyFactory
     */
    public function __construct(
        EntityManagerInterface $em,
        UserRepository $userRepository,
        ClientRepository $clientRepository,
        NamedDeputyRepository $namedDeputyRepository,
        NamedDeputyFactory $namedDeputyFactory
    ) {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->clientRepository = $clientRepository;
        $this->namedDeputyRepository = $namedDeputyRepository;
        $this->namedDeputyFactory = $namedDeputyFactory;
        $this->added = [];
    }

    /**
     * @param string $id
     *
     * @return User|null|object
     *
     */
    public function getMemberById(string $id)
    {
        return $this->userRepository->find($id);
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
