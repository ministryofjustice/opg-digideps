<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;

class TestUserFixtures extends AbstractDataFixture
{
    private $userData = [
        [
            'id' => 'deputy',
            'roleName' => 'ROLE_LAY_DEPUTY',
            'deputyUid' => 123321456654,
        ],
        [
            'id' => 'multi-client-primary-deputy',
            'roleName' => 'ROLE_LAY_DEPUTY',
            'deputyUid' => 567890098765,
            'isPrimary' => true,
        ],
        [
            'id' => 'multi-client-non-primary-deputy',
            'roleName' => 'ROLE_LAY_DEPUTY',
            'deputyUid' => 567890098765,
        ],
        [
            'id' => 'main-deputy',
            'roleName' => 'ROLE_LAY_DEPUTY',
            'deputyUid' => 987654321001,
            'co-deputy' => true,
        ],
        [
            'id' => 'co-deputy',
            'roleName' => 'ROLE_LAY_DEPUTY',
            'deputyUid' => 987654321002,
            'co-deputy' => true,
        ],
        [
            'id' => 'admin',
            'roleName' => 'ROLE_ADMIN',
        ],
        [
            'id' => 'super_admin',
            'roleName' => 'ROLE_SUPER_ADMIN',
        ],
        [
            'id' => 'pa',
            'roleName' => 'ROLE_PA_NAMED',
        ],
        [
            'id' => 'pa_admin',
            'roleName' => 'ROLE_PA_ADMIN',
        ],
        [
            'id' => 'pa_team_member',
            'roleName' => 'ROLE_PA_TEAM_MEMBER',
        ],
        [
            'id' => 'prof',
            'roleName' => 'ROLE_PROF_NAMED',
        ],
    ];

    public function doLoad(ObjectManager $manager)
    {
        // Add users from array
        foreach ($this->userData as $data) {
            $this->addUser($data, $manager);
        }

        $manager->flush();
    }

    private function addUser($data, $manager)
    {
        // Create user
        $user = (new User())
            ->setFirstname('test')
            ->setLastname($data['id'])
            ->setEmail($data['id'].'@example.org')
            ->setActive(true)
            ->setRegistrationDate(new \DateTime())
            ->setNdrEnabled(false)
            ->setPhoneMain('07911111111111')
            ->setAddress1('Victoria Road')
            ->setAddressPostcode('SW1')
            ->setAddressCountry('GB')
            ->setRoleName($data['roleName'])
            ->setDeputyUid($data['deputyUid'] ?? null)
            ->setCoDeputyClientConfirmed($data['co-deputy'] ?? false)
            ->setIsPrimary($data['isPrimary'] ?? false);

        $manager->persist($user);
    }

    protected function getEnvironments()
    {
        return ['test'];
    }
}
