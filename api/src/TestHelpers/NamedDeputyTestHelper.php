<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\NamedDeputy;
use Faker\Factory;

class NamedDeputyTestHelper
{
    public function generateNamedDeputy()
    {
        $faker = Factory::create('en_GB');

        return (new NamedDeputy())
            ->setFirstname($faker->firstName)
            ->setLastname($faker->lastName)
            ->setEmail1($faker->safeEmail.rand(1, 100000))
            ->setAddress1($faker->streetAddress)
            ->setAddress2($faker->city)
            ->setAddress3($faker->county)
            ->setAddressPostcode($faker->postcode)
            ->setDeputyNo($faker->randomNumber(8));
    }
}
