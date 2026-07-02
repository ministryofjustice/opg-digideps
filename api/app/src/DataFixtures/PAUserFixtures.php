<?php

namespace OPG\Digideps\Backend\DataFixtures;

use OPG\Digideps\Common\Deputy\DeputyType;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Entity\PreRegistration;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Factory\OrganisationFactory;
use OPG\Digideps\Backend\Repository\DeputyRepository;
use OPG\Digideps\Backend\Repository\OrganisationRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\KernelInterface;

class PAUserFixtures extends AbstractDataFixture
{
    /** @var array<array<string, mixed>>  */
    private array $fixtureData = [
        [
            'users' => [
                [
                    'id' => 'PA-102-Named',
                    'email' => '@pa102s.gov.uk',
                    'roleName' => 'ROLE_PA_NAMED',
                    'isDeputy' => true,
                    'orgName' => 'PA 102 Org',
                    'count' => 1,
                ],
                [
                    'id' => 'PA-102-Admin',
                    'email' => '@pa102s.gov.uk',
                    'roleName' => 'ROLE_PA_ADMIN',
                    'isDeputy' => false,
                    'count' => 2,
                ],
                [
                    'id' => 'PA-102-Member',
                    'email' => '@pa102s.gov.uk',
                    'roleName' => 'ROLE_PA_TEAM_MEMBER',
                    'isDeputy' => false,
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
                    'deputyUid' => '700781111000',
                    'count' => 10,
                ],
                [
                    'id' => 'PA-OPG-102-5-4',
                    'caseNumber' => '82222000',
                    'reportType' => 'OPG102',
                    'orderType' => 'hw',
                    'deputyUid' => '700781111000',
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
                    'isDeputy' => true,
                    'orgName' => 'PA 103 Org',
                    'count' => 1,
                ],
                [
                    'id' => 'PA-103-Admin',
                    'email' => '@pa103s.gov.uk',
                    'roleName' => 'ROLE_PA_ADMIN',
                    'isDeputy' => false,
                    'count' => 2,
                ],
                [
                    'id' => 'PA-103-Member',
                    'email' => '@pa103s.gov.uk',
                    'roleName' => 'ROLE_PA_TEAM_MEMBER',
                    'isDeputy' => false,
                    'count' => 2,
                ],
            ],
            'clients' => [
                [
                    'id' => 'PA-OPG-103-5',
                    'caseNumber' => '83333000',
                    'reportType' => 'OPG103',
                    'orderType' => 'pfa',
                    'deputyUid' => '700783333000',
                    'count' => 10,
                ],
                [
                    'id' => 'PA-OPG-103-5-4',
                    'caseNumber' => '84444000',
                    'reportType' => 'OPG103',
                    'orderType' => 'hw',
                    'deputyUid' => '700783333000',
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
                    'isDeputy' => true,
                    'orgName' => 'PA 104 Org',
                    'count' => 1,
                ],
                [
                    'id' => 'PA-104-Admin',
                    'email' => '@pa104s.gov.uk',
                    'roleName' => 'ROLE_PA_ADMIN',
                    'isDeputy' => false,
                    'count' => 2,
                ],
                [
                    'id' => 'PA-104-Member',
                    'email' => '@pa104s.gov.uk',
                    'roleName' => 'ROLE_PA_TEAM_MEMBER',
                    'isDeputy' => false,
                    'count' => 2,
                ],
            ],
            'clients' => [
                [
                    'id' => 'PA-OPG-104-5',
                    'caseNumber' => '85555000',
                    'reportType' => 'OPG104',
                    'orderType' => 'hw',
                    'deputyUid' => '700785555000',
                    'count' => 10,
                ],
            ],
        ],
    ];

    public function __construct(
        public readonly KernelInterface $kernel,
        private readonly OrganisationRepository $orgRepository,
        private readonly OrganisationFactory $orgFactory,
        private readonly DeputyRepository $deputyRepository
    ) {
        parent::__construct($kernel);
    }

    public function doLoad(ObjectManager $manager): void
    {
        // Loop through data sets.
        // Creates users, clients, organisation and deputies
        foreach ($this->fixtureData as $data) {
            $this->createFixture($data, $manager);
        }

        $manager->flush();
    }

    private function createFixture(array $data, ObjectManager $manager): void
    {
        $deputyData = null;
        $organisation = null;
        // Create number of users for each user type
        /** @var Mixed[] $userData */
        foreach ($data['users'] as $userData) {
            for ($i = 1; $i <= $userData['count']; ++$i) {
                $fullEmail = $userData['id'] . '-' . $i . $userData['email'];

                // Set the $deputyData when we are processing the deputy
                if ($deputyData === null) {
                    $deputyData = $userData['isDeputy'] ? $userData : null;
                }

                // Create user
                $user = $this->createUser($userData, $i);

                $manager->persist($user);

                $organisation = $this->orgRepository->findByEmailIdentifier($fullEmail);
                // Create organisation if it doesn't exist
                if ($organisation === null) {
                    $organisation = $this->orgFactory->createFromFullEmail($userData['orgName'] ?? 'Org Name', $fullEmail, true);

                    $manager->persist($organisation);
                    $manager->flush();
                }

                // Add user to organisation
                $organisation->addUser($user);
            }
        }

        // Create number of clients for each client type
        /** @var Mixed[] $clientData */
        foreach ($data['clients'] as $clientData) {
            for ($i = 1; $i <= $clientData['count']; ++$i) {
                // Create client
                $client = $this->createClient($clientData, $i);

                $deputy = $this->deputyRepository->findOneBy(['deputyUid' => $clientData['deputyUid']]);
                if ($deputy === null) {
                    // Create deputy if they don't exist
                    $deputy = $this->createDeputy($deputyData, $clientData, $organisation);

                    $manager->persist($deputy);
                    $manager->flush();
                }

                // Set the deputy on the client
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

    private function createUser(array $userData, int $iteration): User
    {
        return new User()
            ->setFirstname($userData['id'] . '-' . $iteration)
            ->setLastname('User')
            ->setEmail($userData['id'] . '-' . $iteration . $userData['email'])
            ->setActive(true)
            ->setRegistrationDate(new \DateTime())
            ->setPhoneMain('07911111111111')
            ->setAddress1('ABC Road')
            ->setAddressPostcode('AB1 2CD')
            ->setAddressCountry('GB')
            ->setRoleName($userData['roleName'])
            ->setAgreeTermsUse(true);
    }

    private function createClient(array $clientData, int $iteration): Client
    {
        $offset = strlen((string) abs($iteration));

        return new Client()
            ->setCaseNumber(substr_replace($clientData['caseNumber'], (string) $iteration, -$offset))
            ->setFirstname($clientData['id'])
            ->setLastname('Client ' . $iteration)
            ->setEmail(strtolower($clientData['id']) . '-client-' . $iteration . '@example.com')
            ->setPhone('07811111111111')
            ->setAddress('ABC Road')
            ->setPostcode('AB1 2CD')
            ->setCountry('GB')
            ->setCourtDate(\DateTime::createFromFormat('d/m/Y', '01/11/2017'));
    }

    private function createReport(array $clientData, Client $client, ObjectManager $manager): void
    {
        $realm = PreRegistration::REALM_PA;
        $type = PreRegistration::getReportTypeByOrderType($clientData['reportType'], $clientData['orderType'], $realm);

        /** @var \DateTime $startDate */
        $startDate = $client->getExpectedReportStartDate();
        $startDate->setDate(2016, intval($startDate->format('m')), intval($startDate->format('d')));
        /** @var \DateTime $endDate */
        $endDate = $client->getExpectedReportEndDate();
        $endDate->setDate(2017, intval($endDate->format('m')), intval($endDate->format('d')));

        $report = new Report($client, $type, $startDate, $endDate);

        $manager->persist($report);
    }

    private function createDeputy(mixed $deputyData, mixed $clientData, ?Organisation $organisation): Deputy
    {
        return new Deputy($clientData['deputyUid'], DeputyType::PA, $deputyData['id'], 'Deputy')
            ->setLastname('Deputy')
            ->setEmail1($deputyData['id'] . $deputyData['email'])
            ->setAddress1('ABC Road')
            ->setAddressPostcode('AB1 2CD')
            ->setAddressCountry('GB')
            ->setOrganisation($organisation);
    }

    /** @return String[] */
    protected function getEnvironments(): array
    {
        return ['dev', 'local'];
    }
}
