<?php
namespace AppBundle\DataFixtures;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;

class TestUserFixtures extends AbstractDataFixture
{
    private $userData = [
        [
            'id' => 'deputy',
            'roleName' => 'ROLE_LAY_DEPUTY',
        ],
        [
            'id' => 'admin',
            'roleName' => 'ROLE_ADMIN',
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
        [
            'id' => 'casemanager',
            'roleName' => 'ROLE_CASE_MANAGER',
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
            ->setEmail($data['id'] . '@example.org')
            ->setActive(true)
            ->setRegistrationDate(new \DateTime())
            ->setNdrEnabled(false)
            ->setPhoneMain('07911111111111')
            ->setAddress1('Victoria Road')
            ->setAddressPostcode('SW1')
            ->setAddressCountry('GB')
            ->setRoleName($data['roleName']);

        $manager->persist($user);
    }

    protected function getEnvironments()
    {
        return array('test');
    }
}
