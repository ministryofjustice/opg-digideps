<?php

namespace AppBundle\Service;

use AppBundle\Entity as EntityDir;
use Doctrine\ORM\EntityManager;

class PaService
{
    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->userRepository = $em->getRepository(EntityDir\User::class);
        $this->reportRepository = $em->getRepository(EntityDir\Report\Report::class);
        $this->clientRepository = $em->getRepository(EntityDir\Client::class);
        $this->log = [];
    }

    /**
     * //TODO
     * - move to methods
     * - cleanup data if needed
     *
     * Example of a single row :[
     *     'Email'        => 'dep2@provider.com',
     *     'Deputy No'    => '00000001',
     *     'Dep Postcode' => 'N1 ABC',
     *     'Dep Forename' => 'Dep1',
     *     'Dep Surname'  => 'Uty2',
     *     'Dep Type'     => 23,
     *     'Dep Adrs1'    => 'ADD1',
     *     'Dep Adrs2'    => 'ADD2',
     *     'Dep Adrs3'    => 'ADD3',
     *     'Dep Adrs4'    => 'ADD4',
     *     'Dep Adrs5'    => 'ADD5',
     *
     *     'Case'       => '10000003',
     *     'Forename'   => 'Cly3',
     *     'Surname'    => 'Hent3',
     *     'Corref'     => 'A3',
     *     'Report Due' => '05-Feb-15',
     * ]
     *
     * @param array $rows
     *
     * @return array
     */
    public function addFromCasrecRows(array $rows)
    {
        $this->added = ['users' => [], 'clients' => [], 'reports' => []];
        $errors = [];

        foreach ($rows as $index => $row) {
            $row = array_map('trim', $row);
            try {
                if ($row['Dep Type'] != 23) {
                    throw new \RuntimeException('Not a PA');
                }

                $user = $this->createUser($row);
                $client = $this->createClient($row, $user);
                $this->createReport($row, $client);
            } catch (\RuntimeException $e) {
                $errors[] = $e->getMessage() . ' in line ' . ($index + 2);
            }
            // clean up for next iteration
            $this->em->clear();
        }

        sort($this->added['users']);
        sort($this->added['clients']);
        sort($this->added['reports']);

        return ['added' => $this->added, 'errors' => $errors];
    }

    /**
     * @param array $row
     *
     * @return EntityDir\User
     */
    private function createUser(array $row)
    {
        $email = $row['Email'];
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            $user = new EntityDir\User();
            $user
                ->setRegistrationDate(new \DateTime())
                ->setDeputyNo($row['Deputy No'])
                ->setEmail($email)
                ->setFirstname($row['Dep Forename'])
                ->setLastname($row['Dep Surname'])
                ->setRoleName(EntityDir\User::ROLE_PA);

            if (!empty($row['Dep Adrs1'])) {
                $user->setAddress1($row['Dep Adrs1']);
            }

            if (!empty($row['Dep Adrs2'])) {
                $user->setAddress2($row['Dep Adrs2']);
            }

            if (!empty($row['Dep Adrs3']) || !empty($row['Dep Adrs4']) || !empty($row['Dep Adrs5'])) {
                $user->setAddress3($row['Dep Adrs3'] . ' ' . $row['Dep Adrs4'] . ' ' . $row['Dep Adrs5']);
            }

            if (!empty($row['Dep Postcode'])) {
                $user->setAddressPostcode($row['Dep Postcode']);
                $user->setAddressCountry('GB'); //postcode given means a UK address is given
            }

            // create team (if not already existing)
            if ($user->getTeams()->isEmpty()) {
                $team = new EntityDir\Team(null);
                $user->addTeam($team);
                $this->em->persist($team);
                $this->em->flush($team);
            }

            $this->added['users'][] = $email;
            $this->em->persist($user);
            $this->em->flush($user);
        }

        return $user;
    }

    /**
     * @param array $row
     * @param EntityDir\User $user
     *
     * @return EntityDir\Client
     */
    private function createClient(array $row, EntityDir\User $user)
    {
        // find or create client
        $caseNumber = strtolower($row['Case']);
        $client = $this->clientRepository->findOneBy(['caseNumber' => $caseNumber]);
        if ($client) {
            foreach ($client->getUsers() as $cu) {
                $client->getUsers()->removeElement($cu);
            }
        } else {
            $client = new EntityDir\Client();
            $client
                ->setCaseNumber($caseNumber)
                ->setFirstname(trim($row['Forename']))
                ->setLastname(trim($row['Surname']));

            if (!empty($row['Client Adrs1'])) {
                $client->setAddress($row['Client Adrs1']);
            }

            if (!empty($row['Client Adrs2'])) {
                $client->setAddress2($row['Client Adrs2']);
            }

            if (!empty($row['Client Adrs3']) || !empty($row['Client Adrs4'])) {
                $client->setCounty($row['Client Adrs3'] . ' ' . $row['Client Adrs4']);
            }

            if (!empty($row['Client Postcode'])) {
                $client->setPostcode($row['Client Postcode']);
                $client->setCountry('GB'); //postcode given means a UK address is given
            }

            $this->added['clients'][] = $client->getCaseNumber();
            $this->em->persist($client);
        }

        foreach ($user->getTeams() as $team) {
            foreach ($team->getMembers() as $member) {
                $member->addClient($client);
            }
        }

        $this->em->flush($client);

        return $client;
    }

    /**
     * @param array $row
     * @param EntityDir\Client $client
     *
     * @return EntityDir\Report\Report
     */
    private function createReport(array $row, EntityDir\Client $client)
    {
        // find or create reports
        $reportDueDate = self::parseDate($row['Report Due']);
        if (!$reportDueDate) {
            throw new \RuntimeException("Cannot parse date {$row['Report Due']}");
        }
        $reportEndDate = clone $reportDueDate;
        $reportEndDate->sub(new \DateInterval('P56D')); //Eight weeks behind due date
        $report = $client->getReportByDueDate($reportEndDate);
        if (!$report) {
            $report = new EntityDir\Report\Report();
            $client->addReport($report);
            $reportStartDate = clone $reportEndDate;
            $reportStartDate->sub(new \DateInterval('P1Y')); //One year behind end date
            $report
                ->setType(EntityDir\Report\Report::TYPE_102)
                ->setStartDate($reportStartDate)
                ->setEndDate($reportEndDate);
            $this->added['reports'][] = $client->getCaseNumber() . '-' . $reportDueDate->format('Y-m-d');
            $this->em->persist($report);
            $this->em->flush();
        }

        return $report;
    }

    /**
     * create DateTime object based on '16-Dec-2014' formatted dates
     * '16-Dec-14' format is accepted too, although seem deprecated according to latest given CSV files
     *
     * @param string $dateString e.g. 16-Dec-2014
     *
     * @return \DateTime|false
     */
    public static function parseDate($dateString)
    {
        $ret = \DateTime::createFromFormat('d-M-Y', $dateString);
        if (!$ret instanceof \DateTime || (int)$ret->format('Y') < 99) {
            $ret = \DateTime::createFromFormat('d-M-y', $dateString);
        }

        return $ret;
    }
}
