<?php
namespace AppBundle\DataFixtures;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixtures extends AbstractDataFixture
{
    private $userData = [
        [
            'id' => '102',
            'deputyType' => 'LAY',
            'reportType' => 'OPG102',
            'reportVariation' => 'L2',
        ],
        [
            'id' => '103',
            'deputyType' => 'LAY',
            'reportType' => 'OPG103',
            'reportVariation' => 'L3',
        ],
        [
            'id' => '104',
            'deputyType' => 'LAY',
            'reportType' => null,
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-4',
            'deputyType' => 'LAY',
            'reportType' => 'OPG103',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '102-4',
            'deputyType' => 'LAY',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-6',
            'deputyType' => 'PA',
            'reportType' => 'OPG103',
            'reportVariation' => 'A3',
        ],
        [
            'id' => '102-6',
            'deputyType' => 'PA',
            'reportType' => 'OPG102',
            'reportVariation' => 'A2',
        ],
        [
            'id' => '104-6',
            'deputyType' => 'PA',
            'reportType' => null,
            'reportVariation' => 'HW',
        ],
        [
            'id' => '102-4-6',
            'deputyType' => 'PA',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-4-6',
            'deputyType' => 'PA',
            'reportType' => 'OPG103',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-5',
            'deputyType' => 'PROF',
            'reportType' => 'OPG103',
            'reportVariation' => 'P3',
        ],
        [
            'id' => '102-5',
            'deputyType' => 'PROF',
            'reportType' => 'OPG102',
            'reportVariation' => 'P2',
        ],
        [
            'id' => '104-5',
            'deputyType' => 'PROF',
            'reportType' => null,
            'reportVariation' => 'HW',
        ],
        [
            'id' => '102-4-5',
            'deputyType' => 'PROF',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-4-5',
            'deputyType' => 'PROF',
            'reportType' => 'OPG103',
            'reportVariation' => 'HW',
        ],
        [
            'id' => 'ndr',
            'deputyType' => 'LAY',
            'reportType' => 'OPG102',
            'reportVariation' => 'L2',
            'ndr' => true,
        ],
        [
            'id' => 'codep',
            'deputyType' => 'LAY',
            'reportType' => 'OPG102',
            'reportVariation' => 'L2',
            'codeputyEnabled' => true,
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

    private function addUser($data, $manager) {
        // Create user
        $user = (new User())
            ->setFirstname(ucfirst($data['deputyType']) . ' Deputy ' . $data['id'])
            ->setLastname('User')
            ->setEmail('behat-' . strtolower($data['deputyType']) .  '-deputy-' . $data['id'] . '@publicguardian.gov.uk')
            ->setActive(true)
            ->setRegistrationDate(new \DateTime())
            ->setNdrEnabled(isset($data['ndr']))
            ->setCoDeputyClientConfirmed(isset($data['codeputyEnabled']))
            ->setPhoneMain('07911111111111')
            ->setAddress1('Victoria Road')
            ->setAddressPostcode('SW1')
            ->setAddressCountry('GB')
            ->setRoleName($data['deputyType'] === 'LAY' ? 'ROLE_LAY_DEPUTY' : 'ROLE_' . $data['deputyType'] . '_NAMED');

        $manager->persist($user);

        // Create CasRec record for lay deputies
        if ($data['deputyType'] === 'LAY') {
            $casRec = new CasRec([
                'Case' => $data['id'],
                'Surname' => $data['id'],
                'Deputy No' => str_replace('-', '', $data['id']),
                'Dep Surname' => 'User',
                'Dep Postcode' => 'SW1',
                'Typeofrep' => $data['reportType'],
                'Corref' => $data['reportVariation'],
            ]);
            $manager->persist($casRec);
        }

        // Create client
        $client = new Client();
        $client
            ->setCaseNumber($data['id'])
            ->setFirstname('John')
            ->setLastname($data['id'] . '-client')
            ->setPhone('022222222222222')
            ->setAddress('Victoria road')
            ->setCourtDate(\DateTime::createFromFormat('d/m/Y', '01/11/2017'));

        $manager->persist($client);
        $user->addClient($client);

        if (!$client->getNdr()) {
            $ndr = new Ndr($client);
            $manager->persist($ndr);
        }

        // Create report for PROF/PA user 2 years ago
        if ($data['deputyType'] === 'PROF' || $data['deputyType'] === 'PA') {
            $type = CasRec::getTypeBasedOnTypeofRepAndCorref($data['reportType'], $data['reportVariation'], $user->getRoleName());
            $startDate = $client->getExpectedReportStartDate();
            $startDate->sub(new \DateInterval('P2Y'));
            $endDate = $client->getExpectedReportEndDate();
            $endDate->sub(new \DateInterval('P2Y'));

            $report = new Report($client, $type, $startDate, $endDate);

            $manager->persist($report);
        }

        // If codeputy was enabled, add a secondary account
        if (isset($data['codeputyEnabled'])) {
            $user2 = clone $user;
            $user2->setLastname($user2->getLastname() . '-2');
            $user2->setEmail('behat-' . strtolower($data['deputyType']) .  '-deputy-' . $data['id'] . '-2@publicguardian.gov.uk');
            $user2->addClient($client);

            $manager->persist($user2);
        }
    }

    protected function getEnvironments()
    {
        return ['dev'];
    }
}
