<?php

namespace AppBundle\Service;

use AppBundle\Entity as EntityDir;
use AppBundle\Factory\OrganisationFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class OrgService
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EntityDir\Repository\OrganisationRepository
     */
    private $orgRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var OrganisationFactory
     */
    private $orgFactory;

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
     * @var EntityDir\Organisation
     */
    private $currentOrganisation;

    private $debug = false;

    /**
     * @var EntityDir\Repository\NamedDeputyRepository
     */
    private $namedDeputyRepository;

    /**
     * OrgService constructor.
     * @param EntityManager $em
     * @param LoggerInterface $logger
     * @param OrganisationFactory $orgFactory
     */
    public function __construct(EntityManager $em, LoggerInterface $logger, OrganisationFactory $orgFactory)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->userRepository = $em->getRepository(EntityDir\User::class);
        $this->reportRepository = $em->getRepository(EntityDir\Report\Report::class);
        $this->clientRepository = $em->getRepository(EntityDir\Client::class);
        $this->namedDeputyRepository = $em->getRepository(EntityDir\NamedDeputy::class);
        $this->orgRepository = $em->getRepository(EntityDir\Organisation::class);
        $this->log = [];
        $this->orgFactory = $orgFactory;
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

        $this->added = ['prof_users' => [], 'pa_users' => [], 'named_deputies' => [], 'clients' => [], 'reports' => []];
        $errors = [];
        foreach ($data as $index => $row) {
            $row = array_map('trim', $row);
            try {

                $this->currentOrganisation = $this->orgRepository->findByEmailIdentifier($row['Email']);
                if (null === $this->currentOrganisation) {
                    $this->currentOrganisation = $this->createOrganisationFromEmail($row['Email']);
                    // Create initial user for organisation
//                    $user = new EntityDir\User();
//                    $user
//                        ->setRegistrationDate(new \DateTime())
//                        ->setDeputyNo(EntityDir\User::padDeputyNumber($row['Deputy No']))
//                        ->setEmail($row['Email'])
//                        ->setFirstname($row['Dep Forename'])
//                        ->setLastname($row['Dep Surname'])
//                        ->setRoleName(EntityDir\User::$depTypeIdToUserRole[$row['Dep Type']]);
//
//                    // update user address, if not set
//                    // the following could be moved to line 154 if no update is needed (DDPB-2262)
//                    if (!empty($csvRow['Dep Adrs1']) && !$user->getAddress1()) {
//                        $user
//                            ->setAddress1($row['Dep Adrs1'])
//                            ->setAddress2($row['Dep Adrs2'])
//                            ->setAddress3($row['Dep Adrs3'])
//                            ->setAddressPostcode($row['Dep Postcode'])
//                            ->setAddressCountry('GB')
//                        ;
//                    }
//
//                    $this->em->persist($user);
//                    $this->em->flush($user);
//
//                    if ($user->isProfDeputy()) {
//                        $this->added['prof_users'][] = $row['Email'];
//                    } elseif ($user->isPaDeputy()) {
//                        $this->added['pa_users'][] = $row['Email'];
//                    }
//
//                    $this->currentOrganisation->addUser($user);
                }

                $namedDeputy = $this->identifyNamedDeputy($row);
                $this->em->persist($namedDeputy);
                $this->em->flush($namedDeputy);

                $client = $this->upsertClientFromCsv($row, $namedDeputy);
                if ($client instanceof EntityDir\Client) {
                    if ($client->hasDeputies()) {
                        $this->upsertReportFromCsv($row, $client);
                    }
                } else {
                    throw new \RuntimeException('Client could not be identified or created');
                }

//                if ($userOrgNamed instanceof EntityDir\User) {
//
//                    $client = $this->upsertClientFromCsv($row, $userOrgNamed);
//                    if ($client instanceof EntityDir\Client) {
//                        $this->upsertReportFromCsv($row, $client, $userOrgNamed);
//                    } else {
//                        throw new \RuntimeException('Client could not be identified or created');
//                    }
//                } else {
//                    throw new \RuntimeException('Named deputy could not be identified or created');
//                }

            } catch (\Throwable $e) {
                $message = 'Error for Case: ' . $row['Case'] . ' for Deputy No: ' . $row['Deputy No'] . ': ' . $e->getMessage();
                $errors[] = $message;
            }
        }

        sort($this->added['prof_users']);
        sort($this->added['pa_users']);
        sort($this->added['named_deputies']);
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

        $csvEmail = strtolower($csvRow['Email']);
        $deputyNo = EntityDir\User::padDeputyNumber($csvRow['Deputy No']);
        $this->log('Processing row:  deputy := deputy no: ' . $deputyNo . ', dep type: ' . $depType . ' with email ' . $csvEmail);
        if (!isset(EntityDir\User::$depTypeIdToUserRole[$depType])) {
            throw new \RuntimeException('Dep Type not recognised');
        }
        $roleName = EntityDir\User::$depTypeIdToUserRole[$depType];

        $user = $this->userRepository->findOneBy([
            'deputyNo' => $deputyNo,
            'roleName' => $roleName
        ]);

        // Notify email change
        if ($user && $user->getEmail() !== $csvEmail) {
            $this->warnings[$user->getDeputyNo()] = 'Deputy ' . $user->getDeputyNo() .
                ' has changed their email to ' . $user->getEmail() . '. ' .
                'Please update the CSV to reflect the new email address.<br />';
        }

        // create user if not existing
        if (!$user) {
            // check for duplicate email address

            $userWithSameEmail = $this->userRepository->findOneBy(['email' => $csvEmail]);
            if ($userWithSameEmail) {
                $this->log('Deputy email address already exists ');
                $this->warnings[] = 'Deputy ' . $deputyNo .
                    ' cannot be added with email ' . $csvEmail .
                    '. Email already taken by Deputy No: ' . $userWithSameEmail->getDeputyNo();
            } else {
                $this->log('Creating new deputy ' . $deputyNo);

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
                    $this->log('Creating new team: ' . $csvRow['Dep Surname']);

                    // Dep Surname in the CSV is actually the PA team name
                    $team = new EntityDir\Team($csvRow['Dep Surname']);
                    $user->addTeam($team);
                    $this->em->persist($team);
                    $this->em->flush($team);
                }

                if ($user->isProfDeputy()) {
                    $this->added['prof_users'][] = $csvRow['Email'];
                } elseif ($user->isPaDeputy()) {
                    $this->added['pa_users'][] = $csvRow['Email'];
                }

            }
        }

        // update user address, if not set
        // the following could be moved to line 154 if no update is needed (DDPB-2262)
        if ($user instanceof EntityDir\User && (!empty($csvRow['Dep Adrs1']) && !$user->getAddress1())) {
            $user
                ->setAddress1($csvRow['Dep Adrs1'])
                ->setAddress2($csvRow['Dep Adrs2'])
                ->setAddress3($csvRow['Dep Adrs3'])
                ->setAddressPostcode($csvRow['Dep Postcode'])
                ->setAddressCountry('GB')
            ;
        }

        // update team name, if not set
        // can be removed if there is not need to update PA names after DDPB-1718
        // is released and one PA CSV upload is done
        if ($user instanceof EntityDir\User && $user->getTeams()->count()
            && ($team = $user->getTeams()->first())
            && $team->getTeamName() != $csvRow['Dep Surname']
        ) {
            $team->setTeamName($csvRow['Dep Surname']);
            $this->warnings[] = 'Organisation/Team ' . $team->getId() . ' updated to ' . $csvRow['Dep Surname'];
            $this->em->flush($team);
        }

        if ($user instanceof EntityDir\User) {
            $this->em->persist($user);
            $this->em->flush($user);
        }

        $this->currentOrganisation = $this->orgRepository->findByEmailIdentifier($csvRow['Email']);
        if (null === $this->currentOrganisation) {
            $this->currentOrganisation = $this->createOrganisationFromEmail($csvRow['Email']);
        }

        return $user;
    }

    /**
     * @param string $email
     * @return EntityDir\Organisation
     * @throws \Doctrine\ORM\ORMException
     */
    private function createOrganisationFromEmail($email)
    {
        $organisation = $this->orgFactory->createFromFullEmail($email, $email);
        $this->em->persist($organisation);
        $this->em->flush($organisation);

        return $organisation;
    }

    /**
     * @param array          $row          keys: Case, caseNumber, Forename, Surname, Client Adrs1...
     * @param EntityDir\User $userOrgNamed the user the client should belong to
     *
     * @return EntityDir\Client
     */
    private function upsertClientFromCsv(array $row, EntityDir\NamedDeputy $namedDeputy)
    {
        // find or create client
        $caseNumber = EntityDir\Client::padCaseNumber(strtolower($row['Case']));

        /** @var EntityDir\Client $client */
        $client = $this->clientRepository->findOneBy(['caseNumber' => $caseNumber]);

        if ($client) {
            $this->log('FOUND client in database with id: ' . $client->getId());
            //$client->setUsers(new ArrayCollection());
        } else {
            $this->log('Creating client');
            $client = new EntityDir\Client();
            $caseNumber = EntityDir\Client::padCaseNumber(strtolower($row['Case']));
            $this->added['clients'][] = $caseNumber;

        }

        // Upsert Client information
        $client = $this->upsertClientDetailsFromCsv($client, $namedDeputy, $row);

        // Add client to named user (will be done later anyway)
        //$client->addUser($userOrgNamed);
        $this->attachClientToOrganisation($client);

        // Add client to all the team members of all teams the user belongs to
        // (duplicates are auto-skipped)

//        $teams = $userOrgNamed->getTeams();
//        $depCount = 0;
//        foreach ($teams as $team) {
//            $members = $team->getMembers();
//            foreach ($members as $member) {
//                $client->addUser($member);
//                $depCount++;
//            }
//        }
//        $this->log('Assigned ' . $depCount . ' additional deputies to client');

        $this->em->persist($client);

        $this->em->flush();

        return $client;
    }

    /**
     * Applies any updated information in the csv to new and existing clients
     *
     * @param EntityDir\Client $client
     * @param $row
     * @return EntityDir\Client
     */
    private function upsertClientDetailsFromCsv(EntityDir\Client $client, EntityDir\NamedDeputy $namedDeputy, $row)
    {
        $caseNumber = EntityDir\Client::padCaseNumber(strtolower($row['Case']));
        $client->setCaseNumber($caseNumber);
        $client->setFirstname(trim($row['Forename']));
        $client->setLastname(trim($row['Surname']));

        // set court date from Last report day
        $courtDate = new \DateTime($row['Last Report Day']);
        $client->setCourtDate($courtDate->modify('-1year +1day'));

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

        $this->log('Setting named deputy on client to deputy id:' . $namedDeputy->getId());
        $client->setNamedDeputy($namedDeputy);

        return $client;
    }

    /**
     * @param array            $csvRow keys: Last Report Day, Typeofrep, }
     * @param EntityDir\Client $client the client the report should belong to
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return EntityDir\Report\Report
     */
    private function upsertReportFromCsv(array $csvRow, EntityDir\Client $client)
    {
        // find or create reports
        $reportEndDate = ReportUtils::parseCsvDate($csvRow['Last Report Day'], '20');
        if (!$reportEndDate) {
            throw new \RuntimeException("Cannot parse date {$csvRow['Last Report Day']}");
        }

        $reportType = EntityDir\CasRec::getTypeBasedOnTypeofRepAndCorref(
            $csvRow['Typeofrep'],
            $csvRow['Corref'],
            EntityDir\User::$depTypeIdToUserRole[$csvRow['Dep Type']]
        );

        $report = $client->getCurrentReport();

        // already existing, just change type
        if ($report) {
            // change report type if it's not already set AND report is not yet submitted
            if ($report->getType() != $reportType && !$report->getSubmitted() && empty($report->getUnSubmitDate())) {
                $this->log('Changing report type from ' . $report->getType() .   ' to ' . $reportType);
                $report->setType($reportType);
                $this->em->persist($report);
                $this->em->flush();
            }

            return $report;
        }

        $this->log('Creating new report');
        $reportStartDate = ReportUtils::generateReportStartDateFromEndDate($reportEndDate);
        $report = new EntityDir\Report\Report($client, $reportType, $reportStartDate, $reportEndDate, true);
        $client->addReport($report);   //double link for testing reasons
        $this->added['reports'][] = $client->getCaseNumber() . '-' . $reportEndDate->format('Y-m-d');
        $this->em->persist($report);
        $this->em->flush();
        $this->em->clear();
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
     * @param EntityDir\User $userWithTeams
     * @param EntityDir\User $userBeingAdded
     */
    public function addUserToUsersTeams(EntityDir\User $userWithTeams, EntityDir\User $userBeingAdded)
    {
        $teamIds = $this->em->getRepository('AppBundle\Entity\Team')->findAllTeamIdsByUser($userWithTeams);

        foreach ($teamIds as $teamId) {
            $this
                ->em
                ->getRepository('AppBundle\Entity\Client')
                ->saveUserToTeam($userBeingAdded, $teamId);
        }
    }

    /**
     * @param EntityDir\User $userWithClients
     * @param EntityDir\User $userBeingAdded
     */
    public function addUserToUsersClients(EntityDir\User $userWithClients, EntityDir\User $userBeingAdded)
    {
        $clientIds = $this->em->getRepository('AppBundle\Entity\Client')->findAllClientIdsByUser($userWithClients);

        foreach ($clientIds as $clientId) {
            $this
                ->em
                ->getRepository('AppBundle\Entity\Client')
                ->saveUserToClient($userBeingAdded, $clientId);
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
        if ($this->debug) {
            $this->logger->warning(__CLASS__ . ':' . $message);
        }
    }

    /**
     * @param $csvRow
     * @return EntityDir\NamedDeputy|null|object
     */
    private function identifyNamedDeputy($csvRow)
    {
        $deputyNo = EntityDir\User::padDeputyNumber($csvRow['Deputy No']);

        $namedDeputy = $this->namedDeputyRepository->findOneBy([
            'deputyNo' => $deputyNo,
            'email1' => $csvRow['Email']
        ]);

        // should we update named deputy details here ?

        if (!$namedDeputy instanceof EntityDir\NamedDeputy) {
            $namedDeputy = new EntityDir\NamedDeputy(
                $csvRow['Deputy No'],
                $csvRow['Email'],
                $csvRow['Dep Adrs1'],
                $csvRow['Dep Adrs2'],
                $csvRow['Dep Adrs3'],
                $csvRow['Dep Postcode'],
                $csvRow['Dep Adrs4'],
                $csvRow['Dep Adrs5'],
                $csvRow
            );
            $this->added['named_deputies'][] = $deputyNo;
        }

        return $namedDeputy;
    }

    /**
     * @param EntityDir\Client $client
     */
    private function attachClientToOrganisation(EntityDir\Client $client): void
    {
        $this->currentOrganisation->addClient($client);
        $client->addOrganisation($this->currentOrganisation);
        $this->currentOrganisation = null;
    }
}
