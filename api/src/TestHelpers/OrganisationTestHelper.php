<?php declare(strict_types=1);


namespace App\TestHelpers;

use App\Entity\Client;
use App\Entity\Organisation;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManager;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class OrganisationTestHelper extends TestCase
{
    public function createOrganisationMock(string $roleName, bool $hasClients, bool $hasUsers, int $id)
    {
        $clientTestHelper = new ClientTestHelper();

        $clients = $hasClients ? [$clientTestHelper->createClientMock(1, false)] : null;

        $userTestHelper = new UserTestHelper();

        $users = $hasUsers ? [$userTestHelper->createUserMock('ROLE_PROF', false, $hasClients, 1)] : null;

        $org = self::prophesize(Organisation::class);
        $org->getUsers()->willReturn($users);
        $org->getClients()->willReturn($clients);
        $org->getId()->willReturn($id);

        return $org->reveal();
    }

    public function createOrganisation(?User $user = null, ?Client $client = null)
    {
        $faker = Factory::create('en_GB');

        $org = (new Organisation)
            ->setName($faker->company)
            ->setEmailIdentifier($faker->safeEmailDomain)
            ->setIsActivated(true);

        if (!is_null(user)) {
            $org->addUser($user);
        }

        if (!is_null($client)) {
            $org->addClient($client);
        }

        return $org;
    }
}
