<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\Client;
use App\Entity\Deputy;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Faker\Factory;

class UserTestHelper
{
    public static function create(): self
    {
        return new UserTestHelper();
    }

    public static function createAndPersistUser(
        EntityManager $em,
        ?Client $client = null,
        ?string $roleName = User::ROLE_LAY_DEPUTY,
        ?string $email = null,
        ?int $deputyUid = null,
        bool $isPrimary = true,
    ): User {
        $user = self::createUser($client, $roleName, $email, $isPrimary, $deputyUid);

        if (!is_null($client)) {
            $em->persist($client);
        }

        $em->persist($user);
        $em->flush($user);

        return $user;
    }

    // set $deputyUid to -1 to get a null deputy UID (passing null for a lay means they end up with a generated UID)
    public static function createUser(
        ?Client $client = null,
        ?string $roleName = User::ROLE_LAY_DEPUTY,
        ?string $email = null,
        bool $isPrimary = true,
        ?int $deputyUid = null,
        ?string $firstName = null,
        ?string $lastName = null,
    ): User {
        $faker = Factory::create('en_GB');

        if (is_null($firstName)) {
            $firstName = $faker->firstName();
        }

        if (is_null($lastName)) {
            $lastName = $faker->lastName();
        }

        if (is_null($email)) {
            $email = $faker->safeEmail() . mt_rand(1, 100);
        }

        if (is_null($deputyUid)) {
            $deputyUid = intval('7' . str_pad((string) mt_rand(1, 99999999), 11, '0', STR_PAD_LEFT));
        }

        $user = (new User())
            ->setFirstname($firstName)
            ->setLastname($lastName)
            ->setEmail($email)
            ->setRoleName($roleName)
            ->setPhoneMain($faker->phoneNumber())
            ->setRegistrationDate(new \DateTime())
            ->setLastLoggedIn(new \DateTime())
            ->setActive(true)
            ->setAddress1($faker->streetAddress())
            ->setAddressCountry('GB')
            ->setAddressPostcode($faker->postcode())
            ->setAgreeTermsUse(true)
            ->setIsPrimary($isPrimary);

        if (-1 === $deputyUid) {
            $user->setDeputyUid(null);
        } elseif (str_contains($roleName, 'LAY')) {
            $user->setDeputyUid($deputyUid);
        }

        if (!is_null($client)) {
            $user->addClient($client);
        }

        return $user;
    }
}
