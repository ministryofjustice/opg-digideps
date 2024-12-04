<?php

namespace App\Service;

use App\Entity\Deputy;
use App\Entity\User;
use App\Factory\DeputyFactory;
use App\Repository\DeputyRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class OrgService
{
    public const DEFAULT_ORG_NAME = 'Your Organisation';

    public function __construct(
        protected EntityManagerInterface $em,
        private UserRepository $userRepository,
        private DeputyRepository $deputyRepository,
        private DeputyFactory $deputyFactory,
        private array $added = []
    ) {
    }

    /**
     * @return User|object|null
     */
    public function getMemberById(string $id)
    {
        return $this->userRepository->find($id);
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
