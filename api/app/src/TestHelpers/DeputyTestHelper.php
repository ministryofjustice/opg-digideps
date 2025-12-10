<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\Deputy;
use App\Entity\User;
use App\Tests\Behat\v2\Common\UserDetails;
use Faker\Factory;

class DeputyTestHelper
{
    public static function generateDeputy(?string $email = null, ?string $deputyUid = null, User $user = null): Deputy
    {
        $faker = Factory::create('en_GB');

        $deputy = (new Deputy())
            ->setDeputyUid($deputyUid ?: '' . $faker->randomNumber(8))
            ->setFirstname(is_null($user) ? $faker->firstName() : $user->getFirstName())
            ->setLastname(is_null($user) ? $faker->lastName() : $user->getLastName())
            ->setEmail1($email ?: $faker->safeEmail() . rand(1, 100000))
            ->setAddress1(is_null($user) ? $faker->streetAddress() : $user->getAddress1())
            ->setAddress2(is_null($user) ? $faker->city() : $user->getAddress2())
            ->setAddress3(is_null($user) ? $faker->county : $user->getAddress3())
            ->setAddressPostcode(is_null($user) ? $faker->postcode() : $user->getAddressPostcode())
            ->setPhoneMain(is_null($user) ? $faker->phoneNumber() : $user->getPhoneMain());

        if (!is_null($user)) {
            $deputy->setUser($user);
        }

        return $deputy;
    }
}
