<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\TestHelpers;

use Doctrine\ORM\EntityManagerInterface;
use OPG\Digideps\Backend\Domain\Deputy\DeputyType;
use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Entity\User;
use Faker\Factory;

class DeputyTestHelper
{
    public static function generateDeputy(?string $email = null, ?string $deputyUid = null, ?User $user = null, ?EntityManagerInterface $em = null): Deputy
    {
        $faker = Factory::create('en_GB');

        $deputyUid = $user?->getDeputyUid() ?? $deputyUid;
        $deputy = null;
        if ($deputyUid !== null && $em !== null) {
            $deputy = $em->getRepository(Deputy::class)->findOneBy(['deputyUid' => (string)$deputyUid]);
        }

        $deputy = ($deputy ?? new Deputy())
            ->setDeputyUid((string)($deputyUid ?? $faker->randomNumber(8)))
            ->setFirstname(is_null($user) ? $faker->firstName() : $user->getFirstName())
            ->setLastname(is_null($user) ? $faker->lastName() : $user->getLastName())
            ->setEmail1($email ?: $faker->safeEmail() . rand(1, 100000))
            ->setAddress1(is_null($user) ? $faker->streetAddress() : $user->getAddress1())
            ->setAddress2(is_null($user) ? $faker->city() : $user->getAddress2())
            ->setAddress3(is_null($user) ? $faker->county : $user->getAddress3())
            ->setAddressPostcode(is_null($user) ? $faker->postcode() : $user->getAddressPostcode())
            ->setPhoneMain(is_null($user) ? $faker->phoneNumber() : $user->getPhoneMain())
            ->setDeputyType(DeputyType::LAY)
            ->setOrganisation(null);

        if (!is_null($user)) {
            $deputy->setUser($user);
            $user->setDeputyUid((int)$deputy->getDeputyUid());
        }

        return $deputy;
    }
}
