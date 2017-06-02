<?php

namespace AppBundle\Service;

use AppBundle\Entity as EntityDir;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;

class PaService
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;


    /**
     * @var array
     */
    protected $added = [];

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var array
     */
    protected $warnings = [];

    /**
     * PaService constructor.
     * @param EntityManager $em
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
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
    public function addFromCasrecRows(array $data)
    {
        $this->log('Received '.count($data).' records');

        $this->added = ['users' => [], 'clients' => [], 'reports' => []];
        $errors = [];
        foreach ($data as $index => $row) {

            $row = array_map('trim', $row);
            try {
                if ($row['Dep Type'] != 23) {
                    throw new \RuntimeException('Not a PA');
                }

                $user = $this->createUser($row);
                $client = $this->createClient($row, $user);
                $this->createReport($row, $client, $user);
            } catch (\Exception $e) {
                $message = 'Error for Deputy No: ' . $row['Deputy No'] . ', case '.$row['Case'].': ' . $e->getMessage();
                $errors[] = $message;
            }
        }

        sort($this->added['users']);
        sort($this->added['clients']);
        sort($this->added['reports']);

        return [
            'added' => $this->added,
            'errors' => $errors,
            'warnings' => $this->warnings
        ];
    }

    /**
     * @param array $row
     *
     * @return EntityDir\User
     */
    private function createUser(array $row)
    {
        $user = $this->userRepository->findOneBy(['deputyNo' => $row['Deputy No']]);
        $userEmail = strtolower($row['Email']);

        if (!$user) {
            $this->log('Creating user');
            // check for duplicate email address
            $user = $this->userRepository->findOneBy(['email' => $userEmail]);
            if ($user) {
                $this->warnings[] = 'Deputy ' . $row['Deputy No'] .
                    ' cannot be added with email ' . $user->getEmail() .
                    '. Email already taken by Deputy No: ' . $user->getDeputyNo();
            } else {

                $user = new EntityDir\User();
                $user
                    ->setRegistrationDate(new \DateTime())
                    ->setDeputyNo($row['Deputy No'])
                    ->setEmail($row['Email'])
                    ->setFirstname($row['Dep Forename'])
                    ->setLastname($row['Dep Surname'])
                    ->setRoleName(EntityDir\User::ROLE_PA);

                // create team (if not already existing)
                if ($user->getTeams()->isEmpty()) {
                    $team = new EntityDir\Team(null);

                    // Address from upload is the team's address, not the user's
                    if (!empty($row['Dep Adrs1'])) {
                        $team->setAddress1($row['Dep Adrs1']);
                    }

                    if (!empty($row['Dep Adrs2'])) {
                        $team->setAddress2($row['Dep Adrs2']);
                    }

                    if (!empty($row['Dep Adrs3'])) {
                        $team->setAddress3($row['Dep Adrs3']);
                    }

                    if (!empty($row['Dep Postcode'])) {
                        $team->setAddressPostcode($row['Dep Postcode']);
                        $team->setAddressCountry('GB'); //postcode given means a UK address is given
                    }

                    $user->addTeam($team);
                    $this->em->persist($team);
                    $this->em->flush($team);
                }

                $this->userRepository->hardDeleteExistingUser($user);
                $this->em->persist($user);
                $this->em->flush($user);
                $this->added['users'][] = $row['Email'];
            }
        } else {
            // Notify email change
            if ($user->getEmail() !== $userEmail) {
                $this->warnings[] = 'Deputy ' . $user->getDeputyNo() .
                    ' previously with email ' . $user->getEmail() .
                    ' has changed email to ' . $userEmail;
            }
        }

        return $user;
    }

    /**
     * //TODO subsquents uploads removes all the clients, and re-add them from the team.
     * Slow and a code smell that the db structure needs a change and connect clients to the team, or
     * a more general "deputyship" new entity, used by both Teams of PA, and Lay
     *
     * @param array          $row
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
            $this->log('Creating client');
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

            if (!empty($row['Client Adrs3'])) {
                $client->setCounty($row['Client Adrs3']);
            }

            if (!empty($row['Client Postcode'])) {
                $client->setPostcode($row['Client Postcode']);
                $client->setCountry('GB'); //postcode given means a UK address is given
            }

            if (!empty($row['Client Phone'])) {
                $client->setPhone($row['Client Phone']);
            }

            if (!empty($row['Client Email'])) {
                $client->setEmail($row['Client Email']);
            }

            if (!empty($row['Client Date of Birth'])) {
                $client->setDateOfBirth(self::parseDate($row['Client Date of Birth']));
            }

            $this->added['clients'][] = $client->getCaseNumber();
            $this->em->persist($client);
        }

        //Add client to user
        $user->addClient($client);

        //Also add client to team members
        foreach ($user->getTeams() as $team) {
            foreach ($team->getMembers() as $member) {
                if ($member->getId() != $user->getId()) {
                    $member->addClient($client);
                }
            }
        }

        $this->em->flush($client);

        return $client;
    }

    /**
     * @param array            $row
     * @param EntityDir\Client $client
     *
     * @return EntityDir\Report\Report
     */
    private function createReport(array $row, EntityDir\Client $client, EntityDir\User $user)
    {
        // find or create reports
        $reportDueDate = self::parseDate($row['Report Due']);
        if (!$reportDueDate) {
            throw new \RuntimeException("Cannot parse date {$row['Report Due']}");
        }
        $reportEndDate = clone $reportDueDate;
        $reportEndDate->sub(new \DateInterval('P56D')); //Eight weeks behind due date
        $reportType = EntityDir\CasRec::getTypeBasedOnTypeofRepAndCorref($row['Typeofrep'], $row['Corref']);
        $report = $client->getReportByDueDate($reportEndDate);
        if ($report) {
            if ($report->getType() != $reportType) {
                $this->log('Changing report type');
                $report->setType($reportType);
                $this->em->persist($report);
                $this->em->flush();
            }
        } else {
            $this->log('Creating report');
            $report = new EntityDir\Report\Report($client);
            $client->addReport($report);   //double link for testing reasons
            $reportStartDate = clone $reportEndDate;
            $reportStartDate->sub(new \DateInterval('P1Y')); //One year behind end date
            $report
                ->setStartDate($reportStartDate)
                ->setEndDate($reportEndDate)
                ->setType($reportType);

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
        if (!$ret instanceof \DateTime || (int) $ret->format('Y') < 99) {
            $ret = \DateTime::createFromFormat('d-M-y', $dateString);
        }

        return $ret;
    }

    /**
     * @param $message
     */
    private function log($message)
    {
        $this->logger->debug(__CLASS__.':'.$message);
    }
}
