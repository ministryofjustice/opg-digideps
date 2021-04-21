<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\Report\Satisfaction;
use Faker\Factory;

class SatisfactionHelper
{
    /**
     * @return Satisfaction
     */
    public static function createSatisfaction(): Satisfaction
    {
        $faker = Factory::create('en_GB');

        return (new Satisfaction())
            ->setComments($faker->text(250))
            ->setCreated($faker->dateTime)
            ->setDeputyrole($faker->randomKey(['ROLE_LAY_DEPUTY', 'ROLE_PROF_ADMIN', 'ROLE_PA_NAMED']))
            ->setReporttype('102')
            ->setScore($faker->numberBetween(1, 5));
    }
}
