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

    private array $users = [];
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
            'admin' => $this->users['admin']->getEmail(),
            'super-admin' => $this->users['super-admin']->getEmail(),
            'lay' => $this->users['lay']->getEmail(),
        ];
    }

    private function createUserFixtures()
    {
        $this->createAdminUsers();
        $this->createDeputies();

        foreach ($this->users as $user) {
            $user->setPassword($this->encoder->encodePassword($user, $this->fixtureParams['account_password']));
            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();
    }

    private function createAdminUsers()
    {
        $this->users['admin'] = $this->userTestHelper
            ->createUser(null, User::ROLE_ADMIN, sprintf('admin-%s@publicguardian.gov.uk', $this->testRunId));

        $this->users['super-admin'] = $this->userTestHelper
            ->createUser(null, User::ROLE_SUPER_ADMIN, sprintf('super-admin-%s@publicguardian.gov.uk', $this->testRunId));
    }

    private function createDeputies()
    {
        $this->users['lay'] = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-%s@publicguardian.gov.uk', $this->testRunId));

        $client = $this->clientTestHelper->createClient($this->entityManager, $this->users['lay']);
        $report = $this->reportTestHelper->generateReport($this->entityManager, $client);

        $this->entityManager->persist($this->users['lay']);
        $this->entityManager->persist($client);
        $this->entityManager->persist($report);

        $this->users['lay']->addClient($client);
    }
}
