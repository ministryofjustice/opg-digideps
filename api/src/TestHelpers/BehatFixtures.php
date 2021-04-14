<?php declare(strict_types=1);


namespace App\TestHelpers;

use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Entity\Ndr\Ndr;
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
                'not-started' => [
                    'email' => $this->layNotStarted->getEmail(),
                    'clientId' => $this->layNotStarted->getFirstClient()->getId(),
                    'currentReportId' => $this->layNotStarted->getFirstClient()->getCurrentReport()->getId(),
                    'currentReportType' => $this->layNotStarted->getFirstClient()->getCurrentReport()->getType(),
                    'currentReportNdrOrReport' => $this->layNotStarted->getFirstClient()->getCurrentReport() instanceof Ndr ? 'ndr' : 'report',
                    'previousReportId' => null,
                    'previousReportType' => null,
                    'previousReportNdrOrReport' => $this->layNotStarted->getFirstClient()->getCurrentReport() instanceof Ndr ? 'ndr' : 'report'
                ],
                'completed' => [
                    'email' => $this->layCompleted->getEmail(),
                    'clientId' => $this->layCompleted->getFirstClient()->getId(),
                    'currentReportId' => $this->layCompleted->getFirstClient()->getCurrentReport()->getId(),
                    'currentReportType' =>$this->layCompleted->getFirstClient()->getCurrentReport()->getType(),
                    'currentReportNdrOrReport' => $this->layCompleted->getFirstClient()->getCurrentReport() instanceof Ndr ? 'ndr' : 'report',
                    'previousReportId' => null,
                    'previousReportType' => null,
                    'previousReportNdrOrReport' => $this->layCompleted->getFirstClient()->getCurrentReport() instanceof Ndr ? 'ndr' : 'report'
                ],
                'submitted' => [
                    'email' => $this->laySubmitted->getEmail(),
                    'clientId' => $this->laySubmitted->getFirstClient()->getId(),
                    'currentReportId' => $this->laySubmitted->getFirstClient()->getCurrentReport()->getId(),
                    'currentReportType' =>$this->laySubmitted->getFirstClient()->getCurrentReport()->getType(),
                    'currentReportNdrOrReport' => $this->laySubmitted->getFirstClient()->getCurrentReport() instanceof Ndr ? 'ndr' : 'report',
                    'previousReportId' => $this->laySubmitted->getFirstClient()->getReports()[0]->getId(),
                    'previousReportType' => $this->laySubmitted->getFirstClient()->getReports()[0]->getType(),
                    'previousReportNdrOrReport' => $this->laySubmitted->getFirstClient()->getCurrentReport() instanceof Ndr ? 'ndr' : 'report'
                ]
            ],
            'lays-ndr' => [
                'not-started' => [
                    'email' => $this->ndrLayNotStarted->getEmail(),
                    'clientId' => $this->ndrLayNotStarted->getFirstClient()->getId(),
                    'currentReportId' => $this->ndrLayNotStarted->getFirstClient()->getNdr()->getId(),
                    'currentReportNdrOrReport' => 'ndr'
                ],
                'completed' => [
                    'email' => $this->ndrLayCompleted->getEmail(),
                    'clientId' => $this->ndrLayCompleted->getFirstClient()->getId(),
                    'currentReportId' => $this->ndrLayCompleted->getFirstClient()->getNdr()->getId(),
                    'currentReportNdrOrReport' => 'ndr'
                ],
                'submitted' => [
                    'email' => $this->ndrLaySubmitted->getEmail(),
                    'clientId' => $this->ndrLaySubmitted->getFirstClient()->getId(),
                    'currentReportId' => $this->ndrLaySubmitted->getFirstClient()->getNdr()->getId(),
                    'currentReportNdrOrReport' => 'ndr'
                ]
            ],
            'professionals' => [
                'admin' => [
                    'not-started' => [
                        'email' => $this->profAdminNotStarted->getEmail(),
                        'clientId' => $this->profAdminNotStarted->getOrganisations()[0]->getClients()[0]->getId(),
                        'currentReportId' => $this->profAdminNotStarted->getOrganisations()[0]->getClients()[0]->getCurrentReport()->getId(),
                        'currentReportType' => $this->profAdminNotStarted->getOrganisations()[0]->getClients()[0]->getCurrentReport()->getType(),
                        'currentReportNdrOrReport' => $this->profAdminNotStarted->getOrganisations()[0]->getClients()[0]->getCurrentReport() instanceof Ndr ? 'ndr' : 'report',
                        'previousReportId' => null,
                        'previousReportType' => null,
                        'previousReportNdrOrReport' => $this->profAdminNotStarted->getOrganisations()[0]->getClients()[0]->getCurrentReport() instanceof Ndr ? 'ndr' : 'report'
                    ],
                    'completed' => [
                        'email' => $this->profAdminCompleted->getEmail(),
                        'clientId' => $this->profAdminCompleted->getOrganisations()[0]->getClients()[0]->getId(),
                        'currentReportId' => $this->profAdminCompleted->getOrganisations()[0]->getClients()[0]->getCurrentReport()->getId(),
                        'currentReportType' =>$this->profAdminCompleted->getOrganisations()[0]->getClients()[0]->getCurrentReport()->getType(),
                        'currentReportNdrOrReport' => $this->profAdminCompleted->getOrganisations()[0]->getClients()[0]->getCurrentReport() instanceof Ndr ? 'ndr' : 'report',
                        'previousReportId' => null,
                        'previousReportType' => null,
                        'previousReportNdrOrReport' => $this->profAdminCompleted->getOrganisations()[0]->getClients()[0]->getCurrentReport() instanceof Ndr ? 'ndr' : 'report'
                    ],
                    'submitted' => [
                        'email' => $this->profAdminSubmitted->getEmail(),
                        'clientId' => $this->profAdminSubmitted->getOrganisations()[0]->getClients()[0]->getId(),
                        'currentReportId' => $this->profAdminSubmitted->getOrganisations()[0]->getClients()[0]->getCurrentReport()->getId(),
                        'currentReportType' =>$this->profAdminSubmitted->getOrganisations()[0]->getClients()[0]->getCurrentReport()->getType(),
                        'currentReportNdrOrReport' => $this->profAdminSubmitted->getOrganisations()[0]->getClients()[0]->getCurrentReport() instanceof Ndr ? 'ndr' : 'report',
                        'previousReportId' => $this->profAdminSubmitted->getOrganisations()[0]->getClients()[0]->getReports()[0]->getId(),
                        'previousReportType' => $this->profAdminSubmitted->getOrganisations()[0]->getClients()[0]->getReports()[0]->getType(),
                        'previousReportNdrOrReport' => $this->profAdminSubmitted->getOrganisations()[0]->getClients()[0]->getCurrentReport() instanceof Ndr ? 'ndr' : 'report'
                    ]
                ]
            ]
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
            ->createUser(null, User::ROLE_ADMIN, sprintf('admin-%s@publicguardian.gov.uk', $this->testRunId));

        $this->superAdmin = $this->userTestHelper
            ->createUser(null, User::ROLE_SUPER_ADMIN, sprintf('super-admin-%s@publicguardian.gov.uk', $this->testRunId));
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
