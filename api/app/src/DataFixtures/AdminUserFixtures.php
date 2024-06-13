<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;

class AdminUserFixtures extends AbstractDataFixture
{
    private $adminData = [
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
            'firstName' => 'AD user',
            'lastName' => 'ADsurname',
            'email' => 'behat-ad@publicguardian.gov.uk',
            'roleType' => 'ROLE_AD',
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

    public function doLoad(ObjectManager $manager)
    {
        // Add admin users
        foreach ($this->adminData as $data) {
            $this->addUser($data, $manager);
        }

        $manager->flush();
    }

    private function addUser(array $data, ObjectManager $manager)
    {
        $user = (new User())
            ->setFirstname($data['firstName'])
            ->setLastname($data['lastName'])
            ->setEmail($data['email'])
            ->setActive(true)
            ->setRoleName($data['roleType']);

        $manager->persist($user);
    }

    protected function getEnvironments()
    {
        return ['dev', 'local'];
    }
}
