<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\NamedDeputy;
use Faker\Factory;

class NamedDeputyTestHelper
{
    /**
     * @return NamedDeputy
     */
    public function generateNamedDeputy(?string $email = null, ?string $deputyUid = null)
    {
        $faker = Factory::create('en_GB');

        return (new NamedDeputy())
            ->setDeputyUid($deputyUid ?: $faker->randomNumber(8))
            ->setFirstname($faker->firstName())
            ->setLastname($faker->lastName())
            ->setEmail1($email ?: $faker->safeEmail().rand(1, 100000))
            ->setAddress1($faker->streetAddress())
            ->setAddress2($faker->city())
            ->setAddress3($faker->county)
            ->setAddressPostcode($faker->postcode())
            ->setPhoneMain($faker->phoneNumber());
    }
}
