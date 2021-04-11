<?php declare(strict_types=1);


namespace App\TestHelpers;

use App\Entity\User;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

// Not extending AbstractDataFixture so we can use this in test runs rather commands
class BehatFixtures
{
    private EntityManagerInterface $entityManager;
    private array $fixtureParams;
    private UserPasswordEncoderInterface $encoder;
    private string $symfonyEnvironment;
    private UserTestHelper $userTestHelper;
    private ReportTestHelper $reportTestHelper;
    private ClientTestHelper $clientTestHelper;

    private User $admin;
    private User $superAdmin;
    private User $layNotStarted;
    private User $layCompletedNotSubmitted;
    private User $laySubmitted;

    private string $testRunId = '';

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
                'completed-not-submitted' => [
                    'email' => $this->layCompletedNotSubmitted->getEmail(),
                    'clientId' => $this->layCompletedNotSubmitted->getFirstClient()->getId(),
                    'currentReportId' => $this->layCompletedNotSubmitted->getFirstClient()->getCurrentReport()->getId(),
                    'currentReportType' =>$this->layCompletedNotSubmitted->getFirstClient()->getCurrentReport()->getType(),
                    'currentReportNdrOrReport' => $this->layCompletedNotSubmitted->getFirstClient()->getCurrentReport() instanceof Ndr ? 'ndr' : 'report',
                    'previousReportId' => null,
                    'previousReportType' => null,
                    'previousReportNdrOrReport' => $this->layCompletedNotSubmitted->getFirstClient()->getCurrentReport() instanceof Ndr ? 'ndr' : 'report'
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
            $this->layCompletedNotSubmitted,
            $this->laySubmitted
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
        $this->layNotStarted = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-not-started-%s@publicguardian.gov.uk', $this->testRunId));
        $this->addClientsAndReportsToDeputy($this->layNotStarted, false, false);

        $this->layCompletedNotSubmitted = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-completed-not-submitted-%s@publicguardian.gov.uk', $this->testRunId));
        $this->addClientsAndReportsToDeputy($this->layCompletedNotSubmitted, true, false);

        $this->laySubmitted = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-submitted-%s@publicguardian.gov.uk', $this->testRunId));
        $this->addClientsAndReportsToDeputy($this->laySubmitted, true, true);
    }

    private function addClientsAndReportsToDeputy(User $deputy, bool $completed = false, bool $submitted = false)
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
}
