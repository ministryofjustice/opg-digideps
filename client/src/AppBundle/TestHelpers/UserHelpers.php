<?php declare(strict_types=1);


namespace AppBundle\TestHelpers;

use AppBundle\Entity\User;
use Faker;

class UserHelpers
{
    public static function createUser()
    {
        $faker = Faker\Factory::create();

        return (new User())
            ->setFirstname($faker->firstName)
            ->setLastname($faker->lastName)
            ->setRoleName($faker->jobTitle)
            ->setEmail($faker->safeEmail);
    }
}
