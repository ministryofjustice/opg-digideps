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

class ProfUserFixtures extends AbstractDataFixture
{
    private $fixtureData = [
        [
            'users' => [
                [
                    'id' => 'Prof-102-Named',
                    'email' => 'Prof-102-Named@prof102s.gov.uk',
                    'roleName' => 'ROLE_PROF_NAMED',
                    'isNamedDeputy' => true,
                    'orgName' => 'Prof 102 Org',
                ],
                [
                    'id' => 'Prof-102-Admin',
                    'email' => 'Prof-102-Admin@prof102s.gov.uk',
                    'roleName' => 'ROLE_PROF_ADMIN',
                    'isNamedDeputy' => false,
                ],
                [
                    'id' => 'Prof-102-Member',
                    'email' => 'Prof-102-Member@prof102s.gov.uk',
                    'roleName' => 'ROLE_PROF_MEMBER',
                    'isNamedDeputy' => false,
                ],
            ],
            'clients' => [
                [
                    'id' => 'Prof-OPG-102-5',
                    'caseNumber' => '71111000',
                    'reportType' => 'OPG102',
                    'orderType' => 'pfa',
                    'namedDeputyUid' => '700787777000',
                ],
                [
                    'id' => 'Prof-OPG-102-5-4',
                    'caseNumber' => '71111001',
                    'reportType' => 'OPG102',
                    'orderType' => 'hw',
                    'namedDeputyUid' => '700787777000',
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
        $realm = PreRegistration::REALM_PROF;
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
