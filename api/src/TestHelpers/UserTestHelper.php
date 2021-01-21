<?php declare(strict_types=1);


namespace App\TestHelpers;

use App\Entity\Client;
use App\Entity\User;
use DateTime;
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

    public function createAndPersistUser(EntityManager $em, ?Client $client)
    {
        $faker = Factory::create('en_GB');

        $user = (new User)
            ->setFirstname($faker->firstName)
            ->setLastname($faker->lastName)
            ->setEmail($faker->safeEmail)
            ->setRoleName(User::ROLE_LAY_DEPUTY)
            ->setPhoneMain($faker->phoneNumber)
            ->setRegistrationDate(new DateTime());

        if (!is_null($client)) {
            $em->persist($client);
            $user->addClient($client);
        }

        $em->persist($user);
        $em->flush();

        return $user;
    }
}
