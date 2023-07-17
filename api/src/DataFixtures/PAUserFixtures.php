<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\NamedDeputy;
use App\Entity\PreRegistration;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Factory\OrganisationFactory;
use App\Repository\NamedDeputyRepository;
use App\Repository\OrganisationRepository;
use Doctrine\Persistence\ObjectManager;

class PAUserFixtures extends AbstractDataFixture
{
    private $fixtureData = [
        [
            'users' => [
                [
                    'id' => 'PA-102-Named',
                    'email' => 'PA-102-Named@pa102s.gov.uk',
                    'roleName' => 'ROLE_PA_NAMED',
                    'isNamedDeputy' => true,
                    'orgName' => 'PA 102 Org',
                ],
                [
                    'id' => 'PA-102-Admin',
                    'email' => 'PA-102-Admin@pa102s.gov.uk',
                    'roleName' => 'ROLE_PA_ADMIN',
                    'isNamedDeputy' => false,
                ],
                [
                    'id' => 'PA-102-Member',
                    'email' => 'PA-102-Member@pa102s.gov.uk',
                    'roleName' => 'ROLE_PA_TEAM_MEMBER',
                    'isNamedDeputy' => false,
                ],
            ],
            'clients' => [
                [
                    'id' => 'PA-OPG-102-6',
                    'caseNumber' => '71111000',
                    'reportType' => 'OPG102',
                    'orderType' => 'pfa',
                    'namedDeputyUid' => '700777777000',
                ],
                [
                    'id' => 'PA-OPG-102-6-4',
                    'caseNumber' => '71111001',
                    'reportType' => 'OPG102',
                    'orderType' => 'hw',
                    'namedDeputyUid' => '700777777000',
                ],
            ],
        ],
        [
            'users' => [
                [
                    'id' => 'PA-103-Named',
                    'email' => 'PA-103-Named@pa103s.gov.uk',
                    'roleName' => 'ROLE_PA_NAMED',
                    'isNamedDeputy' => true,
                    'orgName' => 'PA 103 Org',
                ],
                [
                    'id' => 'PA-103-Admin',
                    'email' => 'PA-103-Admin@pa103s.gov.uk',
                    'roleName' => 'ROLE_PA_ADMIN',
                    'isNamedDeputy' => false,
                ],
                [
                    'id' => 'PA-103-Member',
                    'email' => 'PA-103-Member@pa103s.gov.uk',
                    'roleName' => 'ROLE_PA_TEAM_MEMBER',
                    'isNamedDeputy' => false,
                ],
            ],
            'clients' => [
                [
                    'id' => 'PA-OPG-103-6',
                    'caseNumber' => '72111000',
                    'reportType' => 'OPG103',
                    'orderType' => 'pfa',
                    'namedDeputyUid' => '700788888000',
                ],
                [
                    'id' => 'PA-OPG-103-6-4',
                    'caseNumber' => '72111001',
                    'reportType' => 'OPG103',
                    'orderType' => 'hw',
                    'namedDeputyUid' => '700788888000',
                ],
            ],
        ],
        [
            'users' => [
                [
                    'id' => 'PA-104-Named',
                    'email' => 'PA-104-Named@pa104s.gov.uk',
                    'roleName' => 'ROLE_PA_NAMED',
                    'isNamedDeputy' => true,
                    'orgName' => 'PA 104 Org',
                ],
                [
                    'id' => 'PA-104-Admin',
                    'email' => 'PA-104-Admin@pa104s.gov.uk',
                    'roleName' => 'ROLE_PA_ADMIN',
                    'isNamedDeputy' => false,
                ],
                [
                    'id' => 'PA-104-Member',
                    'email' => 'PA-104-Member@pa104s.gov.uk',
                    'roleName' => 'ROLE_PA_TEAM_MEMBER',
                    'isNamedDeputy' => false,
                ],
            ],
            'clients' => [
                [
                    'id' => 'PA-OPG-104-6',
                    'caseNumber' => '73111000',
                    'reportType' => 'OPG104',
                    'orderType' => 'hw',
                    'namedDeputyUid' => '700799999000',
                ],
            ],
        ],
    ];

    public function __construct(
        private OrganisationRepository $orgRepository,
        private OrganisationFactory $orgFactory,
        private NamedDeputyRepository $namedDeputyRepository
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

        // Create users
        foreach ($data['users'] as $userData) {
            // Set the $namedDeputyData when we are processing the named deputy
            if (null === $namedDeputyData) {
                $namedDeputyData = $userData['isNamedDeputy'] ? $userData : null;
            }

            // Create user
            $user = $this->createUser($userData);

            $manager->persist($user);

            $organisation = $this->orgRepository->findByEmailIdentifier($userData['email']);
            // Create organisation if it doesn't exist
            if (null === $organisation) {
                $organisation = $this->orgFactory->createFromFullEmail($userData['orgName'] ?? 'Org Name', $userData['email']);

                $manager->persist($organisation);
                $manager->flush($organisation);
            }

            // Add user to organisation
            $organisation->addUser($user);
        }

        // Create clients
        foreach ($data['clients'] as $clientData) {
            // Create client
            $client = $this->createClient($clientData);

            $namedDeputy = $this->namedDeputyRepository->findOneBy(['deputyUid' => $clientData['namedDeputyUid']]);
            if (null === $namedDeputy) {
                // Create named deputy if they don't exist
                $namedDeputy = $this->createNamedDeputy($namedDeputyData, $clientData);

                $manager->persist($namedDeputy);
                $manager->flush($namedDeputy);
            }

            // Set the named deputy on the client
            $client->setNamedDeputy($namedDeputy);

            // Add the client to the organisation
            $organisation->addClient($client);
            $client->setOrganisation($organisation);

            $manager->persist($client);

            // Create report for client
            $this->createReport($clientData, $client, $manager);
        }
    }

    private function createUser($userData)
    {
        return (new User())
            ->setFirstname($userData['id'])
            ->setLastname('User')
            ->setEmail($userData['email'])
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

    private function createClient($clientData)
    {
        return (new Client())
            ->setCaseNumber($clientData['caseNumber'])
            ->setFirstname($clientData['id'])
            ->setLastname('Client')
            ->setEmail(strtolower($clientData['id']).'-client-@example.com')
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
        return (new NamedDeputy())
            ->setFirstname($namedDeputyData['id'])
            ->setLastname('Named Deputy')
            ->setDeputyUid($clientData['namedDeputyUid'])
            ->setEmail1($namedDeputyData['email'])
            ->setAddress1('ABC Road')
            ->setAddressPostcode('AB1 2CD')
            ->setAddressCountry('GB');
    }

    protected function getEnvironments()
    {
        return ['dev'];
    }
}
