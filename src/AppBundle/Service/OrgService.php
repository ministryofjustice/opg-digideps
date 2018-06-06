<?php

namespace AppBundle\Service;

use AppBundle\Entity as EntityDir;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class OrgService
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
     * @param EntityManager   $em
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
        $this->log('Received ' . count($data) . ' records');

        $this->added = ['prof_users' => [],'pa_users' => [], 'clients' => [], 'reports' => []];
        $errors = [];
        foreach ($data as $index => $row) {
            $row = array_map('trim', $row);
            try {
                $userOrgNamed = $this->upsertOrgNamedUserFromCsv($row);
                $client = $this->upsertClientFromCsv($row, $userOrgNamed);
                $this->upsertReportFromCsv($row, $client, $userOrgNamed);
            } catch (\Exception $e) {
                $message = 'Error for Case: ' . $row['Case'] . ' for Deputy No: ' . $row['Deputy No'] . ': ' . $e->getMessage();
                $errors[] = $message;
            }
        }

        sort($this->added['prof_users']);
        sort($this->added['pa_users']);
        sort($this->added['clients']);
        sort($this->added['reports']);

        return [
            'added'    => $this->added,
            'errors'   => $errors,
            'warnings' => $this->warnings,
        ];
    }

    /**
     * @param array $csvRow
     *
     * @return EntityDir\User
     */
    private function upsertOrgNamedUserFromCsv(array $csvRow)
    {
        $depType = $csvRow['Dep Type'];
        if (!isset(EntityDir\User::$depTypeIdToUserRole[$depType])) {
            throw new \RuntimeException('Dep Type not recognised');
        }
        $roleName = EntityDir\User::$depTypeIdToUserRole[$depType];
        $deputyNo = EntityDir\User::padDeputyNumber($csvRow['Deputy No']);
        $criteria = [
            'deputyNo' => $deputyNo,
            'roleName' => $roleName
        ];
        $user = $this->userRepository->findOneBy($criteria);
        $userEmail = strtolower($csvRow['Email']);

        if ($user) {
            // Notify email change
            if ($user->getEmail() !== $userEmail) {
                $this->warnings[$user->getDeputyNo()] = 'Deputy ' . $user->getDeputyNo() .
                    ' has changed their email to ' . $user->getEmail() . '. ' .
                    'Please update the CSV to reflect the new email address.<br />';
            }
        } else {
            // create user
            $this->log('Creating user');
            // check for duplicate email address
            $user = $this->userRepository->findOneBy(['email' => $userEmail]);
            if ($user) {
                $this->warnings[] = 'Deputy ' . $deputyNo .
                    ' cannot be added with email ' . $user->getEmail() .
                    '. Email already taken by Deputy No: ' . $user->getDeputyNo();
            } else {
                $user = new EntityDir\User();
                $user
                    ->setRegistrationDate(new \DateTime())
                    ->setDeputyNo($deputyNo)
                    ->setEmail($csvRow['Email'])
                    ->setFirstname($csvRow['Dep Forename'])
                    ->setLastname($csvRow['Dep Surname'])
                    ->setRoleName($roleName);

                // create team (if not already existing)
                if ($user->getTeams()->isEmpty()) {
                    // Dep Surname in the CSV is actually the PA team name
                    $team = new EntityDir\Team($csvRow['Dep Surname']);

                    // Address from upload is the team's address, not the user's
                    if (!empty($csvRow['Dep Adrs1'])) {
                        $team->setAddress1($csvRow['Dep Adrs1']);
                    }

                    if (!empty($csvRow['Dep Adrs2'])) {
                        $team->setAddress2($csvRow['Dep Adrs2']);
                    }

                    if (!empty($csvRow['Dep Adrs3'])) {
                        $team->setAddress3($csvRow['Dep Adrs3']);
                    }

                    if (!empty($csvRow['Dep Postcode'])) {
                        $team->setAddressPostcode($csvRow['Dep Postcode']);
                        $team->setAddressCountry('GB'); //postcode given means a UK address is given
                    }

                    $user->addTeam($team);
                    $this->em->persist($team);
                    $this->em->flush($team);
                }

                $this->em->persist($user);
                $this->em->flush($user);
                if ($user->isProfDeputy()) {
                    $this->added['prof_users'][] = $csvRow['Email'];
                } elseif ($user->isPaDeputy()) {
                    $this->added['pa_users'][] = $csvRow['Email'];
                }
            }
        }

        // update team name, if not set
        // can be removed if there is not need to update PA names after DDPB-1718
        // is released and one PA CSV upload is done
        if ($user->getTeams()->count()
            && ($team = $user->getTeams()->first())
            && $team->getTeamName() != $csvRow['Dep Surname']
        ) {
            $team->setTeamName($csvRow['Dep Surname']);
            $this->warnings[] = 'Organisation/Team ' . $team->getId() . ' updated to ' . $csvRow['Dep Surname'];
            $this->em->flush($team);
        }

        return $user;
    }

    /**
     * @param array          $row          keys: Case, caseNumber, Forename, Surname, Client Adrs1...
     * @param EntityDir\User $userOrgNamed the user the client should belong to
     *
     * @return EntityDir\Client
     */
    private function upsertClientFromCsv(array $row, EntityDir\User $userOrgNamed)
    {
        // find or create client
        $caseNumber = EntityDir\Client::padCaseNumber(strtolower($row['Case']));
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
                $client->setDateOfBirth(ReportUtils::parseCsvDate($row['Client Date of Birth'], '19') ?: null);
            }

            $this->added['clients'][] = $client->getCaseNumber();
            $this->em->persist($client);
        }

        // Add client to named user (will be done later anyway)
        $userOrgNamed->addClient($client);

        // Add client to all the team members of all teams the user belongs to
        // (duplicates are auto-skipped)
        foreach ($userOrgNamed->getTeams() as $team) {
            foreach ($team->getMembers() as $member) {
                //                if ($member->getId() != $userOrgNamed->getId()) {
                    $member->addClient($client);
//                }
            }
        }

        $this->em->flush();

        return $client;
    }

    /**
     * @param array            $csvRow keys: Last Report Day, Typeofrep, }
     * @param EntityDir\Client $client the client the report should belong to
     * @param EntityDir\User   $user   the user (needed for determine the report type, dependendent on user role)
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return EntityDir\Report\Report
     */
    private function upsertReportFromCsv(array $csvRow, EntityDir\Client $client, EntityDir\User $user)
    {
        // find or create reports
        $reportEndDate = ReportUtils::parseCsvDate($csvRow['Last Report Day'], '20');
        if (!$reportEndDate) {
            throw new \RuntimeException("Cannot parse date {$csvRow['Last Report Day']}");
        }
        $reportType = EntityDir\CasRec::getTypeBasedOnTypeofRepAndCorref($csvRow['Typeofrep'], $csvRow['Corref'], $user->getRoleName());
        $report = $client->getReportByEndDate($reportEndDate);

        // already existing, just change type
        if ($report) {
            // change report type if it's not already set AND report is not yet submitted
            if ($report->getType() != $reportType && !$report->getSubmitted()) {
                $this->log('Changing report type');
                $report->setType($reportType);
                $this->em->persist($report);
                $this->em->flush();
            }

            return $report;
        }

        $this->log('Creating report');
        $reportStartDate = ReportUtils::generateReportStartDateFromEndDate($reportEndDate);
        $report = new EntityDir\Report\Report($client, $reportType, $reportStartDate, $reportEndDate, true);
        $client->addReport($report);   //double link for testing reasons
        $this->added['reports'][] = $client->getCaseNumber() . '-' . $reportEndDate->format('Y-m-d');
        $this->em->persist($report);
        $this->em->flush();

        return $report;
    }

    /**
     * @param EntityDir\User $userCreator
     * @param $id
     *
     * @throws AccessDeniedException if user not part of the team the creator user belongs to
     *
     * @return EntityDir\User|null|object
     *
     */
    public function getMemberById(EntityDir\User $userCreator, $id)
    {
        $user = $this->em->getRepository(EntityDir\User::class)->find($id);
        if (!array_key_exists($id, $userCreator->getMembersInAllTeams())) {
            throw new AccessDeniedException('User not part of the same team');
        }

        return $user;
    }

    /**
     * Adds a new Org user and
     * - Sets the team name for the current logged user (using `pa_team_name` from the $data)
     * - Add this new user to the logged user's team
     * - Copy clients from logged in user into the this new user
     * Needs a flush at the end
     *
     * @param User $loggedInUser
     * @param User $userToAdd
     * @param $data
     */
    public function copyTeamAndClientsFrom(EntityDir\User $loggedInUser, EntityDir\User $userToAdd)
    {
        // add to creator's teams
        foreach ($loggedInUser->getTeams() as $team) {
            $userToAdd->addTeam($team);
        }

        // copy clients from logged user into this new user
        foreach ($loggedInUser->getClients() as $client) {
            $userToAdd->addClient($client);
        }
    }

    /**
     * Delete $user from all the teams $loggedInUser belongs to
     * Also removes the user, if doesn't belong to any team any longer
     *
     * @param EntityDir\User $loggedInUser
     * @param EntityDir\User $user
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removeUserFromTeamsOf(EntityDir\User $loggedInUser, EntityDir\User $user)
    {
        // remove user from teams the logged-user (operation performer) belongs to
        foreach ($loggedInUser->getTeams() as $team) {
            $user->getTeams()->removeElement($team);
        }

        // remove client that also belongs to the creator
        // (equivalent to remove client from all the teams of the creator)
        foreach ($loggedInUser->getClients() as $client) {
            $client->removeUser($user);
        }

        // remove user if belonging to no teams
        if (count($user->getTeams()) === 0) {
            $this->em->remove($user);
        }

        $this->em->flush();
    }

    /**
     * @param $message
     */
    private function log($message)
    {
        $this->logger->debug(__CLASS__ . ':' . $message);
    }
}
