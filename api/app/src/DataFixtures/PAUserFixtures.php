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

class PAUserFixtures extends AbstractDataFixture
{
    private $fixtureData = [
        [
            'users' => [
                [
                    'id' => 'PA-102-Named',
                    'email' => '@pa102s.gov.uk',
                    'roleName' => 'ROLE_PA_NAMED',
                    'isNamedDeputy' => true,
                    'orgName' => 'PA 102 Org',
                    'count' => 1,
                ],
                [
                    'id' => 'PA-102-Admin',
                    'email' => '@pa102s.gov.uk',
                    'roleName' => 'ROLE_PA_ADMIN',
                    'isNamedDeputy' => false,
                    'count' => 2,
                ],
                [
                    'id' => 'PA-102-Member',
                    'email' => '@pa102s.gov.uk',
                    'roleName' => 'ROLE_PA_TEAM_MEMBER',
                    'isNamedDeputy' => false,
                    'count' => 2,
                ],
            ],
            // 10 clients of each report type
            'clients' => [
                [
                    'id' => 'PA-OPG-102-5',
                    'caseNumber' => '81111000',
                    'reportType' => 'OPG102',
                    'orderType' => 'pfa',
                    'namedDeputyUid' => '700781111000',
                    'count' => 10,
                ],
                [
                    'id' => 'PA-OPG-102-5-4',
                    'caseNumber' => '82222000',
                    'reportType' => 'OPG102',
                    'orderType' => 'hw',
                    'namedDeputyUid' => '700781111000',
                    'count' => 10,
                ],
            ],
        ],
        [
            'users' => [
                [
                    'id' => 'PA-103-Named',
                    'email' => '@pa103s.gov.uk',
                    'roleName' => 'ROLE_PA_NAMED',
                    'isNamedDeputy' => true,
                    'orgName' => 'PA 103 Org',
                    'count' => 1,
                ],
                [
                    'id' => 'PA-103-Admin',
                    'email' => '@pa103s.gov.uk',
                    'roleName' => 'ROLE_PA_ADMIN',
                    'isNamedDeputy' => false,
                    'count' => 2,
                ],
                [
                    'id' => 'PA-103-Member',
                    'email' => '@pa103s.gov.uk',
                    'roleName' => 'ROLE_PA_TEAM_MEMBER',
                    'isNamedDeputy' => false,
                    'count' => 2,
                ],
            ],
            'clients' => [
                [
                    'id' => 'PA-OPG-103-5',
                    'caseNumber' => '83333000',
                    'reportType' => 'OPG103',
                    'orderType' => 'pfa',
                    'namedDeputyUid' => '700783333000',
                    'count' => 10,
                ],
                [
                    'id' => 'PA-OPG-103-5-4',
                    'caseNumber' => '84444000',
                    'reportType' => 'OPG103',
                    'orderType' => 'hw',
                    'namedDeputyUid' => '700783333000',
                    'count' => 10,
                ],
            ],
        ],
        [
            'users' => [
                [
                    'id' => 'PA-104-Named',
                    'email' => '@pa104s.gov.uk',
                    'roleName' => 'ROLE_PA_NAMED',
                    'isNamedDeputy' => true,
                    'orgName' => 'PA 104 Org',
                    'count' => 1,
                ],
                [
                    'id' => 'PA-104-Admin',
                    'email' => '@pa104s.gov.uk',
                    'roleName' => 'ROLE_PA_ADMIN',
                    'isNamedDeputy' => false,
                    'count' => 2,
                ],
                [
                    'id' => 'PA-104-Member',
                    'email' => '@pa104s.gov.uk',
                    'roleName' => 'ROLE_PA_TEAM_MEMBER',
                    'isNamedDeputy' => false,
                    'count' => 2,
                ],
            ],
            'clients' => [
                [
                    'id' => 'PA-OPG-104-5',
                    'caseNumber' => '85555000',
                    'reportType' => 'OPG104',
                    'orderType' => 'hw',
                    'namedDeputyUid' => '700785555000',
                    'count' => 10,
                ],
            ],
        ],
    ];

    public function __construct(
        private OrganisationRepository $orgRepository,
        private OrganisationFactory $orgFactory,
        private DeputyRepository $namedDeputyRepository
    ) {
    }

    public function doLoad(ObjectManager $manager)
    {
        // Loop through data sets.
        // Creates users, clients, organisation and named deputies
        foreach ($this->fixtureData as $data) {
            $this->createFixture($data, $manager);
        }

        $manager->flush();
    }

    private function createFixture($data, $manager)
    {
        $namedDeputyData = null;
        $organisation = null;
        // Create number of users for each user type
        foreach ($data['users'] as $userData) {
            for ($i = 1; $i <= $userData['count']; ++$i) {
                $fullEmail = $userData['id'].'-'.$i.$userData['email'];

                // Set the $namedDeputyData when we are processing the named deputy
                if (null === $namedDeputyData) {
                    $namedDeputyData = $userData['isNamedDeputy'] ? $userData : null;
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

                $namedDeputy = $this->namedDeputyRepository->findOneBy(['deputyUid' => $clientData['namedDeputyUid']]);
                if (null === $namedDeputy) {
                    // Create named deputy if they don't exist
                    $namedDeputy = $this->createNamedDeputy($namedDeputyData, $clientData);

                    $manager->persist($namedDeputy);
                    $manager->flush($namedDeputy);
                }

                // Set the named deputy on the client
                $client->setDeputy($namedDeputy);

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
        $realm = PreRegistration::REALM_PA;
        $type = PreRegistration::getReportTypeByOrderType($clientData['reportType'], $clientData['orderType'], $realm);

        $startDate = $client->getExpectedReportStartDate();
        $startDate->setDate('2016', intval($startDate->format('m')), intval($startDate->format('d')));

        $endDate = $client->getExpectedReportEndDate();
        $endDate->setDate('2017', intval($endDate->format('m')), intval($endDate->format('d')));

        $report = new Report($client, $type, $startDate, $endDate);

        $manager->persist($report);
    }

    private function createNamedDeputy(mixed $namedDeputyData, mixed $clientData)
    {
        return (new Deputy())
            ->setFirstname($namedDeputyData['id'])
            ->setLastname('Named Deputy')
            ->setDeputyUid($clientData['namedDeputyUid'])
            ->setEmail1($namedDeputyData['id'].$namedDeputyData['email'])
            ->setAddress1('ABC Road')
            ->setAddressPostcode('AB1 2CD')
            ->setAddressCountry('GB');
    }

    protected function getEnvironments()
    {
        return ['dev'];
    }
}
