<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\User;
use App\Entity\UserResearch\ResearchType;
use App\Entity\UserResearch\UserResearchResponse;
use Faker\Factory;

class UserResearchResponseHelpers
{
    public static function createUserResearchResponse(): UserResearchResponse
    {
        $faker = Factory::create('en_GB');

        $researchType = (new ResearchType())
            ->setPhone(true);

        $satisfaction = SatisfactionHelpers::createSatisfaction();

        $user = match ($satisfaction->getDeputyRole()) {
            User::ROLE_LAY_DEPUTY => UserHelpers::createLayUser(),
            User::ROLE_PROF_ADMIN => UserHelpers::createProfAdminUser(),
            User::ROLE_PA_NAMED => UserHelpers::createPaNamedDeputyUser(),
            default => UserHelpers::createLayUser(),
        };

        return (new UserResearchResponse())
            ->setSatisfaction($satisfaction)
            ->setDeputyshipLength($faker->randomKey(['underOne', 'oneToFive', 'sixToTen', 'overTen']))
            ->setResearchType($researchType)
            ->setUser($user)
            ->setHasAccessToVideoCallDevice(true)
            ->setCreated($faker->dateTime);
    }
}
