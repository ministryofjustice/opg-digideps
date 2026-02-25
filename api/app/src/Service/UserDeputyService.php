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
        // find dd_users (active, primary, lay/named) who have no deputy associated with them
        $usersWithoutDeputies = $this->userRepository->findUsersWithoutDeputies();

        // get mapping from deputy UIDs to IDs (so we can quickly find the deputy ID from the user's deputy UID);
        // when we create a deputy, it gets added to this mapping
        $deputyUidsToIds = $this->deputyRepository->getUidToIdMapping();

        // associate users with deputies
        $numAssociations = 0;

        /** @var User $user */
        foreach ($usersWithoutDeputies as $user) {
            $deputyUid = "{$user->getDeputyUid()}";

            if ('' === $deputyUid) {
                // we don't need to process this user any further as they have no deputy UID
                continue;
            }

            /** @var ?Deputy $deputy */
            $deputy = null;

            // get or create the deputy
            if (array_key_exists($deputyUid, $deputyUidsToIds)) {
                $deputy = $this->deputyRepository->find($deputyUidsToIds[$deputyUid]);

                if (!is_null($deputy)) {
                    /** @var ?User $existingUser */
                    $existingUser = $deputy->getUser();

                    if (!is_null($existingUser)) {
                        $this->logger->error(
                            sprintf(
                                'Deputy with ID %s already associated with user with ID %s',
                                $deputy->getId(),
                                $existingUser->getId()
                            )
                        );

                        // we don't need to process this deputy any further as they already have an associated user
                        continue;
                    }
                }
            } else {
                $deputy = $this->deputyService->createDeputyFromUser($user);
            }

            if (!is_null($deputy)) {
                $deputy->setUser($user);
                $this->deputyRepository->save($deputy);

                $user->setDeputy($deputy);
                $this->userRepository->save($user);

                $deputyUidsToIds[$deputyUid] = $deputy->getId();

                ++$numAssociations;
            }
        }

        return $numAssociations;
    }
}
