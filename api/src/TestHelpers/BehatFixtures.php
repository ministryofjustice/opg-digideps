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

    private array $users = ['admin-users' => [], 'deputies' => []];
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

        $test = ''
;
        return [
            'admin' => $this->users['admin-users']['admin']->getEmail(),
            'super-admin' => $this->users['admin-users']['super-admin']->getEmail(),
            'lay-not-started' => $this->users['deputies']['lay-not-started']->getEmail(),
            'lay-completed-not-submitted' => $this->users['deputies']['lay-not-started']->getEmail(),
            'lay-submitted' => $this->users['deputies']['lay-not-started']->getEmail(),
        ];
    }

    private function createUserFixtures()
    {
        $this->createAdminUsers();
        $this->createDeputies();

        foreach (array_merge($this->users['admin-users'], $this->users['deputies']) as $user) {
            $user->setPassword($this->encoder->encodePassword($user, $this->fixtureParams['account_password']));
            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();
    }

    private function createAdminUsers()
    {
        $this->users['admin-users']['admin'] = $this->userTestHelper
            ->createUser(null, User::ROLE_ADMIN, sprintf('admin-%s@publicguardian.gov.uk', $this->testRunId));

        $this->users['admin-users']['super-admin'] = $this->userTestHelper
            ->createUser(null, User::ROLE_SUPER_ADMIN, sprintf('super-admin-%s@publicguardian.gov.uk', $this->testRunId));
    }

    private function createDeputies()
    {
        $this->users['deputies']['lay-not-started'] = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-not-started-%s@publicguardian.gov.uk', $this->testRunId));
        $this->addClientsAndReportsToDeputy($this->users['deputies']['lay-not-started'], false, false);

        $this->users['deputies']['lay-completed-not-submitted'] = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-completed-not-submitted-%s@publicguardian.gov.uk', $this->testRunId));
        $this->addClientsAndReportsToDeputy($this->users['deputies']['lay-completed-not-submitted'], true, false);

        $this->users['deputies']['lay-submitted'] = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-submitted-%s@publicguardian.gov.uk', $this->testRunId));
        $this->addClientsAndReportsToDeputy($this->users['deputies']['lay-submitted'], true, true);
    }

    private function addClientsAndReportsToDeputy(User $deputy, bool $completed = false, bool $submitted = false)
    {
        $client = $this->clientTestHelper->createClient($this->entityManager, $deputy);
        $report = $this->reportTestHelper->generateReport($this->entityManager, $client);

        if ($completed) {
            $this->reportTestHelper->completeLayReport($report, $this->entityManager);
        }

        if ($submitted) {
            $this->reportTestHelper->submitReport($report, $this->entityManager);
        }

        $this->entityManager->persist($client);
        $this->entityManager->persist($report);

        $deputy->addClient($client);
    }
}
