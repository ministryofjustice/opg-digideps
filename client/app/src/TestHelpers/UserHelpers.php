<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\User;
use Faker;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserHelpers extends KernelTestCase
{
    /**
     * @return User|array|object
     */
    public static function createUser(?array $data = null)
    {
        $container = self::bootKernel()->getContainer();
        $serializer = $container->get('serializer');
        $faker = Faker\Factory::create();

        if (!empty($data)) {
            return $serializer->deserialize(json_encode($data), User::class, 'json');
        }

        return (new User())
            ->setId($faker->numberBetween(1, 999999999))
            ->setDeputyUid($faker->randomNumber(8))
            ->setFirstname($faker->firstName())
            ->setLastname($faker->lastName())
            ->setRoleName($faker->jobTitle())
            ->setEmail($faker->safeEmail());
    }

    /**
     * @return User|array|object
     */
    public static function createLayUser()
    {
        return self::createUser()->setRoleName(User::ROLE_LAY_DEPUTY);
    }

    public static function createInvitedCoDeputy(): User
    {
        $faker = Faker\Factory::create();

        $invitedCoDeputy = self::createUser();

        foreach ($invitedCoDeputy as $key => $value) {
            unset($invitedCoDeputy->$key);
        }

        return $invitedCoDeputy->setEmail($faker->safeEmail());
    }

    public static function createAdminUser(): User
    {
        return self::createUser()->setRoleName(User::ROLE_ADMIN)->setIsCaseManager(true);
    }

    public static function createSuperAdminUser(): User
    {
        return self::createUser()->setRoleName(User::ROLE_SUPER_ADMIN);
    }

    public static function createAdminManager(): User
    {
        return self::createUser()->setRoleName(User::ROLE_ADMIN_MANAGER)->setIsCaseManager(true);
    }

    public static function createProfDeputyUser(): User
    {
        return self::createUser()->setRoleName(User::ROLE_PROF);
    }

    public static function createProfNamedDeputyUser(): User
    {
        return self::createUser()->setRoleName(User::ROLE_PROF_NAMED);
    }

    public static function createProfAdminUser(): User
    {
        return self::createUser()->setRoleName(User::ROLE_PROF_ADMIN);
    }

    public static function createProfTeamMemberUser(): User
    {
        return self::createUser()->setRoleName(User::ROLE_PROF_TEAM_MEMBER);
    }

    public static function createPaDeputyUser(): User
    {
        return self::createUser()->setRoleName(User::ROLE_PA);
    }

    public static function createPaNamedDeputyUser(): User
    {
        return self::createUser()->setRoleName(User::ROLE_PA_NAMED);
    }

    public static function createPaAdminUser(): User
    {
        return self::createUser()->setRoleName(User::ROLE_PA_ADMIN);
    }

    public static function createPaTeamMemberUser(): User
    {
        return self::createUser()->setRoleName(User::ROLE_PA_TEAM_MEMBER);
    }
}
