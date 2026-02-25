<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Deputy;
use App\Entity\User;
use App\Repository\DeputyRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Psr\Log\LoggerInterface;

/**
 * Data services for user <-> deputy relationships.
 */
class UserDeputyService
{
    public function __construct(
        private readonly DeputyService $deputyService,
        private readonly UserRepository $userRepository,
        private readonly DeputyRepository $deputyRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Create deputy records for lay and named deputy dd_users where they don't exist.
     * Associate those users with deputy records where they aren't already associated.
     *
     * @return int Number of associations between deputies and users which were added
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function addMissingUserDeputies(): int
    {
        // find users who have no deputy associated with them
        $usersWithoutDeputies = $this->userRepository->findUsersWithoutDeputies();

        // get mapping from deputy UIDs to IDs (so we can quickly find the deputy ID from the user's deputy UID);
        // when we create a deputy, it gets added to this mapping
        $deputyUidsToIds = $this->deputyRepository->getUidToIdMapping();

        // associate users with deputies
        $numAssociations = 0;

        /** @var User $user */
        foreach ($usersWithoutDeputies as $user) {
            $deputyUid = "{$user->getDeputyUid()}";
            $deputy = null;

            // get or create the deputy
            if (array_key_exists($deputyUid, $deputyUidsToIds)) {
                /** @var ?Deputy $deputy */
                $deputy = $this->deputyRepository->find($deputyUidsToIds[$deputyUid]);

                if (!is_null($deputy)) {
                    /** @var ?User $existingUser */
                    $existingUser = $deputy->getUser();

                    if (!is_null($existingUser)) {
                        $this->logger->error(
                            sprintf(
                                'Deputy with ID:%s already associated with a User under ID:%s',
                                $deputy->getId(),
                                $existingUser->getId()
                            )
                        );
                        continue;
                    }
                }
            } elseif ('' !== $deputyUid) {
                // some users have an empty deputy UID, so we can't do anything with those;
                // create the deputy from the user data where we do have a deputy UID
                $deputy = $this->deputyService->createDeputyFromUser($user);
                $this->deputyRepository->save($deputy);
                $deputyUidsToIds[$deputyUid] = $deputy->getId();
            }

            if (!is_null($deputy)) {
                $deputy->setUser($user);
                $user->setDeputy($deputy);
                $this->userRepository->save($user);

                ++$numAssociations;
            }
        }

        return $numAssociations;
    }
}
