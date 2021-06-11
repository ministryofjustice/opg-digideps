<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;

class AdminUserFixtures extends AbstractDataFixture
{
    public function doLoad(ObjectManager $manager)
    {
        // Add admin users
        $adminUser = (new User())
            ->setFirstname('Admin')
            ->setLastname('User')
            ->setEmail('admin@publicguardian.gov.uk')
            ->setActive(true)
            ->setRoleName('ROLE_ADMIN');

        $adminManager = (new User())
            ->setFirstname('Admin Manager')
            ->setLastname('User')
            ->setEmail('admin-manager@publicguardian.gov.uk')
            ->setActive(true)
            ->setRoleName('ROLE_ADMIN_MANAGER');

        $superAdminUser = (new User())
            ->setFirstname('Super Admin')
            ->setLastname('User')
            ->setEmail('super-admin@publicguardian.gov.uk')
            ->setActive(true)
            ->setRoleName('ROLE_SUPER_ADMIN');

        $adUser = (new User())
            ->setFirstname('AD user')
            ->setLastname('ADsurname')
            ->setEmail('behat-ad@publicguardian.gov.uk')
            ->setActive(true)
            ->setRoleName('ROLE_AD');

        $caseManager = (new User())
            ->setFirstname('Case')
            ->setLastname('Manager1')
            ->setEmail('casemanager@publicguardian.gov.uk')
            ->setActive(true)
            ->setRoleName('ROLE_ADMIN');

        $manager->persist($adminUser);
        $manager->persist($adminManager);
        $manager->persist($superAdminUser);
        $manager->persist($adUser);
        $manager->persist($caseManager);

        $manager->flush();
    }

    protected function getEnvironments()
    {
        return ['dev'];
    }
}
