<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\DeputyRepository;
use App\Repository\PreRegistrationRepository;
use App\Repository\UserRepository;

class UserDeputyService
{
    public function __construct(
        private readonly PreRegistrationRepository $preRegistrationRepository,
        private readonly DeputyService $deputyService,
        private readonly UserRepository $userRepository,
        private readonly DeputyRepository $deputyRepository,
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
        // find pre-reg rows whose deputy UIDs aren't in the deputy table
        $preRegs = $this->preRegistrationRepository->findWithoutDeputies();

        // add deputy records for those UIDs, using pre-reg data
        foreach ($preRegs as $preReg) {
            $this->deputyService->createDeputyFromPreRegistration($preReg);
        }

        // find users who have no deputy associated with them (but whose deputy UID is in the pre-reg table)
        $usersWithoutDeputies = $this->userRepository->findUsersWithoutDeputies();

        // get mapping from deputy UIDs to IDs (so we can quickly find the deputy ID from the user's deputy UID)
        $deputyUidsToIds = $this->deputyRepository->getUidToIdMapping();

        // associate users with deputies
        $numAssociations = 0;

        /** @var User $user */
        foreach ($usersWithoutDeputies as $user) {
            $deputyUid = "{$user->getDeputyUid()}";

            // this shouldn't happen, but better to be careful
            if (!array_key_exists($deputyUid, $deputyUidsToIds)) {
                continue;
            }

            $deputy = $this->deputyRepository->find($deputyUidsToIds[$deputyUid]);
            $user->setDeputy($deputy);
            $this->userRepository->save($user);

            ++$numAssociations;
        }

        return $numAssociations;
    }
}
