<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\CourtOrder;
use App\Entity\Deputy;
use App\Entity\Ndr\Ndr;
use App\Entity\PreRegistration;
use App\Entity\Report\Report;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;

class LayUserFixtures extends AbstractDataFixture
{
    private $userData = [
        [
            'id' => 'Lay-OPG102',
            'courtOrderUid' => '700000001100',
            'caseNumber' => '61111000',
            'deputyUid' => '700761111000',
            'reportType' => 'OPG102',
            'orderType' => 'pfa',
            'coDeputy' => false,
            'ndr' => false,
            'multi-client' => false,
            'duplicate-client' => false,
            'count' => 15,
        ],
        [
            'id' => 'Lay-OPG103',
            'courtOrderUid' => '700000002200',
            'caseNumber' => '62222000',
            'deputyUid' => '700762222000',
            'reportType' => 'OPG103',
            'orderType' => 'pfa',
            'coDeputy' => false,
            'ndr' => false,
            'multi-client' => false,
            'duplicate-client' => false,
            'count' => 10,
        ],
        [
            'id' => 'Lay-OPG104',
            'courtOrderUid' => '700000003300',
            'caseNumber' => '63333000',
            'deputyUid' => '700763333000',
            'reportType' => 'OPG104',
            'orderType' => 'hw',
            'coDeputy' => false,
            'ndr' => false,
            'multi-client' => false,
            'duplicate-client' => false,
            'count' => 10,
        ],
        [
            'id' => 'Lay-OPG102-4',
            'courtOrderUid' => '700004400',
            'caseNumber' => '64444000',
            'deputyUid' => '700764444000',
            'reportType' => 'OPG102',
            'orderType' => 'hw',
            'coDeputy' => false,
            'ndr' => false,
            'multi-client' => false,
            'duplicate-client' => false,
            'count' => 15,
        ],
        [
            'id' => 'Lay-OPG103-4',
            'courtOrderUid' => '700005500',
            'caseNumber' => '65555000',
            'deputyUid' => '700765555000',
            'reportType' => 'OPG103',
            'orderType' => 'hw',
            'coDeputy' => false,
            'ndr' => false,
            'multi-client' => false,
            'duplicate-client' => false,
            'count' => 10,
        ],
        [
            'id' => 'Lay-OPG102-NDR',
            'courtOrderUid' => '700000006600',
            'caseNumber' => '66666000',
            'deputyUid' => '700766666000',
            'reportType' => 'OPG102',
            'orderType' => 'hw',
            'coDeputy' => false,
            'ndr' => true,
            'multi-client' => false,
            'duplicate-client' => false,
            'count' => 10,
        ],
        [
            'id' => 'Lay-OPG103-4-NDR',
            'courtOrderUid' => '700007700',
            'caseNumber' => '67777000',
            'deputyUid' => '700767777000',
            'reportType' => 'OPG103',
            'orderType' => 'hw',
            'coDeputy' => false,
            'ndr' => true,
            'multi-client' => false,
            'duplicate-client' => false,
            'count' => 10,
        ],
        [
            'id' => 'Lay-OPG103-Co',
            'courtOrderUid' => '700000008800',
            'caseNumber' => '68888000',
            'deputyUid' => '700768888000',
            'reportType' => 'OPG103',
            'orderType' => 'pfa',
            'coDeputy' => true,
            'ndr' => false,
            'multi-client' => false,
            'duplicate-client' => false,
            'count' => 10,
        ],
        [
            'id' => 'Lay-OPG103-4-Co',
            'courtOrderUid' => '700009900',
            'caseNumber' => '69999000',
            'deputyUid' => '700769999000',
            'reportType' => 'OPG103',
            'orderType' => 'hw',
            'coDeputy' => true,
            'ndr' => false,
            'multi-client' => false,
            'duplicate-client' => false,
            'count' => 10,
        ],
        [
            'id' => 'Lay-Multi-Client-Deputy',
            'courtOrderUid' => '700000011100',
            'caseNumber' => '50000000',
            'deputyUid' => '777700000000',
            'reportType' => 'OPG102',
            'orderType' => 'pfa',
            'coDeputy' => false,
            'ndr' => false,
            'multi-client' => true,
            'duplicate-client' => false,
            'count' => 2,
        ],
        [
            'id' => 'Lay-Duplicate-Client',
            'caseNumber' => '40000000',
            'courtOrderUid' => null,
            'deputyUid' => '666600000000',
            'reportType' => 'OPG102',
            'orderType' => 'pfa',
            'coDeputy' => false,
            'ndr' => false,
            'multi-client' => false,
            'duplicate-client' => true,
            'count' => 2,
        ],
    ];

    private Deputy $deputy;

    private array $deputyUids = [];

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

        $deputyUid = substr_replace($data['deputyUid'], $iteration, -$offset);

        // Create user
        $user = (new User())
            ->setFirstname($data['id'])
            ->setLastname('User '.$iteration)
            ->setDeputyNo($deputyUid)
            ->setDeputyUid($deputyUid)
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
            ->setAgreeTermsUse(true)
            ->setIsPrimary(true);

        $manager->persist($user);

        if ($data['multi-client'] || $data['duplicate-client']) {
            $duplicateUser = clone $user;
            $duplicateUser->setLastname('User '.$iteration.' Dupe');
            $duplicateUser->setEmail(strtolower($data['id']).'-user-'.$iteration.'-dupe@publicguardian.gov.uk');
            $duplicateUser->setIsPrimary(false);

            $manager->persist($duplicateUser);
        }

        if (!in_array($deputyUid, $this->deputyUids)) {
            $this->deputyUids[] = $deputyUid;
            $this->deputy = (new Deputy())
                ->setFirstname($data['id'])
                ->setLastname('User '.$iteration)
                ->setDeputyUid($deputyUid)
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
            'ClientFirstname' => 'Client 1',
            'ClientSurname' => 'Clientsurname',
            'ClientAddress1' => 'Client Road',
            'ClientAddress2' => null,
            'ClientAddress3' => null,
            'ClientAddress4' => null,
            'ClientAddress5' => null,
            'ClientPostcode' => 'CL1 3NT',
            'DeputyUid' => $deputyUid,
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

        if ($data['multi-client']) {
            $preRegistration2 = clone $preRegistration;
            $preRegistration2->setCaseNumber(substr_replace($data['caseNumber'], $iteration, $offset, $offset));
            $preRegistration2->setClientFirstname('Client 2');
            $manager->persist($preRegistration2);
        }

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

        $client2 = clone $client;

        if ($data['multi-client']) {
            $client2->setCaseNumber(substr_replace($data['caseNumber'], $iteration, $offset, $offset));
            $client2->setLastname('Client '.$iteration.'-'.$iteration);
            $client2->setFirstname('Client '.$iteration.'-'.$iteration);
            $client2->setEmail(strtolower($data['id']).'-client-'.$iteration.'-'.$iteration.'@example.com');

            $manager->persist($client2);
            $manager->flush();
            $client2->removeUser($user);
            $duplicateUser->addClient($client2);
            $manager->persist($duplicateUser);
        } elseif ($data['duplicate-client']) {
            $client2->setLastname('Client '.$iteration.'-Discharged');
            $client2->setEmail(strtolower($data['id']).'-client-'.$iteration.'-Discharged@example.com');
            $client2->setDeletedAt(new \DateTime('now'));
            $manager->persist($client2);
            $manager->flush();
            $client2->removeUser($user);
            $duplicateUser->addClient($client2);
            $manager->persist($duplicateUser);
        }

        $ndr = null;
        $report = null;
        $multiClientSecondReport = null;

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

            if ($data['multi-client']) {
                $multiClientSecondReport = new Report($client2, $type, $startDate, $endDate);
                $manager->persist($multiClientSecondReport);
            }
        }

        // If codeputy was enabled, add a secondary account
        $user2 = null;

        if ($data['coDeputy']) {
            $user2 = clone $user;
            $newDeputyUid = substr_replace($user2->getDeputyNo(), $iteration, $offset, $offset);

            $user2->setDeputyNo($newDeputyUid);
            $user2->setDeputyUid($newDeputyUid);
            $user2->setLastname($user->getLastname().'-codeputy');
            $user2->setEmail(substr_replace($user->getEmail(), '-codeputy@publicguardian.gov.uk', -22));
            $user2->addClient($client);

            $manager->persist($user2);
        }

        // create court order and populate linked tables for non-hybrid reports (excluding duplicate clients)
        $courtOrder = null;
        if (!str_contains($data['id'], '-4') || 'Lay-Duplicate-Client' != $data['id']) {
            $courtOrder = $this->populateCourtOrderTable($data, $manager, $iteration, $offset, $client, $report, $ndr);
        }

        // handle hybrid, multi client and co-deputies
        if (str_contains($data['id'], '-4') || $data['multi-client'] || $data['coDeputy']) {
            $this->handleHybridCoDeputyAndMultiClients($data, $manager, $iteration, $offset, $courtOrder, $user2, $client, $client2, $report, $multiClientSecondReport, $ndr);
        }
    }

    private function populateCourtOrderTable(
        array $data,
        ObjectManager $manager,
        int $iteration,
        int $offset,
        Client $client,
        ?Report $report,
        ?Ndr $ndr
    ) {
        $courtOrder = new CourtOrder();
        $courtOrderUid = substr_replace($data['courtOrderUid'], (string) $iteration, -$offset);

        $courtOrder->setCourtOrderUid($courtOrderUid);
        $courtOrder->setOrderType($data['orderType']);
        $courtOrder->setStatus('ACTIVE');
        $courtOrder->setOrderMadeDate(new \DateTime('2020-06-14'));
        $courtOrder->setClient($client);
        $courtOrder->setCreatedAt(new \DateTime());
        $courtOrder->setUpdatedAt(new \DateTime());

        // Associate deputy with court order
        $this->deputy->associateWithCourtOrder($courtOrder);

        // Associate court order with reports if it's not an NDR
        if (!str_ends_with($data['id'], '-NDR') && !is_null($report)) {
            $courtOrder->addReport($report);

            $manager->persist($courtOrder);
        } else if (!is_null($ndr)) {
            $courtOrder->setNdr($ndr);

            $manager->persist($courtOrder);
        }

        $manager->persist($courtOrder);
        $manager->persist($this->deputy);

        return $courtOrder;
    }

    private function handleHybridCoDeputyAndMultiClients(
        array $data,
        ObjectManager $manager,
        int $iteration,
        int $offset,
        ?CourtOrder $courtOrder,
        ?User $user2,
        Client $client,
        Client $client2,
        ?Report $report,
        ?Report $multiClientSecondReport,
        ?Ndr $ndr)
    {
        if (str_ends_with($data['id'], '-4') || str_ends_with($data['id'], '-4-NDR') || str_ends_with($data['id'], '-4-Co')) {
            // Populate court order table and link tables
            $courtOrderPfa = new CourtOrder();
            $courtOrderHW = new CourtOrder();

            $courtOrderUidPfa = substr_replace($data['courtOrderUid'], $iteration. 103, -$offset);
            $courtOrderUidHW = substr_replace($data['courtOrderUid'], $iteration. 102, -$offset);

            $courtOrderPfa->setCourtOrderUid($courtOrderUidPfa);
            $courtOrderPfa->setOrderType($data['orderType']);
            $courtOrderPfa->setStatus('ACTIVE');
            $courtOrderPfa->setOrderMadeDate(new \DateTime('2020-06-14'));
            $courtOrderPfa->setClient($client);
            $courtOrderPfa->setCreatedAt(new \DateTime());
            $courtOrderPfa->setUpdatedAt(new \DateTime());

            $courtOrderHW->setCourtOrderUid($courtOrderUidHW);
            $courtOrderHW->setOrderType($data['orderType']);
            $courtOrderHW->setStatus('ACTIVE');
            $courtOrderHW->setOrderMadeDate(new \DateTime('2020-06-14'));
            $courtOrderHW->setClient($client);
            $courtOrderHW->setCreatedAt(new \DateTime());
            $courtOrderHW->setUpdatedAt(new \DateTime());

            // Associate deputy with court orders
            $this->deputy->associateWithCourtOrder($courtOrderPfa);
            $this->deputy->associateWithCourtOrder($courtOrderHW);

            // Associate court order with reports, excluding NDRs
            if (!$data['ndr'] && !is_null($report)) {
                $courtOrderPfa->addReport($report);
                $courtOrderHW->addReport($report);
            } else if (!empty($ndr)) {
                $courtOrderPfa->setNdr($ndr);
            }

            $manager->persist($this->deputy);
            $manager->persist($courtOrderPfa);
            $manager->persist($courtOrderHW);

            // create hybrid co-deputy and associate with court order
            if (str_ends_with($data['id'], '-4-Co') && !is_null($user2)) {
                $coDeputy = clone $this->deputy;

                $coDeputy->setDeputyUid((string) $user2->getDeputyUid());
                $coDeputy->setEmail1($user2->getEmail());
                $coDeputy->setLastname($user2->getLastname());
                $coDeputy->setUser($user2);

                $coDeputy->associateWithCourtOrder($courtOrderPfa);
                $coDeputy->associateWithCourtOrder($courtOrderHW);

                $manager->persist($coDeputy);
            }

        // create non-hybrid co-deputy account and associate with court order
        } elseif ($data['coDeputy'] && 'Lay-OPG103-Co' == $data['id'] && !is_null($user2) && !is_null($courtOrder)) {
            $coDeputy = clone $this->deputy;

            $coDeputy->setDeputyUid((string) $user2->getDeputyUid());
            $coDeputy->setEmail1($user2->getEmail());
            $coDeputy->setLastname($user2->getLastname());
            $coDeputy->setUser($user2);

            // Associate deputy with court order
            $coDeputy->associateWithCourtOrder($courtOrder);

            $manager->persist($coDeputy);
        } elseif ($data['multi-client'] && !is_null($multiClientSecondReport)) {
            // add court order for additional client
            $additionalCourtOrder = new CourtOrder();
            $courtOrderUid = substr_replace($data['courtOrderUid'], $iteration. 2, -2);

            $additionalCourtOrder->setCourtOrderUid($courtOrderUid);
            $additionalCourtOrder->setOrderType($data['orderType']);
            $additionalCourtOrder->setStatus('ACTIVE');
            $additionalCourtOrder->setOrderMadeDate(new \DateTime('2020-06-14'));
            $additionalCourtOrder->setClient($client2);
            $additionalCourtOrder->setCreatedAt(new \DateTime());
            $additionalCourtOrder->setUpdatedAt(new \DateTime());

            // Associate deputy with court order
            $this->deputy->associateWithCourtOrder($additionalCourtOrder);

            // Associate court order with report
            $additionalCourtOrder->addReport($multiClientSecondReport);

            $manager->persist($additionalCourtOrder);
            $manager->persist($this->deputy);
        }
    }

    protected function getEnvironments()
    {
        return ['dev', 'local'];
    }
}
