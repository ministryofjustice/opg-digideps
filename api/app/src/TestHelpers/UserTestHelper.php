<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\Client;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class UserTestHelper extends TestCase
{
    public function createUserMock(string $roleName, bool $hasReports, bool $hasClients, int $id)
    {
        $clientTestHelper = new ClientTestHelper();

        $clients = $hasClients ? [$clientTestHelper->createClientMock(1, $hasReports)] : null;

        $user = self::prophesize(User::class);
        $user->getRoleName()->willReturn($roleName);
        $user->getClients()->willReturn($clients);
        $user->hasReports()->willReturn($hasReports);
        $user->getId()->willReturn($id);

        return $user->reveal();
    }

    public function createAndPersistUser(EntityManager $em, ?Client $client = null, ?string $roleName = User::ROLE_LAY_DEPUTY, ?string $email = null)
    {
        $user = $this->createUser($client, $roleName, $email);

        if (!is_null($client)) {
            $em->persist($client);
        }

        $em->persist($user);
        $em->flush();

        return $user;
    }

    public function createUser(
        ?Client $client = null,
        ?string $roleName = User::ROLE_LAY_DEPUTY,
        ?string $email = null,
        bool $isPrimary = true,
        ?int $deputyUid = null,
        ?string $firstName = null,
        ?string $lastName = null,
    ) {
        $faker = Factory::create('en_GB');

        if (is_null($firstName)) {
            $firstName = $faker->firstName();
        }
        if (is_null($lastName)) {
            $lastName = $faker->lastName();
        }

        $user = (new User())
            ->setFirstname($firstName)
            ->setLastname($lastName)
            ->setEmail($email ?: $faker->safeEmail().mt_rand(1, 100))
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

        if (str_contains($roleName, 'LAY')) {
            $user->setDeputyUid($deputyUid ?: intval('7'.str_pad((string) mt_rand(1, 99999999), 11, '0', STR_PAD_LEFT)));
        }

        if (!is_null($client)) {
            $user->addClient($client);
        }

        return $user;
    }
}
