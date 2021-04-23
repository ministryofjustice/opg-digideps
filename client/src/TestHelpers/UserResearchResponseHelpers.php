<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\User;
use App\Entity\UserResearch\ResearchType;
use App\Entity\UserResearch\UserResearchResponse;
use Faker\Factory;

class UserResearchResponseHelpers
{
    /**
     * @return UserResearchResponse
     */
    public static function createUserResearchResponse(): UserResearchResponse
    {
        $faker = Factory::create('en_GB');

        $researchType = (new ResearchType())
            ->setPhone(true);

        $satisfaction = SatisfactionHelpers::createSatisfaction();

        switch ($satisfaction->getDeputyRole()) {
            case User::ROLE_LAY_DEPUTY:
                $user = UserHelpers::createLayUser();
                break;
            case User::ROLE_PROF_ADMIN:
                $user = UserHelpers::createProfAdminUser();
                break;
            case User::ROLE_PA_NAMED:
                $user = UserHelpers::createPaNamedDeputyUser();
                break;
            default:
                $user = UserHelpers::createLayUser();
        }

        return (new UserResearchResponse())
            ->setSatisfaction($satisfaction)
            ->setDeputyshipLength($faker->randomKey(['underOne', 'oneToFive', 'sixToTen', 'overTen']))
            ->setResearchType($researchType)
            ->setUser($user)
            ->setHasAccessToVideoCallDevice(true)
            ->setCreated($faker->dateTime);
    }
}
