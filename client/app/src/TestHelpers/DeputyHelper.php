<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\TestHelpers;

use OPG\Digideps\Frontend\Entity\Deputy;
use Faker;

class DeputyHelper
{
    public static function createDeputy()
    {
        $faker = Faker\Factory::create();

        return (new Deputy())
            ->setFirstname($faker->firstName())
            ->setLastname($faker->lastName())
            ->setEmail1($faker->safeEmail())
            ->setPhoneMain($faker->phoneNumber())
            ->setId(1);
    }
}
