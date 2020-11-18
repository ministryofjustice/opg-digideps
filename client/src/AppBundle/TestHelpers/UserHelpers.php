<?php declare(strict_types=1);


namespace AppBundle\TestHelpers;

use AppBundle\Entity\User;
use Faker;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserHelpers extends KernelTestCase
{
    /**
     * @param array|null $data
     * @return User|array|object
     */
    public static function createUser(?array $data = null)
    {
        $container = (self::bootKernel())->getContainer();
        $serializer = $container->get('serializer');
        $faker = Faker\Factory::create();

        if (!empty($data)) {
            return $serializer->deserialize(json_encode($data), User::class, 'json');
        }

        return (new User())
            ->setId($faker->numberBetween(1, 99999999))
            ->setFirstname($faker->firstName)
            ->setLastname($faker->lastName)
            ->setRoleName($faker->jobTitle)
            ->setEmail($faker->safeEmail);
    }

    /**
     * @return User
     */
    public static function createProfAdminUser(?int $id = null): User
    {
        $faker = Faker\Factory::create();

        return self::createUser(
            [
                'id' => $id ? $id : 1,
                'firstname' => $faker->firstName,
                'lastname' => $faker->lastName,
                'role_name' => User::ROLE_PROF_ADMIN,
                'email' => $faker->safeEmail,
            ]
        );
    }

    /**
     * @return User
     */
    public static function createInvitedCoDeputy(): User
    {
        $faker = Faker\Factory::create();

        $invitedCoDeputy = self::createUser();

        foreach ($invitedCoDeputy as $key => $value) {
            unset($invitedCoDeputy->$key);
        }

        return $invitedCoDeputy->setEmail($faker->safeEmail);
    }
}
