<?php
namespace AppBundle\DataFixtures;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Client;
use AppBundle\Entity\NamedDeputy;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Repository\NamedDeputyRepository;
use AppBundle\Entity\Team;
use AppBundle\Entity\User;
use AppBundle\Service\OrgService;
use AppBundle\Service\ReportUtils;
use Doctrine\Common\Persistence\ObjectManager;

class PATestUserFixtures extends AbstractDataFixture
{
    /**
     * @var NamedDeputyRepository
     */
    private $namedDeputyRepository;

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
            'Email' => 'behat-pa1@publicguardian.gov.uk',
            'active' => false,
            'Deputy No' => '1000001',
            'Dep Type' => 23,
            'roleName' => 'ROLE_PA_NAMED',
            'Dep Adrs1' => 'ADD1',
            'Dep Adrs2' => 'ADD2',
            'Dep Adrs3' => 'ADD3',
            'Dep Postcode' => 'SW1',
            'Dep Adrs4' => 'ADD4',
            'Dep Adrs5' => 'ADD5',
            'phoneMain' => '10000000001',
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
                    'reportVariation' => 'A2'
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
                    'reportVariation' => 'A2'
                ],
            ],
            'additionalClients' => 16
        ],
        [
            'id' => '',
            'Dep Forename' => 'DEP1',
            'Dep Surname' => 'SURNAME1',
            'Email' => 'behat-pa2@publicguardian.gov.uk',
            'active' => false,
            'Deputy No' => '9000002',
            'Dep Type' => 23,
            'roleName' => 'ROLE_PA_NAMED',
            'Dep Adrs1' => 'ADD1',
            'Dep Adrs2' => 'ADD2',
            'Dep Adrs3' => 'ADD3',
            'Dep Postcode' => 'SW1',
            'Dep Adrs4' => 'ADD4',
            'Dep Adrs5' => 'ADD5',
            'phoneMain' => '10000000001',
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
                    'reportVariation' => 'A3'
                ],

            ]
        ],
        [
            'id' => '',
            'Dep Forename' => 'DEP1',
            'Dep Surname' => 'SURNAME1',
            'Email' => 'behat-pa3@publicguardian.gov.uk',
            'active' => false,
            'Deputy No' => '9000003',
            'Dep Type' => 23,
            'roleName' => 'ROLE_PA_NAMED',
            'Dep Adrs1' => 'ADD1',
            'Dep Adrs2' => 'ADD2',
            'Dep Adrs3' => 'ADD3',
            'Dep Postcode' => 'SW1',
            'Dep Adrs4' => 'ADD4',
            'Dep Adrs5' => 'ADD5',
            'phoneMain' => '10000000001',
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
                    'reportVariation' => 'A2'
                ],

            ]
        ]

    ];

    public function doLoad(ObjectManager $manager)
    {
        $this->orgService = $this->container->get('org_service');

        $this->namedDeputyRepository = $manager->getRepository(NamedDeputy::class);

        // Add users from array
        foreach ($this->userData as $data) {
            $this->addUser($data, $manager);
        }

        $manager->flush();
    }

    private function addUser($data, $manager)
    {

        $team = new Team($data['Email'] . ' Team');
        $manager->persist($team);

        // Create user
        $user = (new User())
            ->setFirstname(isset($data['Dep Forename']) ? $data['Dep Forename'] : 'test')
            ->setLastname(isset($data['Dep Surname']) ? $data['Dep Surname'] : $data['id'])
            ->setEmail(isset($data['Email']) ? $data['Email'] : $data['id'] . '@example.org')
            ->setActive(isset($data['active']) ? $data['active'] : true)
            ->setRegistrationDate(new \DateTime())
            ->setNdrEnabled(false)
            ->setPhoneMain(isset($data['phoneMain']) ? $data['phoneMain'] : null)
            ->setAddress1(isset($data['address1']) ? $data['address1'] : 'Victoria Road')
            ->setAddress2(isset($data['address2']) ? $data['address2'] : null)
            ->setAddress3(isset($data['address3']) ? $data['address3'] : null)
            ->setAddressPostcode(isset($data['addressPostcode']) ? $data['addressPostcode'] : 'SW1')
            ->setAddressCountry('GB')
            ->setDeputyNo(isset($data['Deputy No']) ? $data['Deputy No'] : null)
            ->setRoleName($data['roleName']);

        $user->addTeam($team);
        $manager->persist($user);

        if (isset($data['clients'])) {
            foreach ($data['clients'] as $clientData) {

                // Create client
                $client = $this->createClient($clientData, $data, $user, $manager);
                $user->addClient($client);
            }
            if (isset($data['additionalClients'])) {
                // add dummy clients for pagination tests
                for($i=1; $i<=$data['additionalClients']; $i++) {
                    $client = $this->createClient($this->generateTestClientData($i), $data, $user, $manager);
                    $user->addClient($client);
                }
            }

        }
    }

    private function generateTestClientData($iterator)
    {
        return [
            'lastReportDate' => '01/01/2018',
            'dob' => '04/05/1977',
            'caseNumber' => '90000' . $iterator,
            'firstname' => 'TEST CLY' . $iterator,
            'lastname' => 'HENT' . $iterator,
            'address1' => 'Address1_' . $iterator,
            'address2' => 'Address2_' . $iterator,
            'address3' => 'Address3_' . $iterator,
            'addressPostcode' => 'PC_' . $iterator,
            'phone' => '01234123123',
            'email' => 'testCly' . $iterator . '@hent.com',
            'reportType' => 'OPG102',
            'reportVariation' => 'A2'
        ];
    }

    private function createClient($clientData, $userData, $user, $manager)
    {
        $client = new Client();
        $courtDate = \DateTime::createFromFormat('d/m/Y', $clientData['lastReportDate']);
        $dob = \DateTime::createFromFormat('d/m/Y', $clientData['dob']);

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

        $manager->persist($client);

        if (isset($clientData['ndrEnabled']) && $clientData['ndrEnabled']) {
            $ndr = new Ndr($client);
            $manager->persist($ndr);
        } else {
            $type = CasRec::getTypeBasedOnTypeofRepAndCorref($clientData['reportType'], $clientData['reportVariation'], $user->getRoleName());
            $endDate = \DateTime::createFromFormat('d/m/Y', $clientData['lastReportDate']);
            $startDate = ReportUtils::generateReportStartDateFromEndDate($endDate);
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
