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

class ProfTestUserFixtures extends AbstractDataFixture
{
    /**
     * @var NamedDeputyRepository
     */
    private $namedDeputyRepository;

    /**
     * @var ReportUtils
     */
    private $reportUtils;

    /**
     * @var OrganisationRepository
     */
    private $orgRepository;

    /**
     * @var OrganisationFactory
     */
    private $orgFactory;

    /**
     * @var OrgService
     */
    private $orgService;

    private $userData = [
        // CSV replacement fixtures for behat
        [
            'id' => '',
            'Dep Forename' => 'DEP1',
            'Dep Surname' => 'SURNAME1',
            'Email' => 'behat-prof1@publicguardian.gov.uk',
            'active' => false,
            'Deputy No' => '9000004',
            'Dep Type' => 23,
            'roleName' => 'ROLE_PROF_NAMED',
            'Dep Adrs1' => 'Prof OPG',
            'Dep Adrs2' => 'ADD2',
            'Dep Adrs3' => 'ADD3',
            'Dep Postcode' => 'SW1',
            'Dep Adrs4' => 'ADD4',
            'Dep Adrs5' => 'ADD5',
            'Phone Main' => '10000000001',
            'Agree Terms Use' => false,
            'clients' => [
                [
                    'firstname' => 'CLY1',
                    'lastname' => 'HENT1',
                    'caseNumber' => '31000010',
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
                    'firstname' => 'CLY2',
                    'lastname' => 'HENT2',
                    'caseNumber' => '3138393T',
                    'lastReportDate' => '19/03/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly2@hent.com',
                    'dob' => '07/07/1967',
                    'reportType' => 'OPG102',
                    'reportVariation' => 'A2',
                ],
                [
                    'firstname' => 'CLY3',
                    'lastname' => 'HENT3',
                    'caseNumber' => '31498120',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly3@hent.com',
                    'dob' => '07/07/1967',
                    'reportType' => 'OPG102',
                    'reportVariation' => 'A2',
                ],
                [
                    'firstname' => 'CLY4',
                    'lastname' => 'HENT4',
                    'caseNumber' => '31000011',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly4@hent.com',
                    'dob' => '04/04/1967',
                    'reportType' => 'OPG103',
                    'reportVariation' => 'A3',
                ],
                [
                    'firstname' => 'CLY5    ',
                    'lastname' => 'HENT5',
                    'caseNumber' => '31000012',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly5@hent.com',
                    'dob' => '05/05/1967',
                    'reportType' => 'OPG103',
                    'reportVariation' => 'A3',
                ],
                [
                    'firstname' => 'CLY6    ',
                    'lastname' => 'HENT6',
                    'caseNumber' => '31000013',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly5@hent.com',
                    'dob' => '05/05/1967',
                    'reportType' => 'OPG103',
                    'reportVariation' => 'A3',
                ],
                [
                    'firstname' => 'CLY7',
                    'lastname' => 'HENT7',
                    'caseNumber' => '31000014',
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
                [
                    'firstname' => 'CLY8',
                    'lastname' => 'HENT8',
                    'caseNumber' => '31000015',
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
                [
                    'firstname' => 'CLY9',
                    'lastname' => 'HENT9',
                    'caseNumber' => '31000016',
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
                [
                    'firstname' => 'CLY10',
                    'lastname' => 'HENT10',
                    'caseNumber' => '31000017',
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
                [
                    'firstname' => 'CLY11',
                    'lastname' => 'HENT11',
                    'caseNumber' => '31000018',
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
                [
                    'firstname' => 'CLY12',
                    'lastname' => 'HENT12',
                    'caseNumber' => '31000019',
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
                [
                    'firstname' => 'CLY13',
                    'lastname' => 'HENT13',
                    'caseNumber' => '31000020',
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
                [
                    'firstname' => 'CLY14',
                    'lastname' => 'HENT14',
                    'caseNumber' => '31000021',
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
                [
                    'firstname' => 'CLY15',
                    'lastname' => 'HENT15',
                    'caseNumber' => '31000022',
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
                [
                    'firstname' => 'CLY16',
                    'lastname' => 'HENT16',
                    'caseNumber' => '31000023',
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
            'additionalClients' => 2,
        ],
        [
            'id' => '',
            'Dep Forename' => 'DEP1',
            'Dep Surname' => 'SURNAME1',
            'Email' => 'behat-prof2@publicguardian.gov.uk',
            'active' => false,
            'Deputy No' => '9000005',
            'Dep Type' => 23,
            'roleName' => 'ROLE_PROF_NAMED',
            'Dep Adrs1' => 'Prof OPG',
            'Dep Adrs2' => 'ADD2',
            'Dep Adrs3' => 'ADD3',
            'Dep Postcode' => 'SW1',
            'Dep Adrs4' => 'ADD4',
            'Dep Adrs5' => 'ADD5',
            'Phone Main' => '10000000002',
            'Agree Terms Use' => false,
            'clients' => [
                [
                    'firstname' => 'CLY201',
                    'lastname' => 'HENT201',
                    'caseNumber' => '32000001',
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
                [
                    'firstname' => 'CLY202',
                    'lastname' => 'HENT202',
                    'caseNumber' => '32000002',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly202@hent.com',
                    'dob' => '18/11/1973',
                    'reportType' => 'OPG102',
                    'reportVariation' => 'A2',
                ],
                [
                    'firstname' => 'CLY301',
                    'lastname' => 'HENT301',
                    'caseNumber' => '32000003',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly203@hent.com',
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
            'Email' => 'behat-prof3@publicguardian.gov.uk',
            'active' => false,
            'Deputy No' => '9000006',
            'Dep Type' => 23,
            'roleName' => 'ROLE_PROF_NAMED',
            'Dep Adrs1' => 'Prof OPG',
            'Dep Adrs2' => 'ADD2',
            'Dep Adrs3' => 'ADD3',
            'Dep Postcode' => 'SW1',
            'Dep Adrs4' => 'ADD4',
            'Dep Adrs5' => 'ADD5',
            'Phone Main' => '10000000003',
            'Agree Terms Use' => false,
            'clients' => [
                [
                    'firstname' => 'CLY301',
                    'lastname' => 'HENT301',
                    'caseNumber' => '33000001',
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
                [
                    'firstname' => 'CLY302',
                    'lastname' => 'HENT302',
                    'caseNumber' => '33000002',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly301@hent.com',
                    'dob' => '22/11/1977',
                    'reportType' => 'OPG102',
                    'reportVariation' => 'A2',
                ],
                [
                    'firstname' => 'CLY303',
                    'lastname' => 'HENT303',
                    'caseNumber' => '33000003',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly301@hent.com',
                    'dob' => '23/11/1977',
                    'reportType' => 'OPG103',
                    'reportVariation' => 'A3',
                ],
            ],
        ],
        [
            'id' => '',
            'Dep Forename' => 'DEP1',
            'Dep Surname' => 'SURNAME1',
            'Email' => 'behat-prof4@publicguardian.gov.uk',
            'active' => false,
            'Deputy No' => '9000007',
            'Dep Type' => 23,
            'roleName' => 'ROLE_PROF_NAMED',
            'Dep Adrs1' => 'Prof OPG',
            'Dep Adrs2' => 'ADD2',
            'Dep Adrs3' => 'ADD3',
            'Dep Postcode' => 'SW1',
            'Dep Adrs4' => 'ADD4',
            'Dep Adrs5' => 'ADD5',
            'Phone Main' => '10000000004',
            'Agree Terms Use' => false,
            'clients' => [
                [
                    'firstname' => 'CLY401',
                    'lastname' => 'HENT401',
                    'caseNumber' => '34000025',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly401@hent.com',
                    'dob' => '02/02/1967',
                    'reportType' => 'OPG103',
                    'reportVariation' => 'hw',
                ],
            ],
        ],
        [
            'id' => '',
            'Dep Forename' => 'ED',
            'Dep Surname' => 'SURNAME1',
            'Email' => 'existing-deputy1@abc-solicitors.uk',
            'active' => true,
            'Deputy No' => '9000008',
            'Dep Type' => 23,
            'roleName' => 'ROLE_PROF_NAMED',
            'Dep Adrs1' => 'ABC Solicitors',
            'Dep Adrs2' => 'ADD2',
            'Dep Adrs3' => 'ADD3',
            'Dep Postcode' => 'SW1',
            'Dep Adrs4' => 'ADD4',
            'Dep Adrs5' => 'ADD5',
            'Phone Main' => '012345123123',
            'organisation' => 'abc-solicitors.uk',
            'clients' => [
                [
                    'firstname' => 'EXISTING_CLY',
                    'lastname' => 'HENT1',
                    'caseNumber' => '50000050',
                    'lastReportDate' => '19/03/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'existing_cly1@hent.com',
                    'dob' => '01/01/1967',
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
            ->setAddress1(isset($data['Dep Adrs1']) ? $data['Dep Adrs1'] : 'Victoria Road')
            ->setAddress2(isset($data['Dep Adrs2']) ? $data['Dep Adrs2'] : null)
            ->setAddress3(isset($data['Dep Adrs3']) ? $data['Dep Adrs3'] : null)
            ->setAddressPostcode(isset($data['Dep Postcode']) ? $data['Dep Postcode'] : 'SW1')
            ->setAddressCountry('GB')
            ->setDeputyNo(isset($data['Deputy No']) ? $data['Deputy No'] : null)
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
                $client = $this->createClient($clientData, $data, $user, $manager, $organisation);
                $user->addClient($client);
            }
            if (isset($data['additionalClients'])) {
                // add dummy clients for pagination tests
                for ($i = 1; $i <= $data['additionalClients']; ++$i) {
                    $client = $this->createClient($this->generateTestClientData($i), $data, $user, $manager, $organisation);
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
            'caseNumber' => '80000'.$iterator,
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

    private function createClient($clientData, $userData, $user, $manager, $organisation)
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
            ->setCounty($clientData['address3'])
            ->setPostcode($clientData['addressPostcode'])
            ->setCountry('GB')
            ->setPhone($clientData['phone'])
            ->setEmail($clientData['email'])
            ->setDateOfBirth($dob);

        if (null === ($namedDeputy = $this->orgService->identifyNamedDeputy($userData))) {
            $namedDeputy = $this->orgService->createNamedDeputy($userData);
        }

        $client->setNamedDeputy($namedDeputy);

        $organisation->addClient($client);
        $client->setOrganisation($organisation);

        $manager->persist($client);

        if (isset($clientData['ndrEnabled']) && $clientData['ndrEnabled']) {
            $ndr = new Ndr($client);
            $manager->persist($ndr);
        } else {
            $type = PreRegistration::getReportTypeByOrderType($clientData['reportType'], $clientData['reportVariation'], PreRegistration::REALM_PROF);
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
