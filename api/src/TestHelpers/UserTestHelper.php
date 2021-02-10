<?php declare(strict_types=1);


namespace App\TestHelpers;

use App\Entity\Client;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManager;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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

    public function createUser(?Client $client = null, ?string $roleName = User::ROLE_LAY_DEPUTY)
    {
        $faker = Factory::create('en_GB');

        $user = (new User)
            ->setFirstname($faker->firstName)
            ->setLastname($faker->lastName)
            ->setEmail($faker->safeEmail)
            ->setRoleName($roleName)
            ->setPhoneMain($faker->phoneNumber)
            ->setRegistrationDate(new DateTime())
            ->setLastLoggedIn(new DateTime());

        if (!is_null($client)) {
            $user->addClient($client);
        }

        return $user;
    }

    public function createAndPersistUser(EntityManager $em, ?Client $client = null, ?string $roleName = User::ROLE_LAY_DEPUTY)
    {
        $user = $this->createUser($client, $roleName);

        if (!is_null($client)) {
            $em->persist($client);
        }

        $em->persist($user);
        $em->flush();

        return $user;
    }
}
