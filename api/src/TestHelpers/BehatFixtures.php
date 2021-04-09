<?php declare(strict_types=1);


namespace App\TestHelpers;

use App\Entity\Organisation;
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

    private User $admin;
    private User $superAdmin;

    private User $layNotStarted;
    private User $layCompleted;
    private User $laySubmitted;

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
                'admin' => [
                    'email' => $this->admin->getEmail()
                ],
                'super-admin' => [
                    'email' => $this->superAdmin->getEmail()
                ]
            ],
            'lays' => [
                'not-started' => $this->buildLayUserDetails($this->layNotStarted),
                'completed' => $this->buildLayUserDetails($this->layCompleted),
                'submitted' => $this->buildLayUserDetails($this->laySubmitted),
            ],
            'professionals' => [
                'admin' => [
                    'not-started' => $this->buildOrgUserDetails($this->profAdminNotStarted),
                    'completed' => $this->buildOrgUserDetails($this->profAdminCompleted),
                    'submitted' => $this->buildOrgUserDetails($this->profAdminSubmitted),
                ]
            ]
        ];
    }

    private function buildLayUserDetails(User $user)
    {
        return [
            'email' => $user->getEmail(),
            'clientId' => $user->getFirstClient()->getId(),
            'clientFirstName' => $user->getFirstClient()->getFirstname(),
            'clientLastName' => $user->getFirstClient()->getLastname(),
            'currentReportId' => $user->getFirstClient()->getCurrentReport()->getId(),
            'currentReportType' =>$user->getFirstClient()->getCurrentReport()->getType(),
            'currentReportNdrOrReport' => $user->getFirstClient()->getCurrentReport() instanceof Ndr ? 'ndr' : 'report',
            'previousReportId' => $user->getFirstClient()->getReports()[0]->getId(),
            'previousReportType' => $user->getFirstClient()->getReports()[0]->getType(),
            'previousReportNdrOrReport' => $user->getFirstClient()->getCurrentReport() instanceof Ndr ? 'ndr' : 'report'
        ];
    }

    private function buildOrgUserDetails(User $user)
    {
        return [
            'email' => $user->getEmail(),
            'clientId' => $user->getOrganisations()[0]->getClients()[0]->getId(),
            'clientFirstName' => $user->getOrganisations()[0]->getClients()[0]->getFirstname(),
            'clientLastName' => $user->getOrganisations()[0]->getClients()[0]->getLastname(),
            'currentReportId' => $user->getOrganisations()[0]->getClients()[0]->getCurrentReport()->getId(),
            'currentReportType' =>$user->getOrganisations()[0]->getClients()[0]->getCurrentReport()->getType(),
            'currentReportNdrOrReport' => $user->getOrganisations()[0]->getClients()[0]->getCurrentReport() instanceof Ndr ? 'ndr' : 'report',
            'previousReportId' => $user->getOrganisations()[0]->getClients()[0]->getReports()[0]->getId(),
            'previousReportType' => $user->getOrganisations()[0]->getClients()[0]->getReports()[0]->getType(),
            'previousReportNdrOrReport' => $user->getOrganisations()[0]->getClients()[0]->getCurrentReport() instanceof Ndr ? 'ndr' : 'report'
        ];
    }

    private function createUserFixtures()
    {
        $this->createAdminUsers();
        $this->createDeputies();

        $users = [
            $this->admin,
            $this->superAdmin,
            $this->layNotStarted,
            $this->layCompleted,
            $this->laySubmitted,
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
            ->createUser(null, User::ROLE_ADMIN, sprintf('admin-%s@publicguardian.gov.uk', $this->testRunId));

        $this->superAdmin = $this->userTestHelper
            ->createUser(null, User::ROLE_SUPER_ADMIN, sprintf('super-admin-%s@publicguardian.gov.uk', $this->testRunId));
    }

    private function createDeputies()
    {
        $this->createLays();
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

    private function createProfs()
    {
        $orgName = sprintf('prof-%s-%s', $this->orgName, $this->testRunId);
        $emailIdentifier = sprintf('prof-%s-%s', $this->orgEmailIdentifier, $this->testRunId);

        $organisation = $this->organisationTestHelper->createOrganisation($orgName, $emailIdentifier);
        $this->entityManager->persist($organisation);

        $this->profAdminNotStarted = $this->userTestHelper
            ->createUser(null, User::ROLE_PROF_ADMIN, sprintf('prof-admin-not-started-%s@t.uk', $this->testRunId));
        $this->addOrgClientsAndReportsToOrgDeputy($this->profAdminNotStarted, $organisation, false, false);

        $this->profAdminCompleted = $this->userTestHelper
            ->createUser(null, User::ROLE_PROF_ADMIN, sprintf('prof-admin-completed-%s@t.uk', $this->testRunId));
        $this->addOrgClientsAndReportsToOrgDeputy($this->profAdminCompleted, $organisation, true, false);

        $this->profAdminSubmitted = $this->userTestHelper
            ->createUser(null, User::ROLE_PROF_ADMIN, sprintf('prof-admin-submitted-%s@t.uk', $this->testRunId));
        $this->addOrgClientsAndReportsToOrgDeputy($this->profAdminSubmitted, $organisation, true, true);
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

    private function addOrgClientsAndReportsToOrgDeputy(User $deputy, Organisation $organisation, bool $completed = false, bool $submitted = false)
    {
        $client = $this->clientTestHelper->generateClient($this->entityManager, $deputy, $organisation);
        $report = $this->reportTestHelper->generateReport($this->entityManager, $client);

        $client->addReport($report);
        $client->setOrganisation($organisation);

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

        $this->entityManager->persist($deputy);
        $this->entityManager->persist($client);
        $this->entityManager->persist($report);
    }
}
