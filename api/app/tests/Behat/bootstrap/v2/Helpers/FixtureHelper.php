<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\Helpers;

use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderType;
use OPG\Digideps\Backend\Domain\Report\ReportType;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Entity\PreRegistration;
use OPG\Digideps\Backend\Entity\Report\Expense;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\Satisfaction;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\FixtureFactory\PreRegistrationFactory;
use OPG\Digideps\Backend\TestHelpers\ClientTestHelper;
use OPG\Digideps\Backend\TestHelpers\CourtOrderTestHelper;
use OPG\Digideps\Backend\TestHelpers\DeputyTestHelper;
use OPG\Digideps\Backend\TestHelpers\OrganisationTestHelper;
use OPG\Digideps\Backend\TestHelpers\ReportTestHelper;
use OPG\Digideps\Backend\TestHelpers\UserTestHelper;
use Tests\OPG\Digideps\Backend\Behat\BehatException;
use Aws\S3\S3ClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class FixtureHelper
{
    private UserTestHelper $userTestHelper;
    private ReportTestHelper $reportTestHelper;
    private ClientTestHelper $clientTestHelper;
    private OrganisationTestHelper $organisationTestHelper;
    private DeputyTestHelper $deputyTestHelper;
    private CourtOrderTestHelper $courtOrderTestHelper;

    private string $testRunId = '';
    private string $orgName = 'Test Org';
    private string $orgEmailIdentifier = 'test-org.uk';

    public function __construct(
        private EntityManagerInterface $em,
        private array $fixtureParams,
        private UserPasswordHasherInterface $hasher,
        private PreRegistrationFactory $preRegistrationFactory,
        private S3ClientInterface $s3Client,
        private readonly bool $fixturesEnabled,
    ) {
        $this->userTestHelper = UserTestHelper::create();
        $this->reportTestHelper = ReportTestHelper::create();
        $this->clientTestHelper = ClientTestHelper::create();
        $this->organisationTestHelper = new OrganisationTestHelper();
        $this->deputyTestHelper = new DeputyTestHelper();
        $this->courtOrderTestHelper = new CourtOrderTestHelper();
    }

    public function createUser(
        string $roleName,
        ?string $email = null,
        ?int $deputyUid = null,
        ?string $firstName = null,
        ?string $lastName = null,
    ): User {
        if (is_null($email)) {
            $email = sprintf('%s-%s@t.uk', substr($roleName, 5), $this->testRunId);
        }

        return $this->userTestHelper->createUser(null, $roleName, $email, true, $deputyUid, $firstName, $lastName);
    }

    public function createAndPersistUser(
        string $roleName,
        ?string $email = null,
        ?int $deputyUid = null,
        ?string $firstName = null,
        ?string $lastName = null,
    ): User {
        if (!$this->fixturesEnabled) {
            throw new BehatException('Prod mode enabled - cannot create fixture users');
        }

        $user = $this->createUser($roleName, $email, $deputyUid, $firstName, $lastName);
        $this->setPassword($user);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function generateClient(?User $user = null, ?Organisation $org = null, ?string $caseNumber = null): Client
    {
        return $this->clientTestHelper->generateClient($this->em, $user, $org, $caseNumber);
    }

    // also associates Deputy with the provided User
    private function getOrAddDeputy(User $user): void
    {
        $deputyObject = $this->em->getRepository(Deputy::class)->findOneBy(['deputyUid' => $user->getDeputyUid()]);

        if (is_null($deputyObject)) {
            $deputyObject = $this->deputyTestHelper->generateDeputy($user->getEmail(), strval($user->getDeputyUid()), $user, em: $this->em);
        }

        $deputyObject->setUser($user);
        $this->em->persist($deputyObject);
        $this->em->flush();
    }

    private function addClientsAndReportsToLayDeputy(
        User $user,
        bool $completed = false,
        bool $submitted = false,
        ?string $type = null,
        ?\DateTime $startDate = null,
        ?int $satisfactionScore = null,
        ?string $caseNumber = null,
    ): void {
        $client = $this->clientTestHelper->generateClient($this->em, $user, null, $caseNumber);
        $report = $this->reportTestHelper->generateReport($this->em, $client, $type, $startDate);

        $this->getOrAddDeputy($user);

        $client->addReport($report);
        $report->setClient($client);
        $user->addClient($client);
        $user->setRegistrationDate($startDate);

        if ($completed) {
            $this->reportTestHelper->completeReport($report, $this->em);
        }

        if ($submitted) {
            $this->storeFileInS3(getenv(FixtureHelperBuilder::S3_BUCKETNAME), 'dd_doc_1234_9876543219876');
            $this->storeFileInS3(getenv(FixtureHelperBuilder::S3_BUCKETNAME), 'dd_doc_1234_123456789123456');
            $this->reportTestHelper->submitReport($report, $this->em);
        }

        $this->em->persist($client);
        $this->em->persist($report);

        if ($submitted and isset($satisfactionScore)) {
            $satisfaction = $this->setSatisfaction($report, $user, $satisfactionScore);
            $this->em->persist($satisfaction);
        }

        $this->em->flush();
    }

    private function storeFileInS3(string $bucketName, string $key): void
    {
        $filePath = sprintf('%s/fixtures/%s', dirname(__DIR__, 3), 'good.pdf');
        $fileBody = file_get_contents($filePath);

        $this->s3Client->putObject([
            'Bucket' => $bucketName,
            'Key' => $key,
            'Body' => $fileBody,
            'ServerSideEncryption' => 'AES256',
            'Metadata' => [],
        ]);
    }

    public function deleteFilesFromS3(string $storageReference): void
    {
        $this->s3Client->deleteMatchingObjects(getenv(FixtureHelperBuilder::S3_BUCKETNAME), $storageReference);
    }

    private function setSatisfaction(Report $report, User $user, int $satisfactionScore): Satisfaction
    {
        $submitDate = clone $report->getStartDate();
        $submitDate->modify('+1 year');
        $satisfaction = new Satisfaction();
        $satisfaction->setScore($satisfactionScore);
        $satisfaction->setComments('random comment');
        $satisfaction->setReport($report);
        $satisfaction->setReporttype($report->getType());
        $satisfaction->setDeputyrole($user->getRoleName());
        $satisfaction->setCreated($submitDate);

        return $satisfaction;
    }

    private function addOrgClientsDeputyAndReportsToOrgDeputy(
        User $user,
        Organisation $organisation,
        bool $completed = false,
        bool $submitted = false,
        string $reportType = Report::PROF_PFA_HIGH_ASSETS_TYPE,
        ?\DateTime $startDate = null,
        ?int $satisfactionScore = null,
        ?string $deputyEmail = null,
        ?string $caseNumber = null,
        ?string $deputyUid = null,
    ): void {
        $client = $this->clientTestHelper->generateClient($this->em, $user, $organisation, $caseNumber);
        $report = $this->reportTestHelper->generateReport($this->em, $client, $reportType, $startDate);
        $deputy = $this->deputyTestHelper->generateDeputy($deputyEmail, $deputyUid, em: $this->em);

        $client->addReport($report);
        $client->setOrganisation($organisation);
        $client->setDeputy($deputy);

        $organisation->addClient($client);
        $organisation->addUser($user);

        $report->setClient($client);

        $user->addOrganisation($organisation);
        $user->setRegistrationDate($startDate);

        if ($completed) {
            $this->reportTestHelper->completeReport($report, $this->em);
        }

        $currentReport = $submitted ? $this->reportTestHelper->submitReport($report, $this->em) : null;

        $this->em->persist($report);

        // additional deputy <-> court order <-> report set up;
        // required to enable admin users to see reports in the dashboard etc.
        $deputy->setOrganisation($organisation);
        $this->em->persist($deputy);

        $courtOrderUid = '' . mt_rand(10000000, 99999999);
        $structuredReportType = ReportType::tryFrom($reportType);
        $courtOrderType = $structuredReportType?->courtOrderType;

        if ($courtOrderType === null) {
            throw new \LogicException("invalid report type: $reportType");
        }

        $courtOrder = $this->courtOrderTestHelper->generateCourtOrder(
            em: $this->em,
            client: $client,
            courtOrderUid: $courtOrderUid,
            type: $courtOrderType,
            report: $report,
            deputy: $deputy
        );

        if ($currentReport !== null) {
            $courtOrder->addReport($currentReport);
            $this->em->persist($courtOrder);
            $this->em->persist($currentReport);
        }

        $this->em->persist($deputy);
        $this->em->persist($user);
        $this->em->persist($client);
        $this->em->persist($report);
        $this->em->persist($organisation);

        if ($submitted && isset($satisfactionScore)) {
            $satisfaction = $this->setSatisfaction($report, $user, $satisfactionScore);
            $this->em->persist($satisfaction);
        }

        $this->em->flush();
    }

    public function getLoggedInUserDetails(string $email): array
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => strtolower($email)]);

        return FixtureHelperBuilder::buildUserDetails($user);
    }

    public function duplicateClient(int $clientId, ?bool $sameFirstName = true, ?bool $sameLastName = true): ?Client
    {
        $client = clone $this->em->getRepository(Client::class)->find($clientId);
        $client->setCaseNumber(ClientTestHelper::createValidCaseNumber());

        if (!$sameFirstName) {
            $client->setFirstName($client->getFirstName() . 'ABC');
        }

        if (!$sameLastName) {
            $client->setLastname($client->getLastName() . 'ABC');
        }

        $this->em->persist($client);
        $this->em->flush();

        return $client;
    }

    public function changeCaseNumber(int $clientId, string $newCaseNumber): ?Client
    {
        $client = $this->em->getRepository(Client::class)->find($clientId);
        $client->setCaseNumber($newCaseNumber);

        $this->em->persist($client);
        $this->em->flush();

        return $client;
    }

    public function createLayPfaHighAssetsNotStarted(string $testRunId, ?string $caseNumber = null, ?int $deputyUid = null): array
    {
        $user = $this->createDeputyClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-pfa-high-assets-not-started',
            Report::LAY_PFA_HIGH_ASSETS_TYPE,
            false,
            false,
            null,
            null,
            $caseNumber,
            false,
            true,
            $deputyUid
        );

        return FixtureHelperBuilder::buildUserDetails($user);
    }

    public function createLayPfaHighAssetsCompleted(string $testRunId): array
    {
        $user = $this->createDeputyClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-pfa-high-assets-completed',
            Report::LAY_PFA_HIGH_ASSETS_TYPE,
            true,
            false
        );

        return FixtureHelperBuilder::buildUserDetails($user);
    }

    public function createLayPfaHighAssetsSubmitted(string $testRunId): array
    {
        $user = $this->createDeputyClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-pfa-high-assets-submitted',
            Report::LAY_PFA_HIGH_ASSETS_TYPE,
            true,
            true
        );

        return FixtureHelperBuilder::buildUserDetails($user);
    }

    public function createLayPfaLowAssetsNotStarted(string $testRunId): array
    {
        $user = $this->createDeputyClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-pfa-low-assets-not-started',
            Report::LAY_PFA_LOW_ASSETS_TYPE,
            false,
            false
        );

        return FixtureHelperBuilder::buildUserDetails($user);
    }

    public function createProfPfaLowAssetsNotStarted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_ADMIN,
            'prof-pfa-low-assets-not-started',
            Report::PROF_PFA_LOW_ASSETS_TYPE,
            false,
            false
        );

        return FixtureHelperBuilder::buildUserDetails($user);
    }

    public function createLayPfaLowAssetsCompleted(string $testRunId): array
    {
        $user = $this->createDeputyClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-pfa-low-assets-completed',
            Report::LAY_PFA_LOW_ASSETS_TYPE,
            true,
            false
        );

        return FixtureHelperBuilder::buildUserDetails($user);
    }

    public function createProfPfaLowAssetsCompleted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_ADMIN,
            'prof-pfa-low-assets-completed',
            Report::PROF_PFA_LOW_ASSETS_TYPE,
            true,
            false
        );

        return FixtureHelperBuilder::buildUserDetails($user);
    }

    public function createLayHealthWelfareNotStarted(string $testRunId): array
    {
        $user = $this->createDeputyClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-hw-not-started',
            Report::LAY_HW_TYPE,
            false,
            false
        );

        return FixtureHelperBuilder::buildUserDetails($user);
    }

    public function createLayHealthWelfareCompleted(string $testRunId): array
    {
        $user = $this->createDeputyClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-hw-completed',
            Report::LAY_HW_TYPE,
            true,
            false
        );

        return FixtureHelperBuilder::buildUserDetails($user);
    }

    public function createLayHealthWelfareSubmitted(string $testRunId): array
    {
        $user = $this->createDeputyClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-hw-submitted',
            Report::LAY_HW_TYPE,
            true,
            true
        );

        return FixtureHelperBuilder::buildUserDetails($user);
    }

    public function createLayCombinedHighAssetsNotStarted(string $testRunId): array
    {
        $user = $this->createDeputyClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-combined-high-not-started',
            Report::LAY_COMBINED_HIGH_ASSETS_TYPE,
            false,
            false
        );

        return FixtureHelperBuilder::buildUserDetails($user);
    }

    public function createLayCombinedHighAssetsCompleted(string $testRunId): array
    {
        $user = $this->createDeputyClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-combined-high-completed',
            Report::LAY_COMBINED_HIGH_ASSETS_TYPE,
            true,
            false
        );

        return FixtureHelperBuilder::buildUserDetails($user);
    }

    public function createLayCombinedHighAssetsSubmitted(string $testRunId): array
    {
        $user = $this->createDeputyClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-combined-high-submitted',
            Report::LAY_COMBINED_HIGH_ASSETS_TYPE,
            true,
            true
        );

        return FixtureHelperBuilder::buildUserDetails($user);
    }

    public function createProfNamedHealthWelfareNotStarted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_NAMED,
            'prof-deputy-hw-not-started',
            Report::PROF_HW_TYPE,
            false,
            false
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createProfNamedHealthWelfareCompleted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_NAMED,
            'prof-deputy-hw-completed',
            Report::PROF_HW_TYPE,
            true,
            false
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createProfNamedHealthWelfareSubmitted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_NAMED,
            'prof-deputy-hw-submitted',
            Report::PROF_HW_TYPE,
            true,
            true
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createPaNamedHealthWelfareNotStarted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PA_NAMED,
            'pa-deputy-hw-not-started',
            Report::PA_HW_TYPE,
            false,
            false
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createPaNamedHealthWelfareCompleted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PA_NAMED,
            'pa-deputy-hw-completed',
            Report::PA_HW_TYPE,
            true,
            false
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createPaNamedHealthWelfareSubmitted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PA_NAMED,
            'pa-deputy-hw-submitted',
            Report::PA_HW_TYPE,
            true,
            true
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createPaAdminCombinedHighNotStarted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PA_ADMIN,
            'pa-admin-combined-high-not-started',
            Report::PA_COMBINED_HIGH_ASSETS_TYPE,
            false,
            false
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createPaAdminCombinedHighCompleted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PA_ADMIN,
            'pa-admin-combined-high-completed',
            Report::PA_COMBINED_HIGH_ASSETS_TYPE,
            true,
            false
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createPaAdminCombinedHighSubmitted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PA_ADMIN,
            'pa-admin-combined-high-submitted',
            Report::PA_COMBINED_HIGH_ASSETS_TYPE,
            true,
            true
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createProfAdminCombinedHighNotStarted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_ADMIN,
            'prof-admin-combined-high-not-started',
            Report::PROF_COMBINED_HIGH_ASSETS_TYPE,
            false,
            false
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createProfAdminCombinedHighCompleted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_ADMIN,
            'prof-admin-combined-high-completed',
            Report::PROF_COMBINED_HIGH_ASSETS_TYPE,
            true,
            false
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createProfAdminCombinedHighSubmitted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_ADMIN,
            'prof-admin-combined-high-submitted',
            Report::PROF_COMBINED_HIGH_ASSETS_TYPE,
            true,
            true
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createProfNamedPfaHighNotStarted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_NAMED,
            'prof-deputy-pfa-high-assets-not-started',
            Report::PROF_PFA_HIGH_ASSETS_TYPE,
            false,
            false
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createProfNamedPfaHighSubmitted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_NAMED,
            'prof-deputy-pfa-high-assets-submitted',
            Report::PROF_PFA_HIGH_ASSETS_TYPE,
            true,
            true
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createPaNamedPfaHighNotStarted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PA_NAMED,
            'pa-deputy-pfa-high-assets-not-started',
            Report::PA_PFA_HIGH_ASSETS_TYPE,
            false,
            false
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createPaNamedPfaHighSubmitted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PA_NAMED,
            'pa-deputy-pfa-high-assets-submitted',
            Report::PA_PFA_HIGH_ASSETS_TYPE,
            true,
            true
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createProfTeamHealthWelfareNotStarted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_TEAM_MEMBER,
            'prof-team-hw-not-started',
            Report::PROF_HW_TYPE,
            false,
            false
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createProfTeamHealthWelfareCompleted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_TEAM_MEMBER,
            'prof-team-hw-completed',
            Report::PROF_HW_TYPE,
            true,
            false
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createLayReportNotStarted(string $testRunId, ?string $caseNumber = null, ?int $deputyUid = null): array
    {
        $user = $this->createDeputyClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-report-not-started',
            Report::LAY_HW_TYPE,
            false,
            false,
            null,
            null,
            $caseNumber,
            true,
            true,
            $deputyUid
        );

        return FixtureHelperBuilder::buildUserDetails($user);
    }

    public function createProfAdminNotStarted(
        string $testRunId,
        ?string $deputyEmail = null,
        ?string $caseNumber = null,
        ?string $deputyUid = null,
    ): array {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_ADMIN,
            'prof-admin-hw-not-started',
            Report::PROF_HW_TYPE,
            false,
            false,
            $deputyEmail,
            $caseNumber,
            $deputyUid
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createProfAdminCompleted(
        string $testRunId,
        ?string $deputyEmail = null,
        ?string $caseNumber = null,
        ?string $deputyNumber = null,
    ): array {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_ADMIN,
            'prof-admin-hw-completed',
            Report::PROF_HW_TYPE,
            true,
            false,
            $deputyEmail,
            $caseNumber,
            $deputyNumber
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createProfAdminSubmitted(
        string $testRunId,
    ): array {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_ADMIN,
            'prof-admin-hw-submitted',
            Report::PROF_HW_TYPE,
            true,
            true
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createPAAdminHealthWelfareNotStarted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PA_ADMIN,
            'pa-admin-hw-not-started',
            Report::PROF_HW_TYPE,
            false,
            false
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createPAAdminHealthWelfareCompleted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PA_ADMIN,
            'pa-admin-hw-completed',
            Report::PROF_HW_TYPE,
            true,
            false
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createPAAdminHealthWelfareSubmitted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PA_ADMIN,
            'pa-admin-hw-completed',
            Report::PROF_HW_TYPE,
            true,
            true,
        );

        return FixtureHelperBuilder::buildOrgUserDetails($user);
    }

    public function createLayPfaHighAssetsNotStartedLegacyPasswordHash(string $testRunId, ?string $caseNumber = null): array
    {
        $user = $this->createDeputyClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-pfa-high-assets-not-started',
            Report::LAY_PFA_HIGH_ASSETS_TYPE,
            false,
            false,
            null,
            null,
            $caseNumber,
            true
        );

        return FixtureHelperBuilder::buildUserDetails($user);
    }

    public function createLayPfaHighAssetsNonPrimaryUser(string $testRunId, ?string $caseNumber = null, ?int $deputyUid = null): array
    {
        $user = $this->createDeputyClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-pfa-high-not-started-non-primary',
            Report::LAY_PFA_HIGH_ASSETS_TYPE,
            false,
            false,
            null,
            null,
            $caseNumber,
            false,
            false,
            $deputyUid
        );

        return FixtureHelperBuilder::buildUserDetails($user);
    }

    public function createAdmin(string $testRunId): array
    {
        $user = $this->createAdminUser(
            $testRunId,
            User::ROLE_ADMIN,
            'admin'
        );

        return FixtureHelperBuilder::buildAdminUserDetails($user);
    }

    public function createAdminManager(string $testRunId): array
    {
        $user = $this->createAdminUser(
            $testRunId,
            User::ROLE_ADMIN_MANAGER,
            'admin-manager'
        );

        return FixtureHelperBuilder::buildAdminUserDetails($user);
    }

    public function createSuperAdmin(string $testRunId): array
    {
        $user = $this->createAdminUser(
            $testRunId,
            User::ROLE_SUPER_ADMIN,
            'super-admin'
        );

        return FixtureHelperBuilder::buildAdminUserDetails($user);
    }

    /**
     * @return array<User>
     */
    public function createDataForAnalytics(string $testRunId, $timeAgo, $satisfactionScore): array
    {
        $startDate = new \DateTime($timeAgo);
        $deputies = [];

        $deputies[] = $this->createOrgUserClientDeputyAndReport(
            $testRunId . '_1',
            User::ROLE_PROF_NAMED,
            'analytics-prof-submitted',
            Report::PROF_HW_TYPE,
            true,
            true,
            null,
            null,
            null,
            $startDate,
            $satisfactionScore
        );

        $deputies[] = $this->createOrgUserClientDeputyAndReport(
            $testRunId . '_2',
            User::ROLE_PA_NAMED,
            'analytics-pa-submitted',
            Report::PA_HW_TYPE,
            true,
            true,
            null,
            null,
            null,
            $startDate,
            $satisfactionScore
        );

        $deputies[] = $this->createDeputyClientAndReport(
            $testRunId . '_3',
            User::ROLE_LAY_DEPUTY,
            'analytics-lay-submitted',
            Report::LAY_HW_TYPE,
            true,
            true,
            $startDate,
            $satisfactionScore
        );

        return $deputies;
    }

    private function createOrganisation(string $testRunId, string $emailIdentifier)
    {
        if (!$this->fixturesEnabled) {
            throw new BehatException('Prod mode enabled - cannot create fixture users');
        }

        $organisation = $this->em->getRepository(Organisation::class)->findByEmailIdentifier($emailIdentifier);

        if ($organisation instanceof Organisation) {
            return $organisation;
        }

        $orgName = sprintf('prof-%s-%s', $this->orgName, $testRunId);

        $organisation = $this->organisationTestHelper->createOrganisation($orgName, $emailIdentifier);
        $this->em->persist($organisation);

        return $organisation;
    }

    public function createDataForAdminUserTests(string $testPurpose): void
    {
        $userRoles = [
            ['typeSuffix' => 'lay', 'role' => User::ROLE_LAY_DEPUTY],
            ['typeSuffix' => 'pa-n', 'role' => User::ROLE_PA_NAMED],
            ['typeSuffix' => 'pa', 'role' => User::ROLE_PA],
            ['typeSuffix' => 'prof-n', 'role' => User::ROLE_PROF_NAMED],
            ['typeSuffix' => 'prof', 'role' => User::ROLE_PROF],
            ['typeSuffix' => 'admin', 'role' => User::ROLE_ADMIN],
            ['typeSuffix' => 'manager', 'role' => User::ROLE_ADMIN_MANAGER],
            ['typeSuffix' => 'super', 'role' => User::ROLE_SUPER_ADMIN],
        ];

        foreach ($userRoles as $userRole) {
            $user = $this->userTestHelper
                ->createUser(null, $userRole['role'], sprintf('%s-%s@t.uk', $testPurpose . '-test-' . $userRole['typeSuffix'], $this->testRunId));
            $this->setPassword($user);
        }

        $this->createDeputyClientAndReport(
            $this->testRunId,
            User::ROLE_LAY_DEPUTY,
            $testPurpose . '-test-lay-hw',
            Report::LAY_HW_TYPE,
            false,
            false,
        );
    }

    private function createDeputyClientAndReport(
        string $testRunId,
        string $userRole,
        string $emailPrefix,
        string $reportType,
        bool $completed,
        bool $submitted,
        ?\DateTime $startDate = null,
        ?int $satisfactionScore = null,
        ?string $caseNumber = null,
        bool $legacyPasswordHash = false,
        bool $isPrimary = true,
        ?int $deputyUid = null,
    ): User {
        if (!$this->fixturesEnabled) {
            throw new BehatException('Prod mode enabled - cannot create fixture users');
        }

        $this->testRunId = $testRunId;

        $user = $this->userTestHelper->createAndPersistUser(
            $this->em,
            null,
            $userRole,
            sprintf('%s-%s@t.uk', $emailPrefix, $this->testRunId),
            $deputyUid,
            $isPrimary
        );

        $this->addClientsAndReportsToLayDeputy($user, $completed, $submitted, $reportType, $startDate, $satisfactionScore, $caseNumber);

        $this->setPassword($user, $legacyPasswordHash);

        return $user;
    }

    private function createAdminUser(string $testRunId, $userRole, $emailPrefix): User
    {
        if (!$this->fixturesEnabled) {
            throw new BehatException('Prod mode enabled - cannot create fixture users');
        }
        $this->testRunId = $testRunId;

        $user = $this->userTestHelper
            ->createUser(null, $userRole, sprintf('%s-%s@t.uk', $emailPrefix, $this->testRunId));

        $this->setPassword($user);

        return $user;
    }

    private function createOrgUserClientDeputyAndReport(
        string $testRunId,
        string $userRole,
        string $emailPrefix,
        string $reportType,
        bool $completed,
        bool $submitted,
        ?string $deputyEmail = null,
        ?string $caseNumber = null,
        ?string $deputyUid = null,
        ?\DateTime $startDate = null,
        ?int $satisfactionScore = null,
    ): User {
        if (!$this->fixturesEnabled) {
            throw new BehatException('Prod mode enabled - cannot create fixture users');
        }

        $this->testRunId = $testRunId;

        $domain = $deputyEmail ? substr(strstr($deputyEmail, '@'), 1) : 't.uk';
        $emailIdentifier = $domain !== 't.uk' ? $domain : sprintf('prof-%s-%s', $this->orgEmailIdentifier, $this->testRunId);
        $organisation = $this->createOrganisation($this->testRunId, $emailIdentifier);

        $userEmail = sprintf('%s-%s@%s', $emailPrefix, $this->testRunId, $domain);
        $user = $this->userTestHelper->createUser(null, $userRole, $userEmail);
        $this->em->persist($user);

        $this->addOrgClientsDeputyAndReportsToOrgDeputy(
            $user,
            $organisation,
            $completed,
            $submitted,
            $reportType,
            $startDate,
            $satisfactionScore,
            $deputyEmail,
            $caseNumber,
            $deputyUid
        );

        $this->setPassword($user);

        return $user;
    }

    public function setPassword($user, $legacyPasswordHash = false): void
    {
        if ($legacyPasswordHash) {
            $user->setPassword($this->fixtureParams['legacy_password_hash']);
        } else {
            $user->setPassword($this->hasher->hashPassword($user, $this->fixtureParams['account_password']));
        }

        $this->em->persist($user);
        $this->em->flush();
    }

    public function createPreRegistration(
        string $reportType = 'OPG102',
        string $orderType = 'PFA',
        string $clientLastname = 'Smith',
        ?string $caseNumber = null,
    ): PreRegistration {
        if (is_null($caseNumber)) {
            $caseNumber = '' . random_int(10000000, 99999999);
        }

        $data = [
            'reportType' => $reportType,
            'orderType' => $orderType,
            'clientLastName' => $clientLastname,
            'caseNumber' => $caseNumber,
        ];

        $preRegistration = $this->preRegistrationFactory->create($data);
        $this->em->persist($preRegistration);
        $this->em->flush();

        return $preRegistration;
    }

    public function getLegacyPasswordHash(): string
    {
        return $this->fixtureParams['legacy_password_hash'];
    }

    public function createAndPersistCourtOrder(CourtOrderType $orderType, Client $client, ?Deputy $deputy = null, ?Report $report = null, ?string $courtOrderUid = null): CourtOrder
    {
        $faker = Factory::create('en_GB');
        if (is_null($courtOrderUid)) {
            $courtOrderUid = '7' . $faker->randomNumber(9);
        }

        return $this->courtOrderTestHelper::generateCourtOrder($this->em, $client, $courtOrderUid, 'ACTIVE', $orderType, $report, $deputy);
    }

    public function createDeputyOnOrder(CourtOrder $courtOrder, ?\DateTime $lastLoggedIn = null): Deputy
    {
        $user = $this->userTestHelper::createUser(active: false);

        $deputy = $this->deputyTestHelper::generateDeputy(null, null, $user, $this->em);
        $deputy->associateWithCourtOrder($courtOrder);

        // if this is null, the user counts as "awaiting registration"
        if ($lastLoggedIn !== null) {
            $user->setLastLoggedIn($lastLoggedIn);
        }


        $this->em->persist($user);
        $this->em->persist($deputy);
        $this->em->flush();

        return $deputy;
    }

    public function setCourtOrderLatestReportType(CourtOrder $courtOrder, string $reportType): void
    {
        $report = $courtOrder->getLatestReport();
        $report->setType($reportType);
        $this->em->persist($report);
        $this->em->flush();
    }

    public function createAndPersistOrganisation(string $name, string $emailIdentifier, bool $isActivated = true): Organisation
    {
        $organisation = new Organisation();
        $organisation->setName($name);
        $organisation->setEmailIdentifier($emailIdentifier);
        $organisation->setIsActivated($isActivated);
        $this->em->persist($organisation);
        $this->em->flush();

        return $organisation;
    }

    public function createAndPersistExpense(int $reportId, int $amount, string $explanation): Expense
    {
        $report = $this->em->getRepository(Report::class)->find($reportId);

        $expense = new Expense($report);
        $expense->setAmount($amount);
        $expense->setExplanation($explanation);

        $report->addExpense($expense);
        $report->setPaidForAnything('yes');

        $this->em->persist($expense);
        $this->em->persist($report);
        $this->em->flush();

        return $expense;
    }

    public function createDeputy(?string $email = null, ?string $deputyUid = null, ?User $user = null): Deputy
    {
        return $this->deputyTestHelper::generateDeputy($email, $deputyUid, $user, $this->em);
    }
}
