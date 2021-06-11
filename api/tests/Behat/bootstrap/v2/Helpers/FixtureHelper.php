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

    private User $layHealthWelfareNotStarted;
    private User $layHealthWelfareCompleted;
    private User $layHealthWelfareSubmitted;

    private User $profNamedHealthWelfareNotStarted;
    private User $profNamedHealthWelfareCompleted;
    private User $profNamedHealthWelfareSubmitted;

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

    private function addOrgClientsNamedDeputyAndReportsToOrgDeputy(User $deputy, Organisation $organisation, bool $completed = false, bool $submitted = false, $reportType = Report::TYPE_102)
    {
        $client = $this->clientTestHelper->generateClient($this->em, $deputy, $organisation);
        $report = $this->reportTestHelper->generateReport($this->em, $client, $reportType);
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

    public function createElevatedAdmin(string $testRunId)
    {
        $this->elevatedAdmin = $this->createAdminUser(
            $testRunId,
            User::ROLE_ELEVATED_ADMIN,
            'elevated-admin'
        );

        return self::buildAdminUserDetails($this->elevatedAdmin);
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

    private function createOrganisation($testRunId)
    {
        $orgName = sprintf('prof-%s-%s', $this->orgName, $testRunId);
        $emailIdentifier = sprintf('prof-%s-%s', $this->orgEmailIdentifier, $this->testRunId);

        $organisation = $this->organisationTestHelper->createOrganisation($orgName, $emailIdentifier);
        $this->em->persist($organisation);

        return $organisation;
    }

    private function createClientAndReport(string $testRunId, $userRole, $emailPrefix, $reportType, $completed, $submitted, bool $ndr = false)
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
            $this->addClientsAndReportsToLayDeputy($client, $completed, $submitted, $reportType);
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

    private function createOrgUserClientNamedDeputyAndReport(string $testRunId, $userRole, $emailPrefix, $reportType, $completed, $submitted)
    {
        if ('prod' === $this->symfonyEnvironment) {
            throw new Exception('Prod mode enabled - cannot create fixture users');
        }
        $this->testRunId = $testRunId;
        $organisation = $this->createOrganisation($this->testRunId);

        $user = $this->userTestHelper
            ->createUser(null, $userRole, sprintf('%s-%s@t.uk', $emailPrefix, $this->testRunId));
        $this->addOrgClientsNamedDeputyAndReportsToOrgDeputy($user, $organisation, $completed, $submitted, $reportType);

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
