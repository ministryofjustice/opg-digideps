<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\PreRegistrationRepository;
use App\Repository\UserRepository;

class UserDeputyService
{
    public function __construct(
        private readonly PreRegistrationRepository $preRegistrationRepository,
        private readonly DeputyService $deputyService,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * Create deputy records for deputy UIDs in pre_registration where they don't exist.
     * Associate users with deputy records where they aren't already associated.
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

        return 0;
    }
}
