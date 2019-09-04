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

class PATestUserFixtures extends AbstractDataFixture
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
            'email' => 'behat-pa1@publicguardian.gov.uk',
            'active' => false,
            'deputyNo' => '1000001',
            'roleName' => 'ROLE_PA_NAMED',
            'address1' => 'ADD1',
            'address2' => 'ADD2',
            'address3' => 'ADD3',
            'addressPostcode' => 'SW1',
            'address4' => 'ADD4',
            'address5' => 'ADD5',
            'clients' => [
                [
                    'firstname' => 'CLY1',
                    'lastname' => 'HENT1',
                    'caseNumber' => '1000010',
                    'lastReportDate' => '19/03/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '78912345678',
                    'email' => 'cly1@hent.com',
                    'dob' => '01/01/1967',
                    'reportType' => 'OPG102',
                    'reportVariation' => 'A2'
                ],
                [
                    'firstname' => 'CLY7',
                    'lastname' => 'HENT',
                    'caseNumber' => '1000014',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '78912345678',
                    'email' => 'cly7@hent.com',
                    'dob' => '07/07/1967',
                    'reportType' => 'OPG102',
                    'reportVariation' => 'A2'
                ],
            ],
        ],
        [
            'id' => '',
            'firstname' => 'DEP1',
            'lastname' => 'SURNAME1',
            'email' => 'behat-pa2@publicguardian.gov.uk',
            'active' => false,
            'deputyNo' => '9000002',
            'roleName' => 'ROLE_PA_NAMED',
            'address1' => 'ADD1',
            'address2' => 'ADD2',
            'address3' => 'ADD3',
            'addressPostcode' => 'SW1',
            'address4' => 'ADD4',
            'address5' => 'ADD5',
            'clients' => [
                [
                    'firstname' => 'CLY301',
                    'lastname' => 'HENT301',
                    'caseNumber' => '2000001',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '78912345678',
                    'email' => 'cly201@hent.com',
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
            'email' => 'behat-pa3@publicguardian.gov.uk',
            'active' => false,
            'deputyNo' => '9000003',
            'roleName' => 'ROLE_PA_NAMED',
            'address1' => 'ADD1',
            'address2' => 'ADD2',
            'address3' => 'ADD3',
            'addressPostcode' => 'SW1',
            'address4' => 'ADD4',
            'address5' => 'ADD5',
            'clients' => [
                [
                    'firstname' => 'CLY201',
                    'lastname' => 'HENT201',
                    'caseNumber' => '3000001',
                    'lastReportDate' => '28/05/2017',
                    'address1' => 'ADD1',
                    'address2' => 'ADD2',
                    'address3' => 'ADD3',
                    'addressPostcode' => 'B301QL',
                    'phone' => '78912345678',
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
            ->setFirstname((isset($data['firstname']) ? $data['firstname'] : 'test'))
            ->setLastname((isset($data['lastname']) ? $data['lastname'] : $data['id']))
            ->setEmail((isset($data['email']) ? $data['email'] : $data['id'] . '@example.org'))
            ->setActive((isset($data['active']) ? $data['active'] : true))
            ->setRegistrationDate(new \DateTime())
            ->setNdrEnabled(false)
            ->setPhoneMain('07911111111111')
            ->setAddress1(isset($data['address1']) ? $data['address1'] : 'Victoria Road')
            ->setAddress2(isset($data['address2']) ? $data['address2'] : NULL)
            ->setAddress3(isset($data['address3']) ? $data['address3'] : NULL)
            ->setAddressPostcode(isset($data['addressPostcode']) ? $data['addressPostcode'] : 'SW1')
            ->setAddressCountry('GB')
            ->setDeputyNo(isset($data['deputyNo']) ? $data['deputyNo'] : NULL)
            ->setRoleName($data['roleName']);

        $user->addTeam($team);
        $manager->persist($user);

        if (isset($data['clients'])) {
            foreach ($data['clients'] as $clientData) {

                // Create client
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

                $namedDeputy = $this->upsertNamedDeputy($data, $manager);

                $client->setNamedDeputy($namedDeputy);

                $manager->persist($client);
                $user->addClient($client);


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

            }
        }
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
                $data['address1'],
                $data['address2'],
                $data['address3'],
                $data['addressPostcode'],
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
