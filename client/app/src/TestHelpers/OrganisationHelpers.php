<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\Organisation;
use Doctrine\Common\Collections\ArrayCollection;
use Faker\Factory;

class OrganisationHelpers
{
    public static function createActivatedOrganisation(): Organisation
    {
        $faker = Factory::create();
        $orgName = $faker->company();

        $organisation = (new Organisation())
            ->setId(1)
            ->setName($orgName)
            ->setEmailAddress(sprintf('info@%s', str_replace(' ', '', $orgName)))
            ->setEmailIdentifier(sprintf('@%s', str_replace(' ', '', $orgName)))
            ->setIsActivated(true);

        $orgUsers = [
            (UserHelpers::createUser())->setOrganisations(new ArrayCollection([$organisation])),
            (UserHelpers::createUser())->setOrganisations(new ArrayCollection([$organisation])),
        ];

        $orgClients = [
            (ClientHelpers::createClient())->addUser($orgUsers[0]),
            (ClientHelpers::createClient())->addUser($orgUsers[1]),
        ];

        return $organisation
            ->setUsers($orgUsers)
            ->setClients($orgClients);
    }

    public static function createInactiveOrganisation()
    {
        return (self::createActivatedOrganisation())->setIsActivated(false);
    }
}
