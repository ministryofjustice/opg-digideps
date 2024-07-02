<?php

namespace App\Service;

use App\Entity\Deputy;
use App\Entity\User;
use App\Repository\DeputyRepository;
use Doctrine\ORM\EntityManagerInterface;

class DeputyService
{
    /** @var DeputyRepository */
    private $deputyRepository;

    /** @var EntityManagerInterface */
    private $em;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->deputyRepository = $em->getRepository(Deputy::class);
        $this->em = $em;
    }

    /**
     * Adds a new deputy to the database if not already exists.
     */
    public function addDeputy(Deputy $deputyToAdd, User $currentUser)
    {
        $existingDeputy = $this->deputyRepository->findOneBy(['deputyUid' => $deputyToAdd->getDeputyUid()]);
        if ($existingDeputy) {
            return $existingDeputy->getId();
        }

        $deputyToAdd->setUser($currentUser);
        $this->em->persist($deputyToAdd);
        $this->em->flush();

        return $deputyToAdd->getId();
    }
}
