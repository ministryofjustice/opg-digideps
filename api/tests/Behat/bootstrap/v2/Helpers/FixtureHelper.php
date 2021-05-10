<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Helpers;

use App\Entity\Client;
use App\Entity\Ndr\Ndr;
use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Entity\User;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\NamedDeputyTestHelper;
use App\TestHelpers\OrganisationTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\TestHelpers\UserTestHelper;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class FixtureHelper
{
    private EntityManagerInterface $em;
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

    private User $layPfaHighAssetsNotStarted;
    private User $layPfaHighAssetsCompleted;
    private User $layPfaHighAssetsSubmitted;

    private User $layPfaLowAssetsNotStarted;
    private User $layPfaLowAssetsCompleted;
    private User $layPfaLowAssetsSubmitted;

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
        $this->em = $entityManager;
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
     * @return array
     *
     * @throws Exception
     */
    public function loadFixtures(string $testRunId)
    {
        if ('prod' === $this->symfonyEnvironment) {
            throw new Exception('Prod mode enabled - cannot purge database');
        }

        $purger = new ORMPurger($this->em);
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
                'pfa-high-assets' => [
                    'not-started' => self::buildUserDetails($this->layPfaHighAssetsNotStarted),
                    'completed' => self::buildUserDetails($this->layPfaHighAssetsCompleted),
                    'submitted' => self::buildUserDetails($this->layPfaHighAssetsSubmitted),
                ],
                'pfa-low-assets' => [
                    'not-started' => self::buildUserDetails($this->layPfaLowAssetsNotStarted),
                    'completed' => self::buildUserDetails($this->layPfaLowAssetsCompleted),
                    'submitted' => self::buildUserDetails($this->layPfaLowAssetsSubmitted),
                ],
            ],
            'lays-ndr' => [
                'not-started' => self::buildUserDetails($this->ndrLayNotStarted),
                'completed' => self::buildUserDetails($this->ndrLayCompleted),
                'submitted' => self::buildUserDetails($this->ndrLaySubmitted),
            ],
            'professionals' => [
                'admin' => [
                    'not-started' => self::buildOrgUserDetails($this->profAdminNotStarted),
                    'completed' => self::buildOrgUserDetails($this->profAdminCompleted),
                    'submitted' => self::buildOrgUserDetails($this->profAdminSubmitted),
                ],
            ],
        ];
    }

    public static function buildUserDetails(User $user)
    {
        $client = $user->isLayDeputy() ? $user->getFirstClient() : $user->getOrganisations()[0]->getClients()[0];

        $currentReport = $user->getNdrEnabled() ? $client->getNdr() : $client->getCurrentReport();
        $currentReportType = $user->getNdrEnabled() ? null : $currentReport->getType();
        $previousReport = $user->getNdrEnabled() ? null : $client->getReports()[0];

        $userDetails = [
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
                $user->getAddressCountry(),
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
            'currentReportDueDate' => $currentReport->getDueDate()->format('j F Y'),
        ];

        if ($previousReport && $previousReport->getId() !== $currentReport->getId()) {
            $userDetails = array_merge(
                $userDetails,
                [
                    'previousReportId' => $previousReport->getId(),
                    'previousReportType' => $previousReport->getType(),
                    'previousReportNdrOrReport' => $previousReport instanceof Ndr ? 'ndr' : 'report',
                    'previousReportDueDate' => $previousReport->getDueDate()->format('j F Y'),
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
            $this->layPfaHighAssetsNotStarted,
            $this->layPfaHighAssetsCompleted,
            $this->layPfaHighAssetsSubmitted,
            $this->layPfaLowAssetsNotStarted,
            $this->layPfaLowAssetsCompleted,
            $this->layPfaLowAssetsSubmitted,
            $this->ndrLayNotStarted,
            $this->ndrLayCompleted,
            $this->ndrLaySubmitted,
            $this->profAdminNotStarted,
            $this->profAdminCompleted,
            $this->profAdminSubmitted,
        ];

        foreach ($users as $user) {
            $user->setPassword($this->encoder->encodePassword($user, $this->fixtureParams['account_password']));
            $this->em->persist($user);
        }

        $this->em->flush();
    }

    private function createAdminUsers()
    {
        $this->admin = $this->createUser(User::ROLE_ADMIN);
        $this->elevatedAdmin = $this->createUser(User::ROLE_ELEVATED_ADMIN);
        $this->superAdmin = $this->createUser(User::ROLE_SUPER_ADMIN);
    }

    public function createUser(string $roleName, ?string $email = null)
    {
        if (is_null($email)) {
            $email = sprintf('%s-%s@t.uk', substr($roleName, 5), $this->testRunId);
        }

        return $this->userTestHelper->createUser(null, $roleName, $email);
    }

    public function createAndPersistUser(string $roleName, ?string $email = null)
    {
        $user = $this->createUser($roleName, $email);

        $this->em->persist($user);
        $this->em->flush();
    }

    private function createDeputies()
    {
        $this->createLaysPfaHighAssets();
        $this->createLaysPfaLowAssets();
        $this->createNdrLays();
        $this->createProfs();
    }

    private function createLaysPfaHighAssets()
    {
        $this->layPfaHighAssetsNotStarted = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-pfa-high-assets-not-started-%s@t.uk', $this->testRunId));
        $this->addClientsAndReportsToLayDeputy($this->layPfaHighAssetsNotStarted, false, false, Report::TYPE_102);

        $this->layPfaHighAssetsCompleted = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-pfa-high-assets-completed-%s@t.uk', $this->testRunId));
        $this->addClientsAndReportsToLayDeputy($this->layPfaHighAssetsCompleted, true, false, Report::TYPE_102);

        $this->layPfaHighAssetsSubmitted = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-pfa-high-assets-submitted-%s@t.uk', $this->testRunId));
        $this->addClientsAndReportsToLayDeputy($this->layPfaHighAssetsSubmitted, true, true, Report::TYPE_102);
    }

    private function createLaysPfaLowAssets()
    {
        $this->layPfaLowAssetsNotStarted = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-pfa-low-assets-not-started-%s@t.uk', $this->testRunId));
        $this->addClientsAndReportsToLayDeputy($this->layPfaLowAssetsNotStarted, false, false, Report::TYPE_103);

        $this->layPfaLowAssetsCompleted = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-pfa-low-assets-completed-%s@t.uk', $this->testRunId));
        $this->addClientsAndReportsToLayDeputy($this->layPfaLowAssetsCompleted, true, false, Report::TYPE_103);

        $this->layPfaLowAssetsSubmitted = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-pfa-low-assets-submitted-%s@t.uk', $this->testRunId));
        $this->addClientsAndReportsToLayDeputy($this->layPfaLowAssetsSubmitted, true, true, Report::TYPE_103);
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
        $this->em->persist($organisation);

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

    private function addClientsAndReportsToLayDeputy(User $deputy, bool $completed = false, bool $submitted = false, ?string $type = null)
    {
        $client = $this->clientTestHelper->generateClient($this->em, $deputy);
        $report = $this->reportTestHelper->generateReport($this->em, $client, $type);

        $client->addReport($report);
        $report->setClient($client);
        $deputy->addClient($client);

        if ($completed) {
            $this->reportTestHelper->completeLayReport($report, $this->em);
        }

        if ($submitted) {
            $this->reportTestHelper->submitReport($report, $this->em);
        }

        $this->em->persist($client);
        $this->em->persist($report);
    }

    private function addClientsAndReportsToNdrLayDeputy(User $deputy, bool $completed = false, bool $submitted = false)
    {
        $client = $this->clientTestHelper->generateClient($this->em, $deputy);

        $ndr = new Ndr($client);
        $deputy->setNdrEnabled(true);
        $client->setNdr($ndr);

        $deputy->addClient($client);

        if ($completed) {
            $this->reportTestHelper->completeNdrLayReport($ndr, $this->em);
        }

//        if ($submitted) {
//            placeholder for when submitted version needed...
//        }

        $this->em->persist($ndr);
        $this->em->persist($client);
    }

    private function addOrgClientsNamedDeputyAndReportsToOrgDeputy(User $deputy, Organisation $organisation, bool $completed = false, bool $submitted = false)
    {
        $client = $this->clientTestHelper->generateClient($this->em, $deputy, $organisation);
        $report = $this->reportTestHelper->generateReport($this->em, $client);
        $namedDeputy = $this->namedDeputyTestHelper->generatenamedDeputy();

        $client->addReport($report);
        $client->setOrganisation($organisation);
        $client->setNamedDeputy($namedDeputy);

        $organisation->addClient($client);
        $organisation->addUser($deputy);

        $report->setClient($client);

        $deputy->addOrganisation($organisation);

        if ($completed) {
            $this->reportTestHelper->completeLayReport($report, $this->em);
        }

        if ($submitted) {
            $this->reportTestHelper->submitReport($report, $this->em);
        }

        $this->em->persist($namedDeputy);
        $this->em->persist($deputy);
        $this->em->persist($client);
        $this->em->persist($report);
    }

    public function getLoggedInUserDetails(string $email)
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => strtolower($email)]);

        return self::buildUserDetails($user);
    }

    public function duplicateClient(int $clientId)
    {
        $client = clone $this->em->getRepository(Client::class)->find($clientId);
        $client->setCaseNumber(ClientTestHelper::createValidCaseNumber());

        $this->em->persist($client);
        $this->em->flush();
    }
}
