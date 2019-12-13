<?php

namespace AppBundle\Service;

use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Client;
use AppBundle\Entity\Organisation;
use AppBundle\Entity\Repository\ClientRepository;
use AppBundle\Entity\Repository\OrganisationRepository;
use AppBundle\Entity\Repository\ReportRepository;
use AppBundle\Entity\Repository\TeamRepository;
use AppBundle\Entity\Repository\UserRepository;
use AppBundle\Entity\Repository\NamedDeputyRepository;
use AppBundle\Entity\User;
use AppBundle\Factory\NamedDeputyFactory;
use AppBundle\Factory\OrganisationFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class OrgService
{
    public const DEFAULT_ORG_NAME = 'Your Organisation';

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var OrganisationRepository
     */
    private $orgRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var ReportRepository
     */
    private $reportRepository;

    /**
     * @var ClientRepository
     */
    private $clientRepository;

    /**
     * @var TeamRepository
     */
    private $teamRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var OrganisationFactory
     */
    private $orgFactory;

    /**
     * @var NamedDeputyFactory
     */
    private $namedDeputyFactory;

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
     * @var Organisation|null
     */
    private $currentOrganisation;

    private $debug = false;

    /**
     * @var EntityDir\Repository\NamedDeputyRepository
     */
    private $namedDeputyRepository;

    /**
     * @var array
     */
    private $log;

    /**
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     * @param UserRepository $userRepository
     * @param ReportRepository $reportRepository
     * @param ClientRepository $clientRepository
     * @param OrganisationRepository $orgRepository
     * @param TeamRepository $teamRepository
     * @param NamedDeputyRepository $namedDeputyRepository
     * @param OrganisationFactory $orgFactory
     * @param NamedDeputyFactory $namedDeputyFactory
     */
    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
        UserRepository $userRepository,
        ReportRepository $reportRepository,
        ClientRepository $clientRepository,
        OrganisationRepository $orgRepository,
        TeamRepository $teamRepository,
        NamedDeputyRepository $namedDeputyRepository,
        OrganisationFactory $orgFactory,
        NamedDeputyFactory $namedDeputyFactory
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->userRepository = $userRepository;
        $this->reportRepository = $reportRepository;
        $this->clientRepository = $clientRepository;
        $this->orgRepository = $orgRepository;
        $this->teamRepository = $teamRepository;
        $this->namedDeputyRepository = $namedDeputyRepository;
        $this->orgFactory = $orgFactory;
        $this->namedDeputyFactory = $namedDeputyFactory;
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
     * @param array $data
     *
     * @return array
     */
    public function addFromCasrecRows(array $data)
    {
        $this->log('Received ' . count($data) . ' records');

        $this->added = ['clients' => [], 'discharged_clients' => [], 'named_deputies' => [], 'reports' => []];

        $errors = [];
        foreach ($data as $index => $row) {
            $row = array_map('trim', $row);
            try {
                $this->currentOrganisation = $this->orgRepository->findByEmailIdentifier($row['Email']);
                if (null === $this->currentOrganisation) {
                    $this->currentOrganisation = $this->createOrganisationFromEmail(self::DEFAULT_ORG_NAME, $row['Email']);
                }

                if (null === ($namedDeputy = $this->identifyNamedDeputy($row))) {
                    $namedDeputy = $this->createNamedDeputy($row);
                }

                $client = $this->upsertClientFromCsv($row, $namedDeputy);
                if ($client instanceof EntityDir\Client) {
                    $this->upsertReportFromCsv($row, $client);
                } else {
                    throw new \RuntimeException('Client could not be identified or created');
                }
            } catch (\Throwable $e) {
                $message = 'Error for Case: ' . $row['Case'] . ' for Deputy No: ' . $row['Deputy No'] . ': ' . $e->getMessage();
                $errors[] = $message;
            }
        }

        sort($this->added['named_deputies']);
        sort($this->added['clients']);
        sort($this->added['discharged_clients']);
        sort($this->added['reports']);

        return [
            'added'    => $this->added,
            'errors'   => $errors,
            'warnings' => $this->warnings,
        ];
    }

    /**
     * @param string $email
     * @return Organisation
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function createOrganisationFromEmail(string $name, string $email)
    {
        $organisation = $this->orgFactory->createFromFullEmail($name, $email);
        $this->em->persist($organisation);
        $this->em->flush();

        return $organisation;
    }

    /**
     * @param Client $client
     * @return bool
     */
    private function clientHasLayDeputy(Client $client)
    {
        if (!$client->hasDeputies()) return false;

        foreach ($client->getUsers() as $user) {
            if ($user->isLayDeputy()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $row keys: Case, caseNumber, Forename, Surname, Client Adrs1...
     * @param User $userOrgNamed the user the client should belong to
     *
     * @return Client
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function upsertClientFromCsv(array $row, EntityDir\NamedDeputy $namedDeputy)
    {
        // find or create client
        $caseNumber = Client::padCaseNumber(strtolower($row['Case']));

        /** @var Client|null $client */
        $client = $this->clientRepository->findOneBy(['caseNumber' => $caseNumber]);

        if ($client && $this->clientHasLayDeputy($client)) {
            throw new \RuntimeException('Case number already used');
        }

        if ($client && $this->clientHasSwitchedOrganisation($client)) {
            // todo this is a temporary exception for debugging.
            throw new \RuntimeException(sprintf('Case %s has switched organisation', $client->getCaseNumber()));
            $csvDeputyNo = EntityDir\User::padDeputyNumber($row['Deputy No']);
            if ($client->getNamedDeputy() instanceof EntityDir\NamedDeputy && $client->getNamedDeputy()->getDeputyNo() !== $csvDeputyNo) {
                // discharge client and recreate new one
                $this->dischargeClient($client);
                unset($client);
            } else {
                $client->setOrganisation(null);
            }
        }

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

        if (null !== $this->currentOrganisation) {
            $this->attachClientToOrganisation($client);
        }

        return $client;
    }

    /**
     * @param array            $csvRow keys: Last Report Day, Typeofrep, }
     * @param Client $client the client the report should belong to
     *
     * @throws OptimisticLockException
     *
     * @return EntityDir\Report\Report
     */
    private function upsertReportFromCsv(array $csvRow, Client $client)
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
                $this->log('Changing report type from ' . $report->getType() . ' to ' . $reportType);
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
     * @param User $userCreator
     * @param string $id
     *
     * @throws AccessDeniedException if user not part of the team the creator user belongs to
     *
     * @return User|null|object
     *
     */
    public function getMemberById(User $userCreator, string $id)
    {
        $user = $this->userRepository->find($id);
        if (!array_key_exists($id, $userCreator->getMembersInAllTeams())) {
            throw new AccessDeniedException('User not part of the same team');
        }

        return $user;
    }

    /**
     * @param User $userWithTeams
     * @param User $userBeingAdded
     */
    public function addUserToUsersTeams(User $userWithTeams, User $userBeingAdded)
    {
        $teamIds = $this->teamRepository->findAllTeamIdsByUser($userWithTeams);

        foreach ($teamIds as $teamId) {
            $this->clientRepository->saveUserToTeam($userBeingAdded, $teamId);
        }
    }

    /**
     * @param User $userWithClients
     * @param User $userBeingAdded
     */
    public function addUserToUsersClients(User $userWithClients, User $userBeingAdded)
    {
        $clientIds = $this->clientRepository->findAllClientIdsByUser($userWithClients);

        foreach ($clientIds as $clientId) {
            $this->clientRepository->saveUserToClient($userBeingAdded, $clientId);
        }
    }

    /**
     * Delete $user from all the teams $loggedInUser belongs to
     * Also removes the user, if doesn't belong to any team any longer
     *
     * @param User $loggedInUser
     * @param User $user
     *
     * @throws OptimisticLockException
     */
    public function removeUserFromTeamsOf(User $loggedInUser, User $user)
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
     * @param string $message
     */
    private function log(string $message)
    {
        if ($this->debug) {
            $this->logger->warning(__CLASS__ . ':' . $message);
        }
    }

    /**
     * @param $csvRow
     * @return EntityDir\NamedDeputy|null|object
     */
    public function identifyNamedDeputy($csvRow)
    {
        $deputyNo = EntityDir\User::padDeputyNumber($csvRow['Deputy No']);

        $namedDeputy = $this->namedDeputyRepository->findOneBy([
            'deputyNo' => $deputyNo,
            'email1' => strtolower($csvRow['Email']),
            'firstname' => $csvRow['Dep Forename'],
            'lastname' => $csvRow['Dep Surname'],
        ]);

        return $namedDeputy;
    }

    /**
     * @param $csvRow
     * @return EntityDir\NamedDeputy
     */
    public function createNamedDeputy($csvRow)
    {
        $deputyNo = EntityDir\User::padDeputyNumber($csvRow['Deputy No']);

        $namedDeputy = $this->namedDeputyFactory->createFromOrgCsv($csvRow);
        $this->em->persist($namedDeputy);
        $this->em->flush($namedDeputy);

        $this->added['named_deputies'][] = $deputyNo;

        return $namedDeputy;
    }

    /**
     * @param Client $client
     */
    private function attachClientToOrganisation(Client $client): void
    {
        if ($this->currentOrganisation !== null) {
            $this->currentOrganisation->addClient($client);
            $client->setOrganisation($this->currentOrganisation);
        }
    }

    /**
     * Returns true if clients organisation has changed
     *
     * @param EntityDir\Client $client
     * @return bool
     */
    private function clientHasSwitchedOrganisation(EntityDir\Client $client)
    {
        if (
            $client->getOrganisation() instanceof Organisation
            && $client->getOrganisation()->getId() !== $this->currentOrganisation->getId()
        ) {
            return true;
        }

        return false;
    }

    private function dischargeClient(EntityDir\Client $client)
    {
        $this->added['discharged_clients'][] = $client->getCaseNumber();
        $client->setDeletedAt(new\DateTime());
    }
}
