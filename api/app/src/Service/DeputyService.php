<?php

namespace App\Service;

use App\Entity\Client;
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
        EntityManagerInterface $em,
    ) {
        $this->deputyRepository = $em->getRepository(Deputy::class);
        $this->em = $em;
    }

    /**
     * Adds a new deputy to the database if it doesn't already exist, or retrieve any existing one.
     * $userForDeputy becomes the user associated with the deputy.
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

    public function associateDeputyWithClient(Deputy $deputy, Client $client): void
    {
        $user = $deputy->getUser();

        if (is_null($user)) {
            throw new \ValueError('Could not associate deputy with client: deputy has no user');
        }

        $client->addUser($user);

        $this->em->persist($client);
        $this->em->flush();
    }
}
