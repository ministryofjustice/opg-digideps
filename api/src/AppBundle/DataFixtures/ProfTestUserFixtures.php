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
use AppBundle\Service\ReportUtils;
use Doctrine\Common\Persistence\ObjectManager;

class ProfTestUserFixtures extends AbstractDataFixture
{
    /**
     * @var NamedDeputyRepository
     */
    private $namedDeputyRepository;

    private $userData = [
        // CSV replacement fixtures for behat
        [
            'id' => '',
            'firstname' => 'DEP1',
            'lastname' => 'SURNAME1',
            'email' => 'behat-prof1@publicguardian.gov.uk',
            'active' => false,
                'deputyNo' => '9000001',
            'roleName' => 'ROLE_PROF_NAMED',
            'address1' => 'ADD1',
            'address2' => 'ADD2',
            'address3' => 'ADD3',
            'addressPostcode' => 'SW1',
            'address4' => 'ADD4',
            'address5' => 'ADD5',
            'phoneMain' => '10000000001',
            'clients' => [
                [
                    'firstname' => 'CLY1',
                    'lastname' => 'HENT1',
                    'caseNumber' => '3100010',
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
                    'firstname' => 'CLY2',
                    'lastname' => 'HENT2',
                    'caseNumber' => '1138393T',
                    'lastReportDate' => '19/03/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly2@hent.com',
                    'dob' => '07/07/1967',
                    'reportType' => 'OPG102',
                    'reportVariation' => 'A2'
                ],
                [
                    'firstname' => 'CLY3',
                    'lastname' => 'HENT3',
                    'caseNumber' => '11498120',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly3@hent.com',
                    'dob' => '07/07/1967',
                    'reportType' => 'OPG102',
                    'reportVariation' => 'A2'
                ],
                [
                    'firstname' => 'CLY4',
                    'lastname' => 'HENT4',
                    'caseNumber' => '3100011',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly4@hent.com',
                    'dob' => '07/07/1967',
                    'reportType' => 'OPG103',
                    'reportVariation' => 'A3'
                ],
                [
                    'firstname' => 'CLY5    ',
                    'lastname' => 'HENT5',
                    'caseNumber' => '3100012',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly5@hent.com',
                    'dob' => '05/05/1967',
                    'reportType' => 'OPG103',
                    'reportVariation' => 'A3'
                ],
                [
                    'firstname' => 'CLY6    ',
                    'lastname' => 'HENT6',
                    'caseNumber' => '3100013',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly5@hent.com',
                    'dob' => '05/05/1967',
                    'reportType' => 'OPG103',
                    'reportVariation' => 'A3'
                ],
                [
                    'firstname' => 'CLY7',
                    'lastname' => 'HENT7',
                    'caseNumber' => '3100014',
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
                [
                    'firstname' => 'CLY8',
                    'lastname' => 'HENT8',
                    'caseNumber' => '3100015',
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
                [
                    'firstname' => 'CLY9',
                    'lastname' => 'HENT9',
                    'caseNumber' => '3100016',
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
                [
                    'firstname' => 'CLY10',
                    'lastname' => 'HENT10',
                    'caseNumber' => '3100017',
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
                [
                    'firstname' => 'CLY11',
                    'lastname' => 'HENT11',
                    'caseNumber' => '3100018',
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
                [
                    'firstname' => 'CLY12',
                    'lastname' => 'HENT12',
                    'caseNumber' => '3100019',
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
                [
                    'firstname' => 'CLY13',
                    'lastname' => 'HENT13',
                    'caseNumber' => '3100020',
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
                [
                    'firstname' => 'CLY14',
                    'lastname' => 'HENT14',
                    'caseNumber' => '3100021',
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
                [
                    'firstname' => 'CLY15',
                    'lastname' => 'HENT15',
                    'caseNumber' => '3100022',
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
                [
                    'firstname' => 'CLY16',
                    'lastname' => 'HENT16',
                    'caseNumber' => '3100023',
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
            'additionalClients' => 2
        ],
        [
            'id' => '',
            'firstname' => 'DEP1',
            'lastname' => 'SURNAME1',
            'email' => 'behat-prof2@publicguardian.gov.uk',
            'active' => false,
            'deputyNo' => '9000002',
            'roleName' => 'ROLE_PROF_NAMED',
            'address1' => 'ADD1',
            'address2' => 'ADD2',
            'address3' => 'ADD3',
            'addressPostcode' => 'SW1',
            'address4' => 'ADD4',
            'address5' => 'ADD5',
            'phoneMain' => '10000000001',
            'clients' => [
                [
                    'firstname' => 'CLY201',
                    'lastname' => 'HENT201',
                    'caseNumber' => '3200001',
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
                [
                    'firstname' => 'CLY202',
                    'lastname' => 'HENT202',
                    'caseNumber' => '3200002',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly202@hent.com',
                    'dob' => '18/11/1973',
                    'reportType' => 'OPG102',
                    'reportVariation' => 'A2'
                ],
                [
                    'firstname' => 'CLY301',
                    'lastname' => 'HENT301',
                    'caseNumber' => '3200003',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly203@hent.com',
                    'dob' => '02/02/1967',
                    'reportType' => 'OPG103',
                    'reportVariation' => 'A3'
                ],

            ]
        ],
        [
            'id' => '',
            'firstname' => 'DEP1',
            'lastname' => 'SURNAME1',
            'email' => 'behat-prof3@publicguardian.gov.uk',
            'active' => false,
            'deputyNo' => '9000003',
            'roleName' => 'ROLE_PROF_NAMED',
            'address1' => 'ADD1',
            'address2' => 'ADD2',
            'address3' => 'ADD3',
            'addressPostcode' => 'SW1',
            'address4' => 'ADD4',
            'address5' => 'ADD5',
            'phoneMain' => '10000000001',
            'clients' => [
                [
                    'firstname' => 'CLY301',
                    'lastname' => 'HENT301',
                    'caseNumber' => '3300001',
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
                [
                    'firstname' => 'CLY302',
                    'lastname' => 'HENT302',
                    'caseNumber' => '3300002',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly301@hent.com',
                    'dob' => '22/11/1977',
                    'reportType' => 'OPG102',
                    'reportVariation' => 'A2'
                ],
                [
                    'firstname' => 'CLY303',
                    'lastname' => 'HENT303',
                    'caseNumber' => '3300003',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly301@hent.com',
                    'dob' => '23/11/1977',
                    'reportType' => 'OPG103',
                    'reportVariation' => 'A3'
                ],
            ]
        ],
        [
            'id' => '',
            'firstname' => 'DEP1',
            'lastname' => 'SURNAME1',
            'email' => 'behat-prof4@publicguardian.gov.uk',
            'active' => false,
            'deputyNo' => '1000025  ',
            'roleName' => 'ROLE_PROF_NAMED',
            'address1' => 'ADD1',
            'address2' => 'ADD2',
            'address3' => 'ADD3',
            'addressPostcode' => 'SW1',
            'address4' => 'ADD4',
            'address5' => 'ADD5',
            'phoneMain' => '10000000001',
            'clients' => [
                [
                    'firstname' => 'CLY401',
                    'lastname' => 'HENT401',
                    'caseNumber' => '3400025',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '078912345678',
                    'email' => 'cly401@hent.com',
                    'dob' => '02/02/1967',
                    'reportType' => '',
                    'reportVariation' => 'hw'
                ],
            ]
        ]

    ];

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

        $team = new Team($data['email'] . ' Team');
        $manager->persist($team);

        // Create user
        $user = (new User())
            ->setFirstname(isset($data['firstname']) ? $data['firstname'] : 'test')
            ->setLastname(isset($data['lastname']) ? $data['lastname'] : $data['id'])
            ->setEmail(isset($data['email']) ? $data['email'] : $data['id'] . '@example.org')
            ->setActive(isset($data['active']) ? $data['active'] : true)
            ->setRegistrationDate(new \DateTime())
            ->setNdrEnabled(false)
            ->setPhoneMain(isset($data['phoneMain']) ? $data['phoneMain'] : null)
            ->setAddress1(isset($data['address1']) ? $data['address1'] : 'Victoria Road')
            ->setAddress2(isset($data['address2']) ? $data['address2'] : null)
            ->setAddress3(isset($data['address3']) ? $data['address3'] : null)
            ->setAddressPostcode(isset($data['addressPostcode']) ? $data['addressPostcode'] : 'SW1')
            ->setAddressCountry('GB')
            ->setDeputyNo(isset($data['deputyNo']) ? $data['deputyNo'] : null)
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
            'caseNumber' => '80000' . $iterator,
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

        $namedDeputy = $this->upsertNamedDeputy($userData, $manager);

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

    /**
     * @param $data
     * @return NamedDeputy|null|object
     */

    private function upsertNamedDeputy($data, $manager)
    {
        $namedDeputy = null;
        $deputyNo = User::padDeputyNumber($data['deputyNo']);

        if (isset($data['deputyNo'])) {
            $namedDeputy = $this->namedDeputyRepository->findOneBy([
                'deputyNo' => $deputyNo,
                'email1' => $data['email']
            ]);
        }
        if (!$namedDeputy instanceof NamedDeputy) {
            $namedDeputy = new NamedDeputy(
                $deputyNo,
                $data['email'],
                $data['firstname'],
                $data['lastname'],
                $data['address1'],
                $data['address2'],
                $data['address3'],
                $data['addressPostcode'],
                $data['phoneMain'],
                isset($data['phoneAlternative']) ? $data['phoneAlternative'] : null,
                $data['address4'],
                $data['address5'],
                $data
            );

            $manager->persist($namedDeputy);
        }

        return $namedDeputy;
    }

    protected function getEnvironments()
    {
        return ['dev', 'test'];
    }
}
