<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\Deputy;
use App\Entity\User;
use Faker\Factory;

class DeputyTestHelper
{
    public static function generateDeputy(?string $email = null, ?string $deputyUid = null, ?User $user = null): Deputy
    {
        $faker = Factory::create('en_GB');

        $deputy =  (new Deputy())
            ->setDeputyUid($deputyUid ?: $faker->randomNumber(8))
            ->setFirstname($faker->firstName())
            ->setLastname($faker->lastName())
            ->setEmail1($email ?: $faker->safeEmail().rand(1, 100000))
            ->setAddress1($faker->streetAddress())
            ->setAddress2($faker->city())
            ->setAddress3($faker->county)
            ->setAddressPostcode($faker->postcode())
            ->setPhoneMain($faker->phoneNumber());

        if (!is_null($user)) {
            $deputy->setUser($user);
        }

        return $deputy;
    }
}
