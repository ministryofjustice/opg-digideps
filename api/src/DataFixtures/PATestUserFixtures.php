<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\NamedDeputy;
use App\Entity\Ndr\Ndr;
use App\Entity\PreRegistration;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Factory\OrganisationFactory;
use App\Repository\NamedDeputyRepository;
use App\Repository\OrganisationRepository;
use App\Service\OrgService;
use App\Service\ReportUtils;
use DateTime;
use Doctrine\Persistence\ObjectManager;

class PATestUserFixtures extends AbstractDataFixture
{
    /**
     * @var ReportUtils
     */
    private $reportUtils;

    /**
     * @var NamedDeputyRepository
     */
    private $namedDeputyRepository;

    /**
     * @var OrgService
     */
    private $orgService;

    /**
     * @var OrganisationRepository
     */
    private $orgRepository;

    /**
     * @var OrganisationFactory
     */
    private $orgFactory;

    private $userData = [
        // CSV replacement fixtures for behat
        [
            'id' => '',
            'Dep Forename' => 'DEP1',
            'Dep Surname' => 'SURNAME1',
            'Email' => 'behat-pa1@publicguardian.gov.uk',
            'active' => false,
            'Deputy Uid' => '9000001',
            'roleName' => 'ROLE_PA_NAMED',
            'Dep Adrs1' => 'PA OPG',
            'Dep Adrs2' => 'ADD2',
            'Dep Adrs3' => 'ADD3',
            'Dep Postcode' => 'SW1',
            'Dep Adrs4' => 'ADD4',
            'Dep Adrs5' => 'ADD5',
            'Phone Main' => '+4410000000001',
            'Agree Terms Use' => false,
            'clients' => [
                [
                    'firstname' => 'CLY1',
                    'lastname' => 'HENT1',
                    'caseNumber' => '2100010',
                    'lastReportDate' => '19/03/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly1@hent.com',
                    'dob' => '01/01/1967',
                    'reportType' => 'OPG102',
                    'reportVariation' => 'A2',
                ],
                [
                    'firstname' => 'CLY7',
                    'lastname' => 'HENT',
                    'caseNumber' => '2100014',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly7@hent.com',
                    'dob' => '07/07/1967',
                    'reportType' => 'OPG102',
                    'reportVariation' => 'A2',
                ],
            ],
            'additionalClients' => 16,
        ],
        [
            'id' => '',
            'Dep Forename' => 'DEP1',
            'Dep Surname' => 'SURNAME1',
            'Email' => 'behat-pa2@publicguardian.gov.uk',
            'active' => false,
            'Deputy Uid' => '9000002',
            'roleName' => 'ROLE_PA_NAMED',
            'Dep Adrs1' => 'PA OPG',
            'Dep Adrs2' => 'ADD2',
            'Dep Adrs3' => 'ADD3',
            'Dep Postcode' => 'SW1',
            'Dep Adrs4' => 'ADD4',
            'Dep Adrs5' => 'ADD5',
            'Phone Main' => '+4410000000002',
            'Agree Terms Use' => false,
            'clients' => [
                [
                    'firstname' => 'CLY301',
                    'lastname' => 'HENT301',
                    'caseNumber' => '2200001',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly201@hent.com',
                    'dob' => '02/02/1967',
                    'reportType' => 'OPG103',
                    'reportVariation' => 'A3',
                ],
            ],
        ],
        [
            'id' => '',
            'Dep Forename' => 'DEP1',
            'Dep Surname' => 'SURNAME1',
            'Email' => 'behat-pa3@publicguardian.gov.uk',
            'active' => false,
            'Deputy Uid' => '9000003',
            'roleName' => 'ROLE_PA_NAMED',
            'Dep Adrs1' => 'PA OPG',
            'Dep Adrs2' => 'ADD2',
            'Dep Adrs3' => 'ADD3',
            'Dep Postcode' => 'SW1',
            'Dep Adrs4' => 'ADD4',
            'Dep Adrs5' => 'ADD5',
            'Phone Main' => '+4410000000003',
            'Agree Terms Use' => false,
            'clients' => [
                [
                    'firstname' => 'CLY201',
                    'lastname' => 'HENT201',
                    'caseNumber' => '2300001',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly301@hent.com',
                    'dob' => '02/02/1967',
                    'reportType' => 'OPG102',
                    'reportVariation' => 'A2',
                ],
            ],
        ],
    ];

    public function __construct(OrgService $orgService, OrganisationRepository $orgRepository, OrganisationFactory $orgFactory, ReportUtils $reportUtils)
    {
        $this->orgService = $orgService;
        $this->orgRepository = $orgRepository;
        $this->orgFactory = $orgFactory;
        $this->reportUtils = $reportUtils;
    }

    public function doLoad(ObjectManager $manager)
    {
        $this->namedDeputyRepository = $manager->getRepository(NamedDeputy::class);

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
            ->setFirstname(isset($data['Dep Forename']) ? $data['Dep Forename'] : 'test')
            ->setLastname(isset($data['Dep Surname']) ? $data['Dep Surname'] : $data['id'])
            ->setEmail(isset($data['Email']) ? $data['Email'] : $data['id'].'@example.org')
            ->setActive(isset($data['active']) ? $data['active'] : true)
            ->setRegistrationDate(new DateTime())
            ->setNdrEnabled(false)
            ->setPhoneMain(isset($data['Phone Main']) ? $data['Phone Main'] : null)
            ->setAddress1(isset($data['address1']) ? $data['address1'] : 'Victoria Road')
            ->setAddress2(isset($data['address2']) ? $data['address2'] : null)
            ->setAddress3(isset($data['address3']) ? $data['address3'] : null)
            ->setAddressPostcode(isset($data['addressPostcode']) ? $data['addressPostcode'] : 'SW1')
            ->setAddressCountry('GB')
            ->setRoleName($data['roleName'])
            ->setAgreeTermsUse(isset($data['Agree Terms Use']) ? $data['Agree Terms Use'] : true);

        $manager->persist($user);

        $organisation = $this->orgRepository->findByEmailIdentifier($data['Email']);
        if (null === $organisation) {
            $organisation = $this->orgFactory->createFromFullEmail($data['Dep Adrs1'], $data['Email']);
            $manager->persist($organisation);
            $manager->flush($organisation);
        }

        if (isset($data['clients'])) {
            foreach ($data['clients'] as $clientData) {
                // Create client
                $client = $this->createClient($clientData, $data, $user, $manager);
                $user->addClient($client);
            }
            if (isset($data['additionalClients'])) {
                // add dummy clients for pagination tests
                for ($i = 1; $i <= $data['additionalClients']; ++$i) {
                    $client = $this->createClient($this->generateTestClientData($i), $data, $user, $manager);
                    $user->addClient($client);
                    $organisation->addClient($client);
                    $client->setOrganisation($organisation);
                }
            }
        }
    }

    private function generateTestClientData($iterator)
    {
        return [
            'lastReportDate' => '01/01/2018',
            'dob' => '04/05/1977',
            'caseNumber' => '90000'.$iterator,
            'firstname' => 'TEST CLY'.$iterator,
            'lastname' => 'HENT'.$iterator,
            'address1' => 'Address1_'.$iterator,
            'address2' => 'Address2_'.$iterator,
            'address3' => 'Address3_'.$iterator,
            'addressPostcode' => 'PC_'.$iterator,
            'phone' => '01234123123',
            'email' => 'testCly'.$iterator.'@hent.com',
            'reportType' => 'OPG102',
            'reportVariation' => 'A2',
        ];
    }

    private function createClient($clientData, $userData, $user, $manager)
    {
        $client = new Client();
        $courtDate = DateTime::createFromFormat('d/m/Y', $clientData['lastReportDate']);
        $dob = DateTime::createFromFormat('d/m/Y', $clientData['dob']);

        $client
            ->setCaseNumber(User::padDeputyNumber($clientData['caseNumber']))
            ->setFirstname($clientData['firstname'])
            ->setLastname($clientData['lastname'])
            ->setCourtDate($courtDate->modify('-1year +1day'))
            ->setAddress($clientData['address1'])
            ->setAddress2($clientData['address2'])
            ->setAddress3($clientData['address3'])
            ->setPostcode($clientData['addressPostcode'])
            ->setCountry('GB')
            ->setPhone($clientData['phone'])
            ->setEmail($clientData['email'])
            ->setDateOfBirth($dob);

        if (null === ($namedDeputy = $this->orgService->identifyNamedDeputy($userData))) {
            $namedDeputy = $this->orgService->createNamedDeputy($userData);
        }

        $client->setNamedDeputy($namedDeputy);

        $manager->persist($client);

        if (isset($clientData['ndrEnabled']) && $clientData['ndrEnabled']) {
            $ndr = new Ndr($client);
            $manager->persist($ndr);
        } else {
            $type = PreRegistration::getReportTypeByOrderType($clientData['reportType'], $clientData['reportVariation'], PreRegistration::REALM_PA);
            $endDate = DateTime::createFromFormat('d/m/Y', $clientData['lastReportDate']);
            $startDate = $this->reportUtils->generateReportStartDateFromEndDate($endDate);
            $report = new Report($client, $type, $startDate, $endDate);

            $manager->persist($report);
        }

        return $client;
    }

    protected function getEnvironments()
    {
        return ['dev'];
    }
}
