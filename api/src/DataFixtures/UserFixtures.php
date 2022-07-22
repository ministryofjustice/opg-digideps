<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\NamedDeputy;
use App\Entity\Ndr\Ndr;
use App\Entity\PreRegistration;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Factory\OrganisationFactory;
use App\Repository\OrganisationRepository;
use DateTime;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends AbstractDataFixture
{
    private $userData = [
        [
            'id' => '102',
            'deputyType' => 'LAY',
            'reportType' => 'OPG102',
            'reportVariation' => 'L2',
        ],
        [
            'id' => '103',
            'deputyType' => 'LAY',
            'reportType' => 'OPG103',
            'reportVariation' => 'L3',
        ],
        [
            'id' => '104',
            'deputyType' => 'LAY',
            'reportType' => 'OPG104',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-4',
            'deputyType' => 'LAY',
            'reportType' => 'OPG103',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '102-4',
            'deputyType' => 'LAY',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-6',
            'deputyType' => 'PA',
            'reportType' => 'OPG103',
            'reportVariation' => 'A3',
        ],
        [
            'id' => '102-6',
            'deputyType' => 'PA',
            'reportType' => 'OPG102',
            'reportVariation' => 'A2',
        ],
        [
            'id' => '104-6',
            'deputyType' => 'PA',
            'reportType' => 'OPG104',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '102-4-6',
            'deputyType' => 'PA',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-4-6',
            'deputyType' => 'PA',
            'reportType' => 'OPG103',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-5',
            'deputyType' => 'PROF',
            'reportType' => 'OPG103',
            'reportVariation' => 'P3',
        ],
        [
            'id' => '102-5',
            'deputyType' => 'PROF',
            'reportType' => 'OPG102',
            'reportVariation' => 'P2',
        ],
        [
            'id' => '104-5',
            'deputyType' => 'PROF',
            'reportType' => 'OPG104',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '102-4-5',
            'deputyType' => 'PROF',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-4-5',
            'deputyType' => 'PROF',
            'reportType' => 'OPG103',
            'reportVariation' => 'HW',
        ],
        [
            'id' => 'ndr',
            'deputyType' => 'LAY',
            'reportType' => 'OPG102',
            'reportVariation' => 'L2',
            'ndr' => true,
        ],
        [
            'id' => 'codep',
            'deputyType' => 'LAY',
            'reportType' => 'OPG102',
            'reportVariation' => 'L2',
            'codeputyEnabled' => true,
        ],
        [
            'id' => 'example1',
            'email' => 'jo.brown@example.com',
            'deputyType' => 'PROF',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
        [
            'id' => 'example2',
            'email' => 'bobby.blue@example.com',
            'deputyType' => 'PROF',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
        [
            'id' => 'abc-ex1',
            'email' => 'john.smith@abc-solicitors.example.com',
            'deputyType' => 'PROF',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
        [
            'id' => 'abc-ex2',
            'email' => 'kieth.willis@abc-solicitors.example.com',
            'deputyType' => 'PROF',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
        [
            'id' => 'abcd-ex3',
            'email' => 'marjorie.watkins@abcd-solicitors.example.com',
            'deputyType' => 'PROF',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
    ];

    private OrganisationRepository $orgRepository;
    private OrganisationFactory $orgFactory;

    public function __construct(OrganisationRepository $orgRepository, OrganisationFactory $orgFactory)
    {
        $this->orgRepository = $orgRepository;
        $this->orgFactory = $orgFactory;
    }

    public function doLoad(ObjectManager $manager)
    {
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
            ->setFirstname(ucfirst($data['deputyType']).' Deputy '.$data['id'])
            ->setLastname('User')
            ->setEmail(isset($data['email']) ? $data['email'] : 'behat-'.strtolower($data['deputyType']).'-deputy-'.$data['id'].'@publicguardian.gov.uk')
            ->setActive(true)
            ->setRegistrationDate(new DateTime())
            ->setNdrEnabled(isset($data['ndr']))
            ->setCoDeputyClientConfirmed(isset($data['codeputyEnabled']))
            ->setPhoneMain('07911111111111')
            ->setAddress1('Victoria Road')
            ->setAddressPostcode('SW1')
            ->setAddressCountry('GB')
            ->setRoleName('LAY' === $data['deputyType'] ? 'ROLE_LAY_DEPUTY' : 'ROLE_'.$data['deputyType'].'_NAMED')
            ->setAgreeTermsUse(true);

        $manager->persist($user);

        // Create PreRegistration record for lay deputies
        if ('LAY' === $data['deputyType']) {
            $preRegistrationData = [
                'Case' => $data['id'],
                'ClientSurname' => $data['id'],
                'DeputyUid' => str_replace('-', '', $data['id']),
                'DeputySurname' => 'User',
                'DeputyAddress1' => 'Victoria Road',
                'DeputyAddress2' => null,
                'DeputyAddress3' => null,
                'DeputyAddress4' => null,
                'DeputyAddress5' => null,
                'DeputyPostcode' => 'SW1',
                'ReportType' => $data['reportType'] ?? null,
                'NDR' => $data['ndr'] ?? null,
                'MadeDate' => '2010-03-30',
                'OrderType' => 'hw',
                'CoDeputy' => $data['codeputyEnabled'] ?? null,
            ];

            $preRegistration = new PreRegistration($preRegistrationData);
            $manager->persist($preRegistration);
        }
        // Create client
        $client = new Client();
        $client
            ->setCaseNumber($data['id'])
            ->setFirstname('John')
            ->setLastname($data['id'].'-client')
            ->setPhone('022222222222222')
            ->setAddress('Victoria road')
            ->setCourtDate(DateTime::createFromFormat('d/m/Y', '01/11/2017'));

        if ('PROF' === $data['deputyType'] || 'PA' === $data['deputyType']) {
            $namedDeputy = new NamedDeputy();
            $namedDeputy
                ->setFirstname('Named')
                ->setLastname('Deputy '.$data['id'])
                ->setDeputyUid('nd-'.$data['id'])
                ->setEmail1('behat-nd-'.$data['id'].'@publicguardian.gov.uk')
                ->setPhoneMain('07911111111111')
                ->setAddress1('Victoria Road')
                ->setAddressPostcode('SW1')
                ->setAddressCountry('GB');

            $manager->persist($namedDeputy);

            $client->setNamedDeputy($namedDeputy);
        }

        $manager->persist($client);
        $user->addClient($client);

        if (!$client->getNdr()) {
            $ndr = new Ndr($client);
            $manager->persist($ndr);
        }

        // Create report for PROF/PA user 2 years ago
        if ('PROF' === $data['deputyType'] || 'PA' === $data['deputyType']) {
            $realm = 'PROF' === $data['deputyType'] ? PreRegistration::REALM_PROF : PreRegistration::REALM_PA;

            $type = PreRegistration::getReportTypeByOrderType($data['reportType'], $data['reportVariation'], $realm);
            $startDate = $client->getExpectedReportStartDate();
            $startDate->setDate('2016', intval($startDate->format('m')), intval($startDate->format('d')));
            $endDate = $client->getExpectedReportEndDate();
            $endDate->setDate('2017', intval($endDate->format('m')), intval($endDate->format('d')));

            $report = new Report($client, $type, $startDate, $endDate);

            $manager->persist($report);

            if (isset($data['email'])) {
                $organisation = $this->orgRepository->findByEmailIdentifier($data['email']);
                if (null === $organisation) {
                    $organisation = $this->orgFactory->createFromFullEmail($data['email'], $data['email']);
                    $manager->persist($organisation);
                    $manager->flush($organisation);
                }
            }
        }

        // If codeputy was enabled, add a secondary account
        if (isset($data['codeputyEnabled'])) {
            $user2 = clone $user;
            $user2->setLastname($user2->getLastname().'-2');
            $user2->setEmail('behat-'.strtolower($data['deputyType']).'-deputy-'.$data['id'].'-2@publicguardian.gov.uk');
            $user2->addClient($client);

            $manager->persist($user2);
        }
    }

    protected function getEnvironments()
    {
        return ['dev'];
    }
}
