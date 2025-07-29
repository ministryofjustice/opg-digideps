<?php

namespace App\Service;

use App\Entity\Deputy;
use App\Entity\User;
use App\Repository\DeputyRepository;
use Doctrine\ORM\EntityManagerInterface;

class DeputyService
{
    public function __construct(
        private readonly DeputyRepository $deputyRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * Adds a new deputy to the database if it doesn't already exist, or retrieve any existing one.
     * $userForDeputy becomes the user associated with the deputy if there is no existing deputy.
     */
    public function getOrAddDeputy(Deputy $deputyToAdd, User $userForDeputy): Deputy
    {
        $existingDeputy = $this->deputyRepository->findOneBy(['deputyUid' => $deputyToAdd->getDeputyUid()]);
        if ($existingDeputy) {
            return $existingDeputy;
        }

        $deputyToAdd->setUser($userForDeputy);
        $this->em->persist($deputyToAdd);
        $this->em->flush();

        return $deputyToAdd;
    }
}
