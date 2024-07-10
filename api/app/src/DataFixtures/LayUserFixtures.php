<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Deputy;
use App\Entity\Ndr\Ndr;
use App\Entity\PreRegistration;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Repository\DeputyRepository;
use Doctrine\Persistence\ObjectManager;

class LayUserFixtures extends AbstractDataFixture
{
    private $userData = [
        [
            'id' => 'Lay-OPG102',
            'caseNumber' => '61111000',
            'deputyUid' => '700761111000',
            'reportType' => 'OPG102',
            'orderType' => 'pfa',
            'coDeputy' => false,
            'ndr' => false,
            'count' => 15,
        ],
        [
            'id' => 'Lay-OPG103',
            'caseNumber' => '62222000',
            'deputyUid' => '700762222000',
            'reportType' => 'OPG103',
            'orderType' => 'pfa',
            'coDeputy' => false,
            'ndr' => false,
            'count' => 10,
        ],
        [
            'id' => 'Lay-OPG104',
            'caseNumber' => '63333000',
            'deputyUid' => '700763333000',
            'reportType' => 'OPG104',
            'orderType' => 'hw',
            'coDeputy' => false,
            'ndr' => false,
            'count' => 10,
        ],
        [
            'id' => 'Lay-OPG102-4',
            'caseNumber' => '64444000',
            'deputyUid' => '700764444000',
            'reportType' => 'OPG102',
            'orderType' => 'hw',
            'coDeputy' => false,
            'ndr' => false,
            'count' => 15,
        ],
        [
            'id' => 'Lay-OPG103-4',
            'caseNumber' => '65555000',
            'deputyUid' => '700765555000',
            'reportType' => 'OPG103',
            'orderType' => 'hw',
            'coDeputy' => false,
            'ndr' => false,
            'count' => 10,
        ],
        [
            'id' => 'Lay-OPG102-NDR',
            'caseNumber' => '66666000',
            'deputyUid' => '700766666000',
            'reportType' => 'OPG102',
            'orderType' => 'hw',
            'coDeputy' => false,
            'ndr' => true,
            'count' => 10,
        ],
        [
            'id' => 'Lay-OPG103-4-NDR',
            'caseNumber' => '67777000',
            'deputyUid' => '700767777000',
            'reportType' => 'OPG103',
            'orderType' => 'hw',
            'coDeputy' => false,
            'ndr' => true,
            'count' => 10,
        ],
        [
            'id' => 'Lay-OPG103-Co',
            'caseNumber' => '68888000',
            'deputyUid' => '700768888000',
            'reportType' => 'OPG103',
            'orderType' => 'pfa',
            'coDeputy' => true,
            'ndr' => false,
            'count' => 10,
        ],
        [
            'id' => 'Lay-OPG103-4-Co',
            'caseNumber' => '69999000',
            'deputyUid' => '700769999000',
            'reportType' => 'OPG103',
            'orderType' => 'hw',
            'coDeputy' => true,
            'ndr' => false,
            'count' => 10,
        ],
    ];

    private Deputy $deputy;

    private array $deputyUids = [];

    public function __construct(
        private DeputyRepository $deputyRepository
    ) {
    }

    public function doLoad(ObjectManager $manager)
    {
        // Add users from array
        foreach ($this->userData as $data) {
            for ($i = 1; $i <= $data['count']; ++$i) {
                $this->addUser($data, $manager, $i);
            }
        }

        $manager->flush();
    }

    private function addUser(array $data, ObjectManager $manager, int $iteration)
    {
        $offset = strlen((string) abs($iteration));

        // Create user
        $user = (new User())
            ->setFirstname($data['id'])
            ->setLastname('User '.$iteration)
            ->setDeputyNo($data['deputyUid'])
            ->setDeputyUid($data['deputyUid'])
            ->setEmail(strtolower($data['id']).'-user-'.$iteration.'@publicguardian.gov.uk')
            ->setActive(true)
            ->setRegistrationDate(new \DateTime())
            ->setNdrEnabled($data['ndr'])
            ->setCoDeputyClientConfirmed($data['coDeputy'])
            ->setPhoneMain('07911111111111')
            ->setAddress1('ABC Road')
            ->setAddressPostcode('AB1 2CD')
            ->setAddressCountry('GB')
            ->setRoleName('ROLE_LAY_DEPUTY')
            ->setAgreeTermsUse(true);

        $manager->persist($user);

        if (!in_array($data['deputyUid'], $this->deputyUids)) {
            $this->deputyUids[] = $data['deputyUid'];
            $this->deputy = (new Deputy())
                ->setFirstname($data['id'])
                ->setLastname('User '.$iteration)
                ->setDeputyUid($data['deputyUid'])
                ->setEmail1(strtolower($data['id']).'-user-'.$iteration.'@publicguardian.gov.uk')
                ->setPhoneMain('07911111111111')
                ->setAddress1('ABC Road')
                ->setAddressPostcode('AB1 2CD')
                ->setAddressCountry('GB')
                ->setUser($user);

            $manager->persist($this->deputy);
        }

        // Create PreRegistration record for lay deputies

        $preRegistrationData = [
            'Case' => substr_replace($data['caseNumber'], $iteration, -$offset),
            'ClientSurname' => 'Client '.$iteration,
            'DeputyUid' => substr_replace($data['deputyUid'], $iteration, -$offset),
            'DeputyFirstname' => $data['id'].'-User-'.$iteration,
            'DeputySurname' => 'User',
            'DeputyAddress1' => 'ABC Road',
            'DeputyAddress2' => null,
            'DeputyAddress3' => null,
            'DeputyAddress4' => null,
            'DeputyAddress5' => null,
            'DeputyPostcode' => 'AB1 2CD',
            'ReportType' => $data['reportType'],
            'NDR' => $data['ndr'],
            'MadeDate' => '2010-03-30',
            'OrderType' => $data['orderType'],
            'CoDeputy' => $data['coDeputy'],
        ];

        $preRegistration = new PreRegistration($preRegistrationData);
        $manager->persist($preRegistration);

        // Create client
        $client = new Client();
        $client
            ->setCaseNumber(substr_replace($data['caseNumber'], $iteration, -$offset))
            ->setFirstname($data['id'])
            ->setLastname('Client '.$iteration)
            ->setEmail(strtolower($data['id']).'-client-'.$iteration.'@example.com')
            ->setPhone('07811111111111')
            ->setAddress('ABC Road')
            ->setPostcode('AB1 2CD')
            ->setCountry('GB')
            ->setCourtDate(\DateTime::createFromFormat('d/m/Y', '01/11/2017'))
            ->setDeputy($this->deputy);

        $manager->persist($client);
        $user->addClient($client);

        if ($data['ndr']) {
            $ndr = new Ndr($client);
            $client->setNdr($ndr);

            $manager->persist($ndr);
        } else {
            $realm = PreRegistration::REALM_LAY;
            $type = PreRegistration::getReportTypeByOrderType($data['reportType'], $data['orderType'], $realm);

            $startDate = $client->getExpectedReportStartDate();
            $startDate->setDate('2016', intval($startDate->format('m')), intval($startDate->format('d')));

            $endDate = $client->getExpectedReportEndDate();
            $endDate->setDate('2017', intval($endDate->format('m')), intval($endDate->format('d')));

            $report = new Report($client, $type, $startDate, $endDate);

            $manager->persist($report);
        }

        // If codeputy was enabled, add a secondary account
        if ($data['coDeputy']) {
            $user2 = clone $user;
            $user2->setLastname($user->getLastname().'-codeputy');
            $user2->setEmail(substr_replace($user->getEmail(), '-codeputy@publicguardian.gov.uk', -22));
            $user2->addClient($client);

            $manager->persist($user2);
        }
    }

    protected function getEnvironments()
    {
        return ['dev', 'local'];
    }
}
