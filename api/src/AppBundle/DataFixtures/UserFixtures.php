<?php
namespace AppBundle\DataFixtures;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Client;
use AppBundle\Entity\NamedDeputy;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use AppBundle\Factory\OrganisationFactory;
use AppBundle\FixtureFactory\ClientFactory;
use AppBundle\FixtureFactory\ReportFactory;
use AppBundle\FixtureFactory\UserFactory;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixtures extends AbstractDataFixture
{
    private $userData = [
        [
            'id' => '102',
            'deputyType' => User::TYPE_LAY,
            'reportType' => Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS,
            'reportVariation' => 'L2',
        ],
        [
            'id' => '103',
            'deputyType' => User::TYPE_LAY,
            'reportType' => Report::TYPE_PROPERTY_AND_AFFAIRS_LOW_ASSETS,
            'reportVariation' => 'L3',
        ],
        [
            'id' => '104',
            'deputyType' => User::TYPE_LAY,
            'reportType' => Report::TYPE_HEALTH_WELFARE,
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-4',
            'deputyType' => User::TYPE_LAY,
            'reportType' => Report::TYPE_COMBINED_LOW_ASSETS,
            'reportVariation' => 'HW',
        ],
        [
            'id' => '102-4',
            'deputyType' => User::TYPE_LAY,
            'reportType' => Report::TYPE_COMBINED_HIGH_ASSETS,
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-6',
            'deputyType' => User::TYPE_PA,
            'reportType' => Report::TYPE_PROPERTY_AND_AFFAIRS_LOW_ASSETS,
            'reportVariation' => 'A3',
        ],
        [
            'id' => '102-6',
            'deputyType' => User::TYPE_PA,
            'reportType' => Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS,
            'reportVariation' => 'A2',
        ],
        [
            'id' => '104-6',
            'deputyType' => User::TYPE_PA,
            'reportType' => Report::TYPE_HEALTH_WELFARE,
            'reportVariation' => 'HW',
        ],
        [
            'id' => '102-4-6',
            'deputyType' => User::TYPE_PA,
            'reportType' => Report::TYPE_COMBINED_HIGH_ASSETS,
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-4-6',
            'deputyType' => User::TYPE_PA,
            'reportType' => Report::TYPE_COMBINED_LOW_ASSETS,
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-5',
            'deputyType' => User::TYPE_PROF,
            'reportType' => Report::TYPE_PROPERTY_AND_AFFAIRS_LOW_ASSETS,
            'reportVariation' => 'P3',
        ],
        [
            'id' => '102-5',
            'deputyType' => User::TYPE_PROF,
            'reportType' => Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS,
            'reportVariation' => 'P2',
        ],
        [
            'id' => '104-5',
            'deputyType' => User::TYPE_PROF,
            'reportType' => Report::TYPE_HEALTH_WELFARE,
            'reportVariation' => 'HW',
        ],
        [
            'id' => '102-4-5',
            'deputyType' => User::TYPE_PROF,
            'reportType' => Report::TYPE_COMBINED_HIGH_ASSETS,
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-4-5',
            'deputyType' => User::TYPE_PROF,
            'reportType' => Report::TYPE_COMBINED_LOW_ASSETS,
            'reportVariation' => 'HW',
        ],
        [
            'id' => 'ndr',
            'deputyType' => User::TYPE_LAY,
            'reportType' => Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS,
            'reportVariation' => 'L2',
            'ndr' => true,
        ],
        [
            'id' => 'codep',
            'deputyType' => User::TYPE_LAY,
            'reportType' => Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS,
            'reportVariation' => 'L2',
            'codeputyEnabled' => true,
        ],
        [
            'id' => 'example1',
            'email' => 'jo.brown@example.com',
            'deputyType' => User::TYPE_PROF,
            'reportType' => Report::TYPE_COMBINED_HIGH_ASSETS,
            'reportVariation' => 'HW',
        ],
        [
            'id' => 'example2',
            'email' => 'bobby.blue@example.com',
            'deputyType' => User::TYPE_PROF,
            'reportType' => Report::TYPE_COMBINED_HIGH_ASSETS,
            'reportVariation' => 'HW',
        ],
        [
            'id' => 'abc-ex1',
            'email' => 'john.smith@abc-solicitors.example.com',
            'deputyType' => User::TYPE_PROF,
            'reportType' => Report::TYPE_COMBINED_HIGH_ASSETS,
            'reportVariation' => 'HW',
        ],
        [
            'id' => 'abc-ex2',
            'email' => 'kieth.willis@abc-solicitors.example.com',
            'deputyType' => User::TYPE_PROF,
            'reportType' => Report::TYPE_COMBINED_HIGH_ASSETS,
            'reportVariation' => 'HW',
        ],
        [
            'id' => 'abcd-ex3',
            'email' => 'marjorie.watkins@abcd-solicitors.example.com',
            'deputyType' => User::TYPE_PROF,
            'reportType' => Report::TYPE_COMBINED_HIGH_ASSETS,
            'reportVariation' => 'HW',
        ]
    ];

    /** @var OrganisationFactory */
    private $orgFactory;

    /** @var UserFactory */
    private $userFactory;

    /** @var ReportFactory */
    private $reportFactory;

    /** @var ClientFactory */
    private $clientFactory;

    public function doLoad(ObjectManager $manager)
    {
        $this->orgRepository = $this->container->get('AppBundle\Entity\Repository\OrganisationRepository');
        $this->orgFactory = $this->container->get('AppBundle\Factory\OrganisationFactory');
        $this->userFactory = $this->container->get('AppBundle\FixtureFactory\UserFactory');
        $this->reportFactory = $this->container->get('AppBundle\FixtureFactory\ReportFactory');
        $this->clientFactory = $this->container->get('AppBundle\FixtureFactory\ClientFactory');

        // Add users from array
        foreach ($this->userData as $data) {
            //$this->addUser($data, $manager);
        }

        $manager->flush();
    }

    private function addUser($data, $manager) {
        // Create user
        $user = $this->userFactory->create($data);

        $manager->persist($user);

        // Create CasRec record for lay deputies
        if ($data['deputyType'] === User::TYPE_LAY) {
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
        $client = $this->clientFactory->create($data);

        if ($data['deputyType'] === User::TYPE_PROF || $data['deputyType'] === User::TYPE_PA) {
            $namedDeputy = new NamedDeputy();
            $namedDeputy
                ->setFirstname('Named')
                ->setLastname('Deputy ' . $data['id'])
                ->setDeputyNo('nd-' . $data['id'])
                ->setEmail1('behat-nd-' . $data['id'] . '@publicguardian.gov.uk')
                ->setPhoneMain('07911111111111')
                ->setAddress1('Victoria Road')
                ->setAddressPostcode('SW1')
                ->setAddressCountry('GB');

            $manager->persist($namedDeputy);

            $client->setNamedDeputy($namedDeputy);
        }

        $manager->persist($client);
        $user->addClient($client);

        if (!$client->getNdr()) {
            $ndr = new Ndr($client);
            $manager->persist($ndr);
        }

        // Create report for PROF/PA user 2 years ago
        if ($data['deputyType'] === User::TYPE_PROF || $data['deputyType'] === User::TYPE_PA) {
            $report = $this->container->get('AppBundle\FixtureFactory\ReportFactory')->create($data, $client);
            $startDate = $report->getStartDate();
            $startDate->setDate('2016', intval($startDate->format('m')), intval($startDate->format('d')));
            $endDate = $report->getEndDate();
            $endDate->setDate('2017', intval($endDate->format('m')), intval($endDate->format('d')));
            $manager->persist($report);

            if (isset($data['email'])) {
                $organisation = $this->orgRepository->findByEmailIdentifier($data['email']);
                if (null === $organisation) {
                    $organisation = $this->orgFactory->createFromFullEmail($data['email'], $data['email']);
                    $manager->persist($organisation);
                    $manager->flush($organisation);
                }
            }
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
