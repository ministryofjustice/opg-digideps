<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Deputy;
use App\Entity\PreRegistration;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Factory\OrganisationFactory;
use App\Repository\DeputyRepository;
use App\Repository\OrganisationRepository;
use Doctrine\Persistence\ObjectManager;

class ProfUserFixtures extends AbstractDataFixture
{
    private $fixtureData = [
        [
            'users' => [
                [
                    'id' => 'Prof-102-Named',
                    'email' => '@prof102s.gov.uk',
                    'roleName' => 'ROLE_PROF_NAMED',
                    'isDeputy' => true,
                    'orgName' => 'Prof 102 Org',
                    'count' => 1,
                ],
                [
                    'id' => 'Prof-102-Admin',
                    'email' => '@prof102s.gov.uk',
                    'roleName' => 'ROLE_PROF_ADMIN',
                    'isDeputy' => false,
                    'count' => 2,
                ],
                [
                    'id' => 'Prof-102-Member',
                    'email' => '@prof102s.gov.uk',
                    'roleName' => 'ROLE_PROF_TEAM_MEMBER',
                    'isDeputy' => false,
                    'count' => 2,
                ],
            ],
            // 10 clients of each report type
            'clients' => [
                [
                    'id' => 'Prof-OPG-102-5',
                    'caseNumber' => '71111000',
                    'reportType' => 'OPG102',
                    'orderType' => 'pfa',
                    'deputyUid' => '700771111000',
                    'count' => 10,
                ],
                [
                    'id' => 'Prof-OPG-102-5-4',
                    'caseNumber' => '72222000',
                    'reportType' => 'OPG102',
                    'orderType' => 'hw',
                    'deputyUid' => '700771111000',
                    'count' => 10,
                ],
            ],
        ],
        [
            'users' => [
                [
                    'id' => 'Prof-103-Named',
                    'email' => '@prof103s.gov.uk',
                    'roleName' => 'ROLE_PROF_NAMED',
                    'isDeputy' => true,
                    'orgName' => 'Prof 103 Org',
                    'count' => 1,
                ],
                [
                    'id' => 'Prof-103-Admin',
                    'email' => '@prof103s.gov.uk',
                    'roleName' => 'ROLE_PROF_ADMIN',
                    'isDeputy' => false,
                    'count' => 2,
                ],
                [
                    'id' => 'Prof-103-Member',
                    'email' => '@prof103s.gov.uk',
                    'roleName' => 'ROLE_PROF_TEAM_MEMBER',
                    'isDeputy' => false,
                    'count' => 2,
                ],
            ],
            'clients' => [
                [
                    'id' => 'Prof-OPG-103-5',
                    'caseNumber' => '73333000',
                    'reportType' => 'OPG103',
                    'orderType' => 'pfa',
                    'deputyUid' => '700773333000',
                    'count' => 10,
                ],
                [
                    'id' => 'Prof-OPG-103-5-4',
                    'caseNumber' => '74444000',
                    'reportType' => 'OPG103',
                    'orderType' => 'hw',
                    'deputyUid' => '700773333000',
                    'count' => 10,
                ],
            ],
        ],
        [
            'users' => [
                [
                    'id' => 'Prof-104-Named',
                    'email' => '@prof104s.gov.uk',
                    'roleName' => 'ROLE_PROF_NAMED',
                    'isDeputy' => true,
                    'orgName' => 'Prof 104 Org',
                    'count' => 1,
                ],
                [
                    'id' => 'Prof-104-Admin',
                    'email' => '@prof104s.gov.uk',
                    'roleName' => 'ROLE_PROF_ADMIN',
                    'isDeputy' => false,
                    'count' => 2,
                ],
                [
                    'id' => 'Prof-104-Member',
                    'email' => '@prof104s.gov.uk',
                    'roleName' => 'ROLE_PROF_TEAM_MEMBER',
                    'isDeputy' => false,
                    'count' => 2,
                ],
            ],
            'clients' => [
                [
                    'id' => 'Prof-OPG-104-5',
                    'caseNumber' => '75555000',
                    'reportType' => 'OPG104',
                    'orderType' => 'hw',
                    'deputyUid' => '700775555000',
                    'count' => 10,
                ],
            ],
        ],
    ];

    public function __construct(
        private OrganisationRepository $orgRepository,
        private OrganisationFactory $orgFactory,
        private DeputyRepository $deputyRepository,
    ) {
    }

    public function doLoad(ObjectManager $manager)
    {
        // Loop through data sets.
        // Creates users, clients, organisation and deputies
        foreach ($this->fixtureData as $data) {
            $this->createFixture($data, $manager);
        }

        $manager->flush();
    }

    private function createFixture($data, $manager)
    {
        $deputyData = null;
        $organisation = null;
        // Create number of users for each user type
        foreach ($data['users'] as $userData) {
            for ($i = 1; $i <= $userData['count']; ++$i) {
                $fullEmail = $userData['id'].'-'.$i.$userData['email'];

                // Set the $deputyData when we are processing the named deputy
                if (null === $deputyData) {
                    $deputyData = $userData['isDeputy'] ? $userData : null;
                }

                // Create user
                $user = $this->createUser($userData, $i);

                $manager->persist($user);

                $organisation = $this->orgRepository->findByEmailIdentifier($fullEmail);
                // Create organisation if it doesn't exist
                if (null === $organisation) {
                    $organisation = $this->orgFactory->createFromFullEmail($userData['orgName'] ?? 'Org Name', $fullEmail, true);

                    $manager->persist($organisation);
                    $manager->flush($organisation);
                }

                // Add user to organisation
                $organisation->addUser($user);
            }
        }

        // Create number of clients for each client type
        foreach ($data['clients'] as $clientData) {
            for ($i = 1; $i <= $clientData['count']; ++$i) {
                // Create client
                $client = $this->createClient($clientData, $i);

                $deputy = $this->deputyRepository->findOneBy(['deputyUid' => $clientData['deputyUid']]);
                if (null === $deputy) {
                    // Create named deputy if they don't exist
                    $deputy = $this->createDeputy($deputyData, $clientData);

                    $manager->persist($deputy);
                    $manager->flush($deputy);
                }

                // Set the named deputy on the client
                $client->setDeputy($deputy);

                // Add the client to the organisation
                $organisation->addClient($client);
                $client->setOrganisation($organisation);

                $manager->persist($client);

                // Create report for client
                $this->createReport($clientData, $client, $manager);
            }
        }
    }

    private function createUser($userData, $iteration)
    {
        return (new User())
            ->setFirstname($userData['id'].'-'.$iteration)
            ->setLastname('User')
            ->setEmail($userData['id'].'-'.$iteration.$userData['email'])
            ->setActive(true)
            ->setRegistrationDate(new \DateTime())
            ->setNdrEnabled(false)
            ->setPhoneMain('07911111111111')
            ->setAddress1('ABC Road')
            ->setAddressPostcode('AB1 2CD')
            ->setAddressCountry('GB')
            ->setRoleName($userData['roleName'])
            ->setAgreeTermsUse(true);
    }

    private function createClient($clientData, $iteration)
    {
        $offset = strlen((string) abs($iteration));

        return (new Client())
            ->setCaseNumber(substr_replace($clientData['caseNumber'], $iteration, -$offset))
            ->setFirstname($clientData['id'])
            ->setLastname('Client '.$iteration)
            ->setEmail(strtolower($clientData['id']).'-client-'.$iteration.'@example.com')
            ->setPhone('07811111111111')
            ->setAddress('ABC Road')
            ->setPostcode('AB1 2CD')
            ->setCountry('GB')
            ->setCourtDate(\DateTime::createFromFormat('d/m/Y', '01/11/2017'));
    }

    private function createReport($clientData, $client, $manager)
    {
        $realm = PreRegistration::REALM_PROF;
        $type = PreRegistration::getReportTypeByOrderType($clientData['reportType'], $clientData['orderType'], $realm);

        $startDate = $client->getExpectedReportStartDate();
        $startDate->setDate('2016', intval($startDate->format('m')), intval($startDate->format('d')));

        $endDate = $client->getExpectedReportEndDate();
        $endDate->setDate('2017', intval($endDate->format('m')), intval($endDate->format('d')));

        $report = new Report($client, $type, $startDate, $endDate);

        $manager->persist($report);
    }

    private function createDeputy(mixed $deputyData, mixed $clientData)
    {
        return (new Deputy())
            ->setFirstname($deputyData['id'])
            ->setLastname('Deputy')
            ->setDeputyUid($clientData['deputyUid'])
            ->setEmail1($deputyData['id'].$deputyData['email'])
            ->setAddress1('ABC Road')
            ->setAddressPostcode('AB1 2CD')
            ->setAddressCountry('GB');
    }

    protected function getEnvironments()
    {
        return ['dev', 'local'];
    }
}
