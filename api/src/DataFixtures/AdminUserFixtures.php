<?php
namespace AppBundle\DataFixtures;

use AppBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;

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
