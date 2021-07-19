<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Helpers;

use App\Entity\Client;
use App\Entity\Ndr\Ndr;
use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Entity\Satisfaction;
use App\Entity\User;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\NamedDeputyTestHelper;
use App\TestHelpers\OrganisationTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\TestHelpers\UserTestHelper;
use DateTime;
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
    private User $adminManager;
    private User $superAdmin;

    private User $layPfaHighAssetsNotStarted;
    private User $layPfaHighAssetsCompleted;
    private User $layPfaHighAssetsSubmitted;

    private User $layPfaLowAssetsNotStarted;
    private User $layPfaLowAssetsCompleted;
    private User $layPfaLowAssetsSubmitted;

    private User $layHealthWelfareNotStarted;
    private User $layHealthWelfareCompleted;
    private User $layHealthWelfareSubmitted;

    private User $profNamedHealthWelfareNotStarted;
    private User $profNamedHealthWelfareCompleted;
    private User $profNamedHealthWelfareSubmitted;

    private User $paNamedHealthWelfareNotStarted;
    private User $paNamedHealthWelfareCompleted;
    private User $paNamedHealthWelfareSubmitted;

    private User $profTeamHealthWelfareNotStarted;
    private User $profTeamHealthWelfareCompleted;
    private User $profTeamHealthWelfareSubmitted;

    private User $layNdrNotStarted;
    private User $layNdrCompleted;
    private User $layNdrSubmitted;

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

    public static function buildUserDetails(User $user)
    {
        $client = $user->isLayDeputy() ? $user->getFirstClient() : $user->getOrganisations()[0]->getClients()[0];

        $currentReport = $user->getNdrEnabled() ? $client->getNdr() : $client->getCurrentReport();
        $currentReportType = $user->getNdrEnabled() ? null : $currentReport->getType();
        $previousReport = $user->getNdrEnabled() ? null : $client->getReports()[0];

        $userDetails = [
            'userId' => $user->getId(),
            'userEmail' => $user->getEmail(),
            'userRole' => $user->getRoleName(),
            'userFirstName' => $user->getFirstname(),
            'userLastName' => $user->getLastname(),
            'userFullName' => $user->getFullName(),
            'userFullAddressArray' => self::buildAddressArray($user),
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
            'userId' => $user->getId(),
            'userEmail' => $user->getEmail(),
            'userRole' => $user->getRoleName(),
            'userFirstName' => $user->getFirstname(),
            'userLastName' => $user->getLastname(),
            'userFullName' => $user->getFullName(),
            'userFullAddressArray' => self::buildAddressArray($user),
        ];
    }

    private static function buildAddressArray(User $user): array
    {
        return array_filter(
            [
                'address1' => $user->getAddress1(),
                'address2' => $user->getAddress2(),
                'address3' => $user->getAddress3(),
                'addressPostcode' => $user->getAddressPostcode(),
                'addressCountry' => $user->getAddressCountry(),
            ],
            function ($value, $key) {
                return !is_null($value);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    private function createUserFixtures()
    {
        $this->createAdminUsers();
        $this->createDeputies();

        $users = [
            $this->admin,
            $this->adminManager,
            $this->superAdmin,
            $this->layHealthWelfareNotStarted,
            $this->layHealthWelfareCompleted,
            $this->layHealthWelfareSubmitted,
            $this->layPfaHighAssetsNotStarted,
            $this->layPfaHighAssetsCompleted,
            $this->layPfaHighAssetsSubmitted,
            $this->layPfaLowAssetsNotStarted,
            $this->layPfaLowAssetsCompleted,
            $this->layPfaLowAssetsSubmitted,
            $this->layNdrNotStarted,
            $this->layNdrCompleted,
            $this->layNdrSubmitted,
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
        $this->adminManager = $this->createUser(User::ROLE_ADMIN_MANAGER);
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

        return $user;
    }

    private function createDeputies()
    {
        $this->createLaysPfaHighAssets();
        $this->createLaysPfaLowAssets();
        $this->createLaysHealthWelfare();
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

    private function createLaysHealthWelfare()
    {
        $this->layHealthWelfareNotStarted = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-health-welfare-not-started-%s@t.uk', $this->testRunId));
        $this->addClientsAndReportsToLayDeputy($this->layHealthWelfareNotStarted, false, false, Report::TYPE_104);

        $this->layHealthWelfareCompleted = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-health-welfare-completed-%s@t.uk', $this->testRunId));
        $this->addClientsAndReportsToLayDeputy($this->layHealthWelfareCompleted, true, false, Report::TYPE_104);

        $this->layHealthWelfareSubmitted = $this->userTestHelper
            ->createUser(null, User::ROLE_LAY_DEPUTY, sprintf('lay-health-welfare-submitted-%s@t.uk', $this->testRunId));
        $this->addClientsAndReportsToLayDeputy($this->layHealthWelfareSubmitted, true, true, Report::TYPE_104);
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

    private function addClientsAndReportsToLayDeputy(User $deputy, bool $completed = false, bool $submitted = false,
                                                     ?string $type = null, ?DateTime $startDate = null, int $satisfactionScore = null)
    {
        $client = $this->clientTestHelper->generateClient($this->em, $deputy);
        $report = $this->reportTestHelper->generateReport($this->em, $client, $type, $startDate);

        $client->addReport($report);
        $report->setClient($client);
        $deputy->addClient($client);
        $deputy->setRegistrationDate($startDate);

        if ($completed) {
            $this->reportTestHelper->completeLayReport($report, $this->em);
        }

        if ($submitted) {
            $this->reportTestHelper->submitReport($report, $this->em);
        }

        $this->em->persist($client);
        $this->em->persist($report);

        if ($submitted and isset($satisfactionScore)) {
            $satisfaction = $this->setSatisfaction($report, $deputy, $satisfactionScore);
            $this->em->persist($satisfaction);
        }
    }

    private function setSatisfaction(Report $report, User $deputy, int $satisfactionScore)
    {
        $submitDate = clone $report->getStartDate();
        $submitDate->modify('+365 day');
        $satisfaction = new Satisfaction();
        $satisfaction->setScore($satisfactionScore);
        $satisfaction->setComments('random comment');
        $satisfaction->setReport($report);
        $satisfaction->setReporttype($report->getType());
        $satisfaction->setDeputyrole($deputy->getRoleName());
        $satisfaction->setCreated($submitDate);

        return $satisfaction;
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

    private function addOrgClientsNamedDeputyAndReportsToOrgDeputy(
        User $deputy, Organisation $organisation, bool $completed = false, bool $submitted = false,
        $reportType = Report::TYPE_102_5, ?DateTime $startDate = null, int $satisfactionScore = null
    ) {
        $client = $this->clientTestHelper->generateClient($this->em, $deputy, $organisation);
        $report = $this->reportTestHelper->generateReport($this->em, $client, $reportType, $startDate);
        $namedDeputy = $this->namedDeputyTestHelper->generatenamedDeputy();

        $client->addReport($report);
        $client->setOrganisation($organisation);
        $client->setNamedDeputy($namedDeputy);

        $organisation->addClient($client);
        $organisation->addUser($deputy);

        $report->setClient($client);

        $deputy->addOrganisation($organisation);
        $deputy->setRegistrationDate($startDate);

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

        if ($submitted and isset($satisfactionScore)) {
            $satisfaction = $this->setSatisfaction($report, $deputy, $satisfactionScore);
            $this->em->persist($satisfaction);
        }
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

    public function createLayPfaHighAssetsNotStarted(string $testRunId)
    {
        $this->layPfaHighAssetsNotStarted = $this->createClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-pfa-high-assets-not-started',
            Report::TYPE_102,
            false,
            false
        );

        return self::buildUserDetails($this->layPfaHighAssetsNotStarted);
    }

    public function createLayPfaHighAssetsCompleted(string $testRunId)
    {
        $this->layPfaHighAssetsCompleted = $this->createClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-pfa-high-assets-completed',
            Report::TYPE_102,
            true,
            false
        );

        return self::buildUserDetails($this->layPfaHighAssetsCompleted);
    }

    public function createLayPfaHighAssetsSubmitted(string $testRunId)
    {
        $this->layPfaHighAssetsSubmitted = $this->createClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-pfa-high-assets-submitted',
            Report::TYPE_102,
            true,
            true
        );

        return self::buildUserDetails($this->layPfaHighAssetsSubmitted);
    }

    public function createLayPfaLowAssetsNotStarted(string $testRunId)
    {
        $this->layPfaLowAssetsNotStarted = $this->createClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-pfa-low-assets-not-started',
            Report::TYPE_103,
            false,
            false
        );

        return self::buildUserDetails($this->layPfaLowAssetsNotStarted);
    }

    public function createLayPfaLowAssetsCompleted(string $testRunId)
    {
        $this->layPfaLowAssetsCompleted = $this->createClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-pfa-low-assets-completed',
            Report::TYPE_103,
            true,
            false
        );

        return self::buildUserDetails($this->layPfaLowAssetsCompleted);
    }

    public function createLayPfaLowAssetsSubmitted(string $testRunId)
    {
        $this->layPfaLowAssetsSubmitted = $this->createClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-pfa-low-assets-submitted',
            Report::TYPE_103,
            true,
            true
        );

        return self::buildUserDetails($this->layPfaLowAssetsSubmitted);
    }

    public function createLayHealthWelfareNotStarted(string $testRunId)
    {
        $this->layHealthWelfareNotStarted = $this->createClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-health-welfare-not-started',
            Report::TYPE_104,
            false,
            false
        );

        return self::buildUserDetails($this->layHealthWelfareNotStarted);
    }

    public function createLayHealthWelfareCompleted(string $testRunId)
    {
        $this->layHealthWelfareCompleted = $this->createClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-health-welfare-completed',
            Report::TYPE_104,
            true,
            false
        );

        return self::buildUserDetails($this->layHealthWelfareCompleted);
    }

    public function createLayHealthWelfareSubmitted(string $testRunId)
    {
        $this->layHealthWelfareSubmitted = $this->createClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-health-welfare-submitted',
            Report::TYPE_104,
            true,
            true
        );

        return self::buildUserDetails($this->layHealthWelfareSubmitted);
    }

    public function createProfNamedHealthWelfareNotStarted(string $testRunId)
    {
        $this->profNamedHealthWelfareNotStarted = $this->createOrgUserClientNamedDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_NAMED,
            'prof-named-health-welfare-not-started',
            Report::TYPE_104_5,
            false,
            false
        );

        return self::buildOrgUserDetails($this->profNamedHealthWelfareNotStarted);
    }

    public function createProfNamedHealthWelfareCompleted(string $testRunId)
    {
        $this->profNamedHealthWelfareCompleted = $this->createOrgUserClientNamedDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_NAMED,
            'prof-named-health-welfare-completed',
            Report::TYPE_104_5,
            true,
            false
        );

        return self::buildOrgUserDetails($this->profNamedHealthWelfareCompleted);
    }

    public function createProfNamedHealthWelfareSubmitted(string $testRunId)
    {
        $this->profNamedHealthWelfareSubmitted = $this->createOrgUserClientNamedDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_NAMED,
            'prof-named-health-welfare-submitted',
            Report::TYPE_104_5,
            true,
            true
        );

        return self::buildOrgUserDetails($this->profNamedHealthWelfareSubmitted);
    }

    public function createPaNamedHealthWelfareNotStarted(string $testRunId)
    {
        $this->paNamedHealthWelfareNotStarted = $this->createOrgUserClientNamedDeputyAndReport(
            $testRunId,
            User::ROLE_PA_NAMED,
            'pa-named-health-welfare-not-started',
            Report::TYPE_104_6,
            false,
            false
        );

        return self::buildOrgUserDetails($this->paNamedHealthWelfareNotStarted);
    }

    public function createPaNamedHealthWelfareCompleted(string $testRunId)
    {
        $this->paNamedHealthWelfareCompleted = $this->createOrgUserClientNamedDeputyAndReport(
            $testRunId,
            User::ROLE_PA_NAMED,
            'pa-named-health-welfare-completed',
            Report::TYPE_104_6,
            true,
            false
        );

        return self::buildOrgUserDetails($this->paNamedHealthWelfareCompleted);
    }

    public function createPaNamedHealthWelfareSubmitted(string $testRunId)
    {
        $this->paNamedHealthWelfareSubmitted = $this->createOrgUserClientNamedDeputyAndReport(
            $testRunId,
            User::ROLE_PA_NAMED,
            'pa-named-health-welfare-submitted',
            Report::TYPE_104_6,
            true,
            true
        );

        return self::buildOrgUserDetails($this->paNamedHealthWelfareSubmitted);
    }

    public function createProfTeamHealthWelfareNotStarted(string $testRunId)
    {
        $this->profTeamHealthWelfareNotStarted = $this->createOrgUserClientNamedDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_TEAM_MEMBER,
            'prof-team-health-welfare-not-started',
            Report::TYPE_104_5,
            false,
            false
        );

        return self::buildOrgUserDetails($this->profTeamHealthWelfareNotStarted);
    }

    public function createProfTeamHealthWelfareCompleted(string $testRunId)
    {
        $this->profTeamHealthWelfareCompleted = $this->createOrgUserClientNamedDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_TEAM_MEMBER,
            'prof-team-health-welfare-completed',
            Report::TYPE_104_5,
            true,
            false
        );

        return self::buildOrgUserDetails($this->profTeamHealthWelfareCompleted);
    }

    public function createProfTeamHealthWelfareSubmitted(string $testRunId)
    {
        $this->profTeamHealthWelfareSubmitted = $this->createOrgUserClientNamedDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_TEAM_MEMBER,
            'prof-team-health-welfare-submitted',
            Report::TYPE_104_5,
            true,
            true
        );

        return self::buildOrgUserDetails($this->profTeamHealthWelfareSubmitted);
    }

    public function createLayNdrNotStarted(string $testRunId)
    {
        $this->layNdrNotStarted = $this->createClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-ndr-not-started',
            Report::TYPE_104,
            false,
            false,
            true
        );

        return self::buildUserDetails($this->layNdrNotStarted);
    }

    public function createLayNdrCompleted(string $testRunId)
    {
        $this->layNdrCompleted = $this->createClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-ndr-completed',
            Report::TYPE_104,
            true,
            false,
            true
        );

        return self::buildUserDetails($this->layNdrCompleted);
    }

    public function createLayNdrSubmitted(string $testRunId)
    {
        $this->layNdrSubmitted = $this->createClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-ndr-submitted',
            Report::TYPE_104,
            true,
            false,
            true
        );

        return self::buildUserDetails($this->layNdrSubmitted);
    }

    public function createProfAdminNotStarted(string $testRunId)
    {
        $this->profAdminNotStarted = $this->createOrgUserClientNamedDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_ADMIN,
            'prof-admin-not-started',
            Report::TYPE_104_5,
            false,
            false
        );

        return self::buildOrgUserDetails($this->profAdminNotStarted);
    }

    public function createProfAdminCompleted(string $testRunId)
    {
        $this->profAdminCompleted = $this->createOrgUserClientNamedDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_ADMIN,
            'prof-admin-completed',
            Report::TYPE_104_5,
            true,
            false
        );

        return self::buildOrgUserDetails($this->profAdminCompleted);
    }

    public function createProfAdminSubmitted(string $testRunId)
    {
        $this->profAdminSubmitted = $this->createOrgUserClientNamedDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_ADMIN,
            'prof-admin-completed',
            Report::TYPE_104_5,
            true,
            true
        );

        return self::buildOrgUserDetails($this->profAdminSubmitted);
    }

    public function createAdmin(string $testRunId)
    {
        $this->admin = $this->createAdminUser(
            $testRunId,
            User::ROLE_ADMIN,
            'admin'
        );

        return self::buildAdminUserDetails($this->admin);
    }

    public function createAdminManager(string $testRunId)
    {
        $this->adminManager = $this->createAdminUser(
            $testRunId,
            User::ROLE_ADMIN_MANAGER,
            'admin-manager'
        );

        return self::buildAdminUserDetails($this->adminManager);
    }

    public function createSuperAdmin(string $testRunId)
    {
        $this->superAdmin = $this->createAdminUser(
            $testRunId,
            User::ROLE_SUPER_ADMIN,
            'super-admin'
        );

        return self::buildAdminUserDetails($this->superAdmin);
    }

    public function createDataForAnalytics(string $testRunId, $timeAgo, $satisfactionScore)
    {
        $startDate = new \DateTime($timeAgo);

        $this->createOrgUserClientNamedDeputyAndReport(
            $testRunId.'_1',
            User::ROLE_PROF_NAMED,
            'analytics-prof-submitted',
            Report::TYPE_104_6,
            true,
            true,
            $startDate,
            $satisfactionScore
        );

        $this->createOrgUserClientNamedDeputyAndReport(
            $testRunId.'_2',
            User::ROLE_PA_NAMED,
            'analytics-pa-submitted',
            Report::TYPE_104_6,
            true,
            true,
            $startDate,
            $satisfactionScore
        );

        $this->createClientAndReport(
            $testRunId.'_3',
            User::ROLE_LAY_DEPUTY,
            'analytics-lay-submitted',
            Report::TYPE_104,
            true,
            true,
            false,
            $startDate,
            $satisfactionScore
        );
    }

    private function createOrganisation($testRunId)
    {
        $orgName = sprintf('prof-%s-%s', $this->orgName, $testRunId);
        $emailIdentifier = sprintf('prof-%s-%s', $this->orgEmailIdentifier, $this->testRunId);

        $organisation = $this->organisationTestHelper->createOrganisation($orgName, $emailIdentifier);
        $this->em->persist($organisation);

        return $organisation;
    }

    private function createClientAndReport(string $testRunId, $userRole, $emailPrefix, $reportType, $completed, $submitted,
                                           bool $ndr = false, ?DateTime $startDate = null, int $satisfactionScore = null)
    {
        if ('prod' === $this->symfonyEnvironment) {
            throw new Exception('Prod mode enabled - cannot create fixture users');
        }
        $this->testRunId = $testRunId;

        $client = $this->userTestHelper
            ->createUser(null, $userRole, sprintf('%s-%s@t.uk', $emailPrefix, $this->testRunId));

        if ($ndr) {
            $this->addClientsAndReportsToNdrLayDeputy($client, $completed, $submitted);
        } else {
            $this->addClientsAndReportsToLayDeputy($client, $completed, $submitted, $reportType, $startDate, $satisfactionScore);
        }

        $this->setClientPassword($client);

        return $client;
    }

    private function createAdminUser(string $testRunId, $userRole, $emailPrefix)
    {
        if ('prod' === $this->symfonyEnvironment) {
            throw new Exception('Prod mode enabled - cannot create fixture users');
        }
        $this->testRunId = $testRunId;

        $client = $this->userTestHelper
            ->createUser(null, $userRole, sprintf('%s-%s@t.uk', $emailPrefix, $this->testRunId));

        $this->setClientPassword($client);

        return $client;
    }

    private function createOrgUserClientNamedDeputyAndReport(
        string $testRunId, $userRole, $emailPrefix, $reportType, $completed,
        $submitted, ?DateTime $startDate = null, int $satisfactionScore = null
    ) {
        if ('prod' === $this->symfonyEnvironment) {
            throw new Exception('Prod mode enabled - cannot create fixture users');
        }
        $this->testRunId = $testRunId;
        $organisation = $this->createOrganisation($this->testRunId);

        $user = $this->userTestHelper
            ->createUser(null, $userRole, sprintf('%s-%s@t.uk', $emailPrefix, $this->testRunId));
        $this->addOrgClientsNamedDeputyAndReportsToOrgDeputy(
            $user, $organisation, $completed, $submitted, $reportType, $startDate, $satisfactionScore
        );

        $this->setClientPassword($user);

        return $user;
    }

    private function setClientPassword($client)
    {
        $client->setPassword($this->encoder->encodePassword($client, $this->fixtureParams['account_password']));
        $this->em->persist($client);
        $this->em->flush();
    }
}
