<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Deputy;
use App\Entity\PreRegistration;
use App\Entity\User;
use App\Repository\DeputyRepository;
use App\Repository\PreRegistrationRepository;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;

class UserDeputyService
{
    public function __construct(
        private readonly PreRegistrationRepository $preRegistrationRepository,
        private readonly DeputyService $deputyService,
        private readonly UserRepository $userRepository,
        private readonly DeputyRepository $deputyRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Create deputy records for deputy UIDs in pre_registration where they don't exist.
     * Associate users with deputy records where they aren't already associated.
     *
     * @return int Number of associations between deputies and users which were added
     */
    public function addMissingUserDeputies(): int
    {
        // find users who have no deputy associated with them (but whose deputy UID is in the pre-reg table)
        $usersWithoutDeputies = $this->userRepository->findUsersWithoutDeputies();

        // get mapping from deputy UIDs to IDs (so we can quickly find the deputy ID from the user's deputy UID)
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
                /** @var ?User $exitingUser */
                $exitingUser = $deputy->getUser();

                if (is_null($exitingUser)) {
                    $this->logger->error(
                        sprintf(
                            'Deputy with ID:%s already associated with a User under ID:%s',
                            $deputy->getId(),
                            $exitingUser->getId()
                        )
                    );
                    continue;
                }
            } else {
                // get pre-reg row for this deputy UID
                /** @var ?PreRegistration $preReg */
                $preReg = $this->preRegistrationRepository->findOneBy(['deputyUid' => $deputyUid]);

                // create the deputy from the pre-reg row and user data; NB if $preReg is null, deputy will remain null
                $deputy = $this->deputyService->createDeputyFromPreRegistration($preReg, ['email' => $user->getEmail()]);

                if (!is_null($deputy)) {
                    $this->deputyRepository->save($deputy);
                }
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
