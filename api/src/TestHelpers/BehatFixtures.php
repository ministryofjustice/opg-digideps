<?php declare(strict_types=1);


namespace App\TestHelpers;

use App\Entity\Ndr\Ndr;
use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Entity\User;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

// Not extending AbstractDataFixture so we can use this in test runs rather than just commands
class BehatFixtures
{
    private EntityManagerInterface $entityManager;
    private array $fixtureParams;
    private UserPasswordEncoderInterface $encoder;
    private string $symfonyEnvironment;
    private UserTestHelper $userTestHelper;
    private ReportTestHelper $reportTestHelper;
    private ClientTestHelper $clientTestHelper;
    private OrganisationTestHelper $organisationTestHelper;
    private NamedDeputyTestHelper $namedDeputyTestHelper;

    private User $admin;
    private User $elevatedAdmin;
    private User $superAdmin;

    private User $layNotStarted;
    private User $layCompleted;
    private User $laySubmitted;

    private User $ndrLayNotStarted;
    private User $ndrLayCompleted;
    private User $ndrLaySubmitted;

    private User $profAdminNotStarted;
    private User $profAdminCompleted;
    private User $profAdminSubmitted;

    private string $testRunId = '';
    private string $orgName = 'Test Org';
    private string $orgEmailIdentifier = 'test-org.uk';

    public function __construct(
        EntityManagerInterface $entityManager,
        array $fixtureParams,
        UserPasswordEncoderInterface $encoder,
        string $symfonyEnvironment
    ) {
        $this->entityManager = $entityManager;
        $this->fixtureParams = $fixtureParams;
        $this->encoder = $encoder;
        $this->symfonyEnvironment = $symfonyEnvironment;

        $this->userTestHelper = new UserTestHelper();
        $this->reportTestHelper = new ReportTestHelper();
        $this->clientTestHelper = new ClientTestHelper();
        $this->organisationTestHelper = new OrganisationTestHelper();
        $this->namedDeputyTestHelper = new NamedDeputyTestHelper();
    }

    /**
     * @param string $testRunId
     * @return array
     * @throws Exception
     */
    public function loadFixtures(string $testRunId)
    {
        if ($this->symfonyEnvironment === 'prod') {
            throw new Exception('Prod mode enabled - cannot purge database');
        }

        $purger = new ORMPurger($this->entityManager);
        $purger->purge();

        $this->testRunId = $testRunId;

        $this->createUserFixtures();

        return [
            'admin-users' => [
                'admin' => self::buildAdminUserDetails($this->admin),
                'elevated-admin' => self::buildAdminUserDetails($this->elevatedAdmin),
                'super-admin' => self::buildAdminUserDetails($this->superAdmin),
            ],
            'lays' => [
                'not-started' => self::buildUserDetails($this->layNotStarted),
                'completed' => self::buildUserDetails($this->layCompleted),
                'submitted' => self::buildUserDetails($this->laySubmitted),
            ],
            'lays-ndr' => [
                'not-started' => self::buildUserDetails($this->ndrLayNotStarted),
                'completed' => self::buildUserDetails($this->ndrLayCompleted),
                'submitted' => self::buildUserDetails($this->ndrLaySubmitted)
            ],
            'professionals' => [
                'admin' => [
                    'not-started' => self::buildOrgUserDetails($this->profAdminNotStarted),
                    'completed' => self::buildOrgUserDetails($this->profAdminCompleted),
                    'submitted' => self::buildOrgUserDetails($this->profAdminSubmitted),
                ]
            ]
        ];
    }

    public static function buildUserDetails(User $user)
    {
        $client = $user->isLayDeputy() ? $user->getFirstClient() : $user->getOrganisations()[0]->getClients()[0];

        $currentReport = $user->getNdrEnabled() ? $client->getNdr() : $client->getCurrentReport();
        $currentReportType = $user->getNdrEnabled() ? null : $currentReport->getType();
        $previousReport = $user->getNdrEnabled() ? null : $client->getReports()[0];

        $userDetails =  [
            'userEmail' => $user->getEmail(),
            'userRole' => $user->getRoleName(),
            'userFirstName' => $user->getFirstname(),
            'userLastName' => $user->getLastname(),
            'userFullName' => $user->getFullName(),
            'userFullAddressArray' => array_filter([
                $user->getAddress1(),
                $user->getAddress2(),
                $user->getAddress3(),
                $user->getAddressPostcode(),
                $user->getAddressCountry()
            ]),
            'userPhone' => $user->getPhoneMain(),
            'courtOrderNumber' => $client->getCaseNumber(),
            'clientId' => $client->getId(),
            'clientFirstName' => $client->getFirstname(),
            'clientLastName' => $client->getLastname(),
            'clientCaseNumber' => $client->getCaseNumber(),
            'currentReportId' => $currentReport->getId(),
            'currentReportType' => $currentReportType,
            'currentReportNdrOrReport' => $currentReport instanceof Ndr ? 'ndr' : 'report',
            'currentReportDueDate' => $currentReport->getDueDate()->format('j F Y')
        ];

        if ($previousReport) {
            $userDetails = array_merge(
                $userDetails,
                [
                    'previousReportId' => $previousReport->getId(),
                    'previousReportType' => $previousReport->getType(),
                    'previousReportNdrOrReport' => $previousReport instanceof Ndr ? 'ndr' : 'report',
                    'previousReportDueDate' => $previousReport->getDueDate()->format('j F Y')
                ]
            );
        }

        return $userDetails;
    }

    public static function buildOrgUserDetails(User $user)
    {
        $organisation = $user->getOrganisations()->first();
        $namedDeputy = $organisation->getClients()[0]->getNamedDeputy();

        $details = [
            'organisationName' => $organisation->getName(),
            'namedDeputyName' => sprintf(
                '%s %s',
                $namedDeputy->getFirstname(),
                $namedDeputy->getLastName()
            ),
            'namedDeputyEmail' => $namedDeputy->getEmail1(),
        ];

        return array_merge(self::buildUserDetails($user), $details);
    }

    public static function buildAdminUserDetails(User $user)
    {
        return [
            'userEmail' => $user->getEmail(),
            'userRole' => $user->getRoleName(),
        ];
    }

    private function createUserFixtures()
    {
        $this->createAdminUsers();
        $this->createDeputies();

        $users = [
            $this->admin,
            $this->elevatedAdmin,
            $this->superAdmin,
            $this->layNotStarted,
            $this->layCompleted,
            $this->laySubmitted,
            $this->ndrLayNotStarted,
            $this->ndrLayCompleted,
            $this->ndrLaySubmitted,
            $this->profAdminNotStarted,
            $this->profAdminCompleted,
            $this->profAdminSubmitted,
        ];

        foreach ($users as $user) {
            $user->setPassword($this->encoder->encodePassword($user, $this->fixtureParams['account_password']));
            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();
    }

    private function createAdminUsers()
    {
        $this->admin = $this->userTestHelper
            ->createUser(null, User::ROLE_ADMIN, sprintf('admin-%s@t.uk', $this->testRunId));

        $this->elevatedAdmin = $this->userTestHelper
            ->createUser(null, User::ROLE_ELEVATED_ADMIN, sprintf('elevated-admin-%s@t.uk', $this->testRunId));

        $this->superAdmin = $this->userTestHelper
            ->createUser(null, User::ROLE_SUPER_ADMIN, sprintf('super-admin-%s@t.uk', $this->testRunId));
    }

    private function createDeputies()
    {
        $this->createLays();
        $this->createNdrLays();
        $this->createProfs();
    }

    private function createLays()
    {
        $this->layNotStarted = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-not-started-%s@t.uk', $this->testRunId));
        $this->addClientsAndReportsToLayDeputy($this->layNotStarted, false, false);

        $this->layCompleted = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-completed-%s@t.uk', $this->testRunId));
        $this->addClientsAndReportsToLayDeputy($this->layCompleted, true, false);

        $this->laySubmitted = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-submitted-%s@t.uk', $this->testRunId));
        $this->addClientsAndReportsToLayDeputy($this->laySubmitted, true, true);
    }

    private function createNdrLays()
    {
        $this->ndrLayNotStarted = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-ndr-not-started-%s@t.uk', $this->testRunId));
        $this->addClientsAndReportsToNdrLayDeputy($this->ndrLayNotStarted, false, false);

        $this->ndrLayCompleted = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-ndr-completed-%s@t.uk', $this->testRunId));
        $this->addClientsAndReportsToNdrLayDeputy($this->ndrLayCompleted, true, false);

        $this->ndrLaySubmitted = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-ndr-submitted-%s@t.uk', $this->testRunId));
        $this->addClientsAndReportsToNdrLayDeputy($this->ndrLaySubmitted, true, true);
    }

    private function createProfs()
    {
        $orgName = sprintf('prof-%s-%s', $this->orgName, $this->testRunId);
        $emailIdentifier = sprintf('prof-%s-%s', $this->orgEmailIdentifier, $this->testRunId);

        $organisation = $this->organisationTestHelper->createOrganisation($orgName, $emailIdentifier);
        $this->entityManager->persist($organisation);

        $this->profAdminNotStarted = $this->userTestHelper
            ->createUser(null, User::ROLE_PROF_ADMIN, sprintf('prof-admin-not-started-%s@t.uk', $this->testRunId));
        $this->addOrgClientsNamedDeputyAndReportsToOrgDeputy($this->profAdminNotStarted, $organisation, false, false);

        $this->profAdminCompleted = $this->userTestHelper
            ->createUser(null, User::ROLE_PROF_ADMIN, sprintf('prof-admin-completed-%s@t.uk', $this->testRunId));
        $this->addOrgClientsNamedDeputyAndReportsToOrgDeputy($this->profAdminCompleted, $organisation, true, false);

        $this->profAdminSubmitted = $this->userTestHelper
            ->createUser(null, User::ROLE_PROF_ADMIN, sprintf('prof-admin-submitted-%s@t.uk', $this->testRunId));
        $this->addOrgClientsNamedDeputyAndReportsToOrgDeputy($this->profAdminSubmitted, $organisation, true, true);
    }

    private function addClientsAndReportsToLayDeputy(User $deputy, bool $completed = false, bool $submitted = false)
    {
        $client = $this->clientTestHelper->generateClient($this->entityManager, $deputy);
        $report = $this->reportTestHelper->generateReport($this->entityManager, $client);

        $client->addReport($report);
        $report->setClient($client);
        $deputy->addClient($client);

        if ($completed) {
            $this->reportTestHelper->completeLayReport($report, $this->entityManager);
        }

        if ($submitted) {
            $this->reportTestHelper->submitReport($report, $this->entityManager);
        }

        $this->entityManager->persist($client);
        $this->entityManager->persist($report);
    }

    private function addClientsAndReportsToNdrLayDeputy(User $deputy, bool $completed = false, bool $submitted = false)
    {
        $client = $this->clientTestHelper->generateClient($this->entityManager, $deputy);

        $ndr = new Ndr($client);
        $deputy->setNdrEnabled(true);
        $client->setNdr($ndr);

        $deputy->addClient($client);

        if ($completed) {
            $this->reportTestHelper->completeNdrLayReport($ndr, $this->entityManager);
        }

//        if ($submitted) {
//            placeholder for when submitted version needed...
//        }

        $this->entityManager->persist($ndr);
        $this->entityManager->persist($client);
    }

    private function addOrgClientsNamedDeputyAndReportsToOrgDeputy(User $deputy, Organisation $organisation, bool $completed = false, bool $submitted = false)
    {
        $client = $this->clientTestHelper->generateClient($this->entityManager, $deputy, $organisation);
        $report = $this->reportTestHelper->generateReport($this->entityManager, $client);
        $namedDeputy = $this->namedDeputyTestHelper->generatenamedDeputy();

        $client->addReport($report);
        $client->setOrganisation($organisation);
        $client->setNamedDeputy($namedDeputy);

        $organisation->addClient($client);
        $organisation->addUser($deputy);

        $report->setClient($client);

        $deputy->addOrganisation($organisation);

        if ($completed) {
            $this->reportTestHelper->completeLayReport($report, $this->entityManager);
        }

        if ($submitted) {
            $this->reportTestHelper->submitReport($report, $this->entityManager);
        }

        $this->entityManager->persist($namedDeputy);
        $this->entityManager->persist($deputy);
        $this->entityManager->persist($client);
        $this->entityManager->persist($report);
    }
}
