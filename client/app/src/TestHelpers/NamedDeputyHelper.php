<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\Deputy;
use Faker;

class NamedDeputyHelper
{
    public static function createNamedDeputy()
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