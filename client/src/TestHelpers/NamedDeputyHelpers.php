<?php declare(strict_types=1);


namespace App\TestHelpers;

use App\Entity\NamedDeputy;
use Faker;

class NamedDeputyHelpers
{
    public static function createNamedDeputy()
    {
        $faker = Faker\Factory::create();

        return (new NamedDeputy())
            ->setFirstname($faker->firstName)
            ->setLastname($faker->lastName)
            ->setEmail1($faker->safeEmail)
            ->setPhoneMain($faker->phoneNumber)
            ->setId(1);
    }
}
