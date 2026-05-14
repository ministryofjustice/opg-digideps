<?php

namespace OPG\Digideps\Backend\DataFixtures;

use OPG\Digideps\Backend\Entity\User;
use Doctrine\Persistence\ObjectManager;

class AdminUserFixtures extends AbstractDataFixture
{
    private array $adminData = [
        [
            'firstName' => 'Admin',
            'lastName' => 'User',
            'email' => 'admin@publicguardian.gov.uk',
            'roleType' => 'ROLE_ADMIN',
        ],
        [
            'firstName' => 'Admin Manager',
            'lastName' => 'User',
            'email' => 'admin-manager@publicguardian.gov.uk',
            'roleType' => 'ROLE_ADMIN_MANAGER',
        ],
        [
            'firstName' => 'Super Admin',
            'lastName' => 'User',
            'email' => 'super-admin@publicguardian.gov.uk',
            'roleType' => 'ROLE_SUPER_ADMIN',
        ],
        [
            'firstName' => 'Case',
            'lastName' => 'Manager1',
            'email' => 'casemanager1@publicguardian.gov.uk',
            'roleType' => 'ROLE_ADMIN',
        ],
        [
            'firstName' => 'Case',
            'lastName' => 'Manager2',
            'email' => 'casemanager2@publicguardian.gov.uk',
            'roleType' => 'ROLE_ADMIN',
        ],
        [
            'firstName' => 'Case',
            'lastName' => 'Manager3',
            'email' => 'casemanager3@publicguardian.gov.uk',
            'roleType' => 'ROLE_ADMIN',
        ],
        [
            'firstName' => 'SmokeyJoe',
            'lastName' => 'SmokeTest',
            'email' => 'smoketestddadmin@smoketest.com',
            'roleType' => 'ROLE_ADMIN',
        ],
    ];

    public function doLoad(ObjectManager $manager): void
    {
        // Add admin users
        foreach ($this->adminData as $data) {
            $this->addUser($data, $manager);
        }

        $manager->flush();
    }

    private function addUser(array $data, ObjectManager $manager): void
    {
        $user = new User()
            ->setFirstname($data['firstName'])
            ->setLastname($data['lastName'])
            ->setEmail($data['email'])
            ->setActive(true)
            ->setRoleName($data['roleType']);

        $manager->persist($user);
    }

    /** @return String[] */
    protected function getEnvironments(): array
    {
        return ['dev', 'local'];
    }
}
