<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Helpers;

use App\Entity\Client;
use App\Entity\Deputy;
use App\Entity\Ndr\Ndr;
use App\Entity\Organisation;
use App\Entity\PreRegistration;
use App\Entity\Report\Report;
use App\Entity\Satisfaction;
use App\Entity\User;
use App\FixtureFactory\PreRegistrationFactory;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\DeputyTestHelper;
use App\TestHelpers\OrganisationTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\TestHelpers\UserTestHelper;
use App\Tests\Behat\BehatException;
use Aws\S3\S3ClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class FixtureHelper
{
    private UserTestHelper $userTestHelper;
    private ReportTestHelper $reportTestHelper;
    private ClientTestHelper $clientTestHelper;
    private OrganisationTestHelper $organisationTestHelper;
    private DeputyTestHelper $deputyTestHelper;

    private string $testRunId = '';
    private string $orgName = 'Test Org';
    private string $orgEmailIdentifier = 'test-org.uk';
    public const S3_BUCKETNAME = 'S3_BUCKETNAME';

    public function __construct(
        private EntityManagerInterface $em,
        private array $fixtureParams,
        private UserPasswordHasherInterface $hasher,
        private string $symfonyEnvironment,
        private PreRegistrationFactory $preRegistrationFactory,
        private S3ClientInterface $s3Client,
    ) {
        $this->userTestHelper = new UserTestHelper();
        $this->reportTestHelper = new ReportTestHelper();
        $this->clientTestHelper = new ClientTestHelper();
        $this->organisationTestHelper = new OrganisationTestHelper();
        $this->deputyTestHelper = new DeputyTestHelper();
    }

    public static function buildUserDetails(User $user)
    {
        $client = $user->isLayDeputy() ? $user->getFirstClient() : $user?->getOrganisations()[0]?->getClients()[0];

        if ($client) {
            $currentReport = $user->getNdrEnabled() ? $client->getNdr() : $client?->getCurrentReport();
            $currentReportType = $user->getNdrEnabled() ? null : $currentReport?->getType();
            $previousReport = $user->getNdrEnabled() ? null : $client?->getReports()[0];
        } else {
            $currentReport = null;
            $currentReportType = null;
            $previousReport = null;
        }

        $userDetails = [
            'userId' => $user->getId(),
            'userEmail' => $user->getEmail(),
            'userRole' => $user->getRoleName(),
            'userFirstName' => $user->getFirstname(),
            'userLastName' => $user->getLastname(),
            'userFullName' => $user->getFullName(),
            'userFullAddressArray' => self::buildUserAddressArray($user),
            'userPhone' => $user->getPhoneMain(),
            'clientId' => $client?->getId(),
            'clientFirstName' => $client?->getFirstname(),
            'clientLastName' => $client?->getLastname(),
            'clientFullAddressArray' => $client ? self::buildClientAddressArray($client) : null,
            'clientEmail' => $client?->getEmail(),
            'clientCaseNumber' => $client?->getCaseNumber(),
            'clientArchivedAt' => $client?->getArchivedAt(),
            'currentReportId' => $currentReport?->getId(),
            'currentReportType' => $currentReportType,
            'currentReportNdrOrReport' => $currentReport instanceof Ndr ? 'ndr' : 'report',
            'currentReportDueDate' => $currentReport?->getDueDate(),
            'currentReportStartDate' => $currentReport?->getStartDate(),
            'currentReportEndDate' => $currentReport instanceof Ndr ? null : $currentReport?->getEndDate(),
            'currentReportBankAccountId' => $currentReport?->getBankAccounts()[0]->getId(),
            'courtDate' => $client ? $client->getCourtDate()?->format('j F Y') : null,
        ];

        if ($previousReport && $previousReport->getId() !== $currentReport->getId()) {
            $userDetails = array_merge(
                $userDetails,
                [
                    'previousReportId' => $previousReport->getId(),
                    'previousReportType' => $previousReport->getType(),
                    'previousReportNdrOrReport' => $previousReport instanceof Ndr ? 'ndr' : 'report',
                    'previousReportDueDate' => $previousReport->getDueDate(),
                    'previousReportStartDate' => $previousReport->getStartDate(),
                    'previousReportEndDate' => $previousReport->getEndDate(),
                    'previousReportBankAccountId' => $previousReport->getBankAccounts()[0]->getId(),
                ]
            );
        }

        return $userDetails;
    }

    public static function buildOrgUserDetails(User $user)
    {
        $organisation = $user->getOrganisations()->first();
        $deputy = $organisation?->getClients()[0]->getDeputy();

        if ($deputy) {
            $details = [
                'organisationName' => $organisation->getName(),
                'organisationEmailIdentifier' => $organisation->getEmailIdentifier(),
                'deputyName' => sprintf(
                    '%s %s',
                    $deputy->getFirstname(),
                    $deputy->getLastName()
                ),
                'deputyFullAddressArray' => self::buildDeputyAddressArray($deputy),
                'deputyPhone' => $deputy->getPhoneMain(),
                'deputyPhoneAlt' => $deputy->getPhoneAlternative(),
                'deputyEmail' => $deputy->getEmail1(),
                'deputyEmailAlt' => $deputy->getEmail2(),
            ];
        }

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
            'userFullAddressArray' => self::buildUserAddressArray($user),
        ];
    }

    private static function buildUserAddressArray(User $user): array
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

    private static function buildClientAddressArray(Client $client): array
    {
        return array_filter(
            [
                'address1' => $client->getAddress(),
                'address2' => $client->getAddress2(),
                'address3' => $client->getAddress3(),
                'addressPostcode' => $client->getPostcode(),
                'addressCountry' => $client->getCountry(),
            ],
            function ($value, $key) {
                return !is_null($value);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    private static function buildDeputyAddressArray(Deputy $deputy): array
    {
        return array_filter(
            [
                'address1' => $deputy->getAddress1(),
                'address2' => $deputy->getAddress2(),
                'address3' => $deputy->getAddress3(),
                'address4' => $deputy->getAddress4(),
                'address5' => $deputy->getAddress5(),
                'addressPostcode' => $deputy->getAddressPostcode(),
                'addressCountry' => $deputy->getAddressCountry(),
            ],
            function ($value, $key) {
                return !is_null($value);
            },
            ARRAY_FILTER_USE_BOTH
        );
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
        if ('prod' === $this->symfonyEnvironment) {
            throw new BehatException('Prod mode enabled - cannot create fixture users');
        }

        $user = $this->createUser($roleName, $email);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    private function addClientsAndReportsToLayDeputy(
        User $deputy,
        bool $completed = false,
        bool $submitted = false,
        ?string $type = null,
        ?\DateTime $startDate = null,
        ?int $satisfactionScore = null,
        ?string $caseNumber = null
    ) {
        $client = $this->clientTestHelper->generateClient($this->em, $deputy, null, $caseNumber);
        $report = $this->reportTestHelper->generateReport($this->em, $client, $type, $startDate);

        $client->addReport($report);
        $report->setClient($client);
        $deputy->addClient($client);
        $deputy->setRegistrationDate($startDate);

        if ($completed) {
            $this->reportTestHelper->completeLayReport($report, $this->em);
        }

        if ($submitted) {
            $this->storeFileInS3(getenv(self::S3_BUCKETNAME), 'dd_doc_1234_9876543219876');
            $this->storeFileInS3(getenv(self::S3_BUCKETNAME), 'dd_doc_1234_123456789123456');
            $this->reportTestHelper->submitReport($report, $this->em);
        }

        $this->em->persist($client);
        $this->em->persist($report);

        if ($submitted and isset($satisfactionScore)) {
            $satisfaction = $this->setSatisfaction($report, $deputy, $satisfactionScore);
            $this->em->persist($satisfaction);
        }
    }

    private function addReportsToClient(
        Client $client,
        ?User $user = null,
        bool $completed = false,
        bool $submitted = false,
        ?string $type = null,
        ?\DateTime $startDate = null,
        ?int $satisfactionScore = null,
    ) {
        $report = $this->reportTestHelper->generateReport($this->em, $client, $type, $startDate);

        $client->addReport($report);
        $report->setClient($client);

        if ($completed) {
            $this->reportTestHelper->completeLayReport($report, $this->em);
        }

        if ($submitted) {
            $this->storeFileInS3(getenv(self::S3_BUCKETNAME), 'dd_doc_1234_9876543219876');
            $this->storeFileInS3(getenv(self::S3_BUCKETNAME), 'dd_doc_1234_123456789123456');
            $this->reportTestHelper->submitReport($report, $this->em);
        }

        $this->em->persist($client);
        $this->em->persist($report);

        if ($submitted and isset($satisfactionScore) and isset($user)) {
            $satisfaction = $this->setSatisfaction($report, $user, $satisfactionScore);
            $this->em->persist($satisfaction);
        }
    }

    private function storeFileInS3(string $bucketName, string $key)
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
        $this->s3Client->deleteMatchingObjects(getenv(self::S3_BUCKETNAME), $storageReference, '', []);
    }

    private function setSatisfaction(Report $report, User $deputy, int $satisfactionScore)
    {
        $submitDate = clone $report->getStartDate();
        $submitDate->modify('+1 year');
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
        $ndr = $this->reportTestHelper->generateNdr($this->em, $deputy, $client);

        if ($completed) {
            $this->reportTestHelper->completeNdrLayReport($ndr, $this->em);
        }

        //        if ($submitted) {
        //            placeholder for when submitted version needed...
        //        }

        $this->em->persist($ndr);
        $this->em->persist($client);
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
        ?string $deputyUid = null
    ) {
        $client = $this->clientTestHelper->generateClient($this->em, $user, $organisation, $caseNumber);
        $report = $this->reportTestHelper->generateReport($this->em, $client, $reportType, $startDate);
        $deputy = $this->deputyTestHelper->generateDeputy($deputyEmail, $deputyUid);

        $client->addReport($report);
        $client->setOrganisation($organisation);
        $client->setDeputy($deputy);

        $organisation->addClient($client);
        $organisation->addUser($user);

        $report->setClient($client);

        $user->addOrganisation($organisation);
        $user->setRegistrationDate($startDate);

        if ($completed) {
            $this->reportTestHelper->completeLayReport($report, $this->em);
        }

        if ($submitted) {
            $this->reportTestHelper->submitReport($report, $this->em);
        }

        $this->em->persist($deputy);
        $this->em->persist($user);
        $this->em->persist($client);
        $this->em->persist($report);
        $this->em->persist($organisation);

        if ($submitted and isset($satisfactionScore)) {
            $satisfaction = $this->setSatisfaction($report, $user, $satisfactionScore);
            $this->em->persist($satisfaction);
        }
    }

    public function getLoggedInUserDetails(string $email): array
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => strtolower($email)]);

        return self::buildUserDetails($user);
    }

    public function duplicateClient(int $clientId, ?bool $sameFirstName = true, ?bool $sameLastName = true)
    {
        $client = clone $this->em->getRepository(Client::class)->find($clientId);
        $client->setCaseNumber(ClientTestHelper::createValidCaseNumber());

        if (!$sameFirstName) {
            $client->setFirstName($client->getFirstName().'ABC');
        }

        if (!$sameLastName) {
            $client->setLastname($client->getLastName().'ABC');
        }

        $this->em->persist($client);
        $this->em->flush();

        return $client;
    }

    public function changeCaseNumber(int $clientId, string $newCaseNumber)
    {
        $client = $this->em->getRepository(Client::class)->find($clientId);
        $client->setCaseNumber($newCaseNumber);

        $this->em->persist($client);
        $this->em->flush();

        return $client;
    }

    public function createLayPfaHighAssetsNotStarted(string $testRunId, ?string $caseNumber = null, ?int $deputyUid = null, bool $isMultiClientDeputy = false): array
    {
        $user = $this->createDeputyClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-pfa-high-assets-not-started',
            Report::LAY_PFA_HIGH_ASSETS_TYPE,
            false,
            false,
            false,
            null,
            null,
            $caseNumber,
            false,
            true,
            $deputyUid,
            $isMultiClientDeputy
        );

        return self::buildUserDetails($user);
    }

    public function createLayPfaHighAssetsNotStartedWithNdr(string $testRunId, ?string $caseNumber = null, ?int $deputyUid = null, $isMultiClientDeputy = false): array
    {
        $user = $this->createDeputyClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-pfa-high-assets-not-started-with-ndr',
            Report::LAY_PFA_HIGH_ASSETS_TYPE,
            false,
            false,
            true,
            null,
            null,
            $caseNumber,
            false,
            true,
            $deputyUid,
            $isMultiClientDeputy
        );

        $this->addReportsToClient($user->getFirstClient(), $user);

        return self::buildUserDetails($user);
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

        return self::buildUserDetails($user);
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

        return self::buildUserDetails($user);
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

        return self::buildUserDetails($user);
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

        return self::buildUserDetails($user);
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

        return self::buildUserDetails($user);
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

        return self::buildUserDetails($user);
    }

    public function createLayPfaLowAssetsSubmitted(string $testRunId): array
    {
        $user = $this->createDeputyClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-pfa-low-assets-submitted',
            Report::LAY_PFA_LOW_ASSETS_TYPE,
            true,
            true
        );

        return self::buildUserDetails($user);
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

        return self::buildUserDetails($user);
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

        return self::buildUserDetails($user);
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

        return self::buildUserDetails($user);
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

        return self::buildUserDetails($user);
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

        return self::buildUserDetails($user);
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

        return self::buildUserDetails($user);
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

        return self::buildOrgUserDetails($user);
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

        return self::buildOrgUserDetails($user);
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

        return self::buildOrgUserDetails($user);
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

        return self::buildOrgUserDetails($user);
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

        return self::buildOrgUserDetails($user);
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

        return self::buildOrgUserDetails($user);
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

        return self::buildOrgUserDetails($user);
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

        return self::buildOrgUserDetails($user);
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

        return self::buildOrgUserDetails($user);
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

        return self::buildOrgUserDetails($user);
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

        return self::buildOrgUserDetails($user);
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

        return self::buildOrgUserDetails($user);
    }

    public function createProfNamedPfaHighNotStarted(string $testRunId)
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_NAMED,
            'prof-deputy-pfa-high-assets-not-started',
            Report::PROF_PFA_HIGH_ASSETS_TYPE,
            false,
            false
        );

        return self::buildOrgUserDetails($user);
    }

    public function createProfNamedPfaHighSubmitted(string $testRunId)
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_NAMED,
            'prof-deputy-pfa-high-assets-submitted',
            Report::PROF_PFA_HIGH_ASSETS_TYPE,
            true,
            true
        );

        return self::buildOrgUserDetails($user);
    }

    public function createPaNamedPfaHighNotStarted(string $testRunId)
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PA_NAMED,
            'pa-deputy-pfa-high-assets-not-started',
            Report::PA_PFA_HIGH_ASSETS_TYPE,
            false,
            false
        );

        return self::buildOrgUserDetails($user);
    }

    public function createPaNamedPfaHighSubmitted(string $testRunId)
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PA_NAMED,
            'pa-deputy-pfa-high-assets-submitted',
            Report::PA_PFA_HIGH_ASSETS_TYPE,
            true,
            true
        );

        return self::buildOrgUserDetails($user);
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

        return self::buildOrgUserDetails($user);
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

        return self::buildOrgUserDetails($user);
    }

    public function createProfTeamHealthWelfareSubmitted(string $testRunId): array
    {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_TEAM_MEMBER,
            'prof-team-hw-submitted',
            Report::PROF_HW_TYPE,
            true,
            true
        );

        return self::buildOrgUserDetails($user);
    }

    public function createLayNdrNotStarted(string $testRunId, ?string $caseNumber = null, ?int $deputyUid = null): array
    {
        $user = $this->createDeputyClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-ndr-not-started',
            Report::LAY_HW_TYPE,
            false,
            false,
            true,
            null,
            null,
            $caseNumber,
            true,
            false,
            $deputyUid
        );

        return self::buildUserDetails($user);
    }

    public function createLayNdrCompleted(string $testRunId): array
    {
        $user = $this->createDeputyClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-ndr-completed',
            Report::LAY_HW_TYPE,
            true,
            false,
            true
        );

        return self::buildUserDetails($user);
    }

    public function createLayNdrSubmitted(string $testRunId): array
    {
        $user = $this->createDeputyClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-ndr-submitted',
            Report::LAY_HW_TYPE,
            true,
            false,
            true
        );

        return self::buildUserDetails($user);
    }

    public function createProfAdminNotStarted(
        string $testRunId,
        ?string $deputyEmail = null,
        ?string $caseNumber = null,
        ?string $deputyUid = null
    ) {
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

        return self::buildOrgUserDetails($user);
    }

    public function createProfAdminCompleted(
        string $testRunId,
        ?string $deputyEmail = null,
        ?string $caseNumber = null,
        ?string $deputyNumber = null
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

        return self::buildOrgUserDetails($user);
    }

    public function createProfAdminSubmitted(
        string $testRunId,
        ?string $deputyEmail = null,
        ?string $caseNumber = null,
        ?string $deputyNumber = null
    ): array {
        $user = $this->createOrgUserClientDeputyAndReport(
            $testRunId,
            User::ROLE_PROF_ADMIN,
            'prof-admin-hw-submitted',
            Report::PROF_HW_TYPE,
            true,
            true
        );

        return self::buildOrgUserDetails($user);
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

        return self::buildOrgUserDetails($user);
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

        return self::buildOrgUserDetails($user);
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

        return self::buildOrgUserDetails($user);
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
            false,
            null,
            null,
            $caseNumber,
            true
        );

        return self::buildUserDetails($user);
    }

    public function createLayPfaHighAssetsNonPrimaryUser(string $testRunId, ?string $caseNumber = null, ?int $deputyUid = null): array
    {
        $user = $this->createDeputyClientAndReport(
            $testRunId,
            User::ROLE_LAY_DEPUTY,
            'lay-pfa-high-assets-not-started-non-primary',
            Report::LAY_PFA_HIGH_ASSETS_TYPE,
            false,
            false,
            false,
            null,
            null,
            $caseNumber,
            true,
            false,
            $deputyUid
        );

        return self::buildUserDetails($user);
    }

    public function createAdmin(string $testRunId): array
    {
        $user = $this->createAdminUser(
            $testRunId,
            User::ROLE_ADMIN,
            'admin'
        );

        return self::buildAdminUserDetails($user);
    }

    public function createAdminManager(string $testRunId): array
    {
        $user = $this->createAdminUser(
            $testRunId,
            User::ROLE_ADMIN_MANAGER,
            'admin-manager'
        );

        return self::buildAdminUserDetails($user);
    }

    public function createSuperAdmin(string $testRunId): array
    {
        $user = $this->createAdminUser(
            $testRunId,
            User::ROLE_SUPER_ADMIN,
            'super-admin'
        );

        return self::buildAdminUserDetails($user);
    }

    public function createDataForAnalytics(string $testRunId, $timeAgo, $satisfactionScore)
    {
        $startDate = new \DateTime($timeAgo);
        $deputies = [];

        $deputies[] = $this->createOrgUserClientDeputyAndReport(
            $testRunId.'_1',
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
            $testRunId.'_2',
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
            $testRunId.'_3',
            User::ROLE_LAY_DEPUTY,
            'analytics-lay-submitted',
            Report::LAY_HW_TYPE,
            true,
            true,
            false,
            $startDate,
            $satisfactionScore
        );

        return $deputies;
    }

    private function createOrganisation(string $testRunId, string $emailIdentifier)
    {
        if ('prod' === $this->symfonyEnvironment) {
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

    public function createDataForAdminUserTests(string $testPurpose)
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
            ['typeSuffix' => 'ad', 'role' => User::ROLE_AD],
        ];

        foreach ($userRoles as $userRole) {
            $user = $this->userTestHelper
                ->createUser(null, $userRole['role'], sprintf('%s-%s@t.uk', $testPurpose.'-test-'.$userRole['typeSuffix'], $this->testRunId));
            $this->setPassword($user);
        }

        $this->createDeputyClientAndReport(
            $this->testRunId,
            User::ROLE_LAY_DEPUTY,
            $testPurpose.'-test-ndr',
            Report::LAY_HW_TYPE,
            false,
            false,
            true
        );
    }

    private function createDeputyClientAndReport(
        string $testRunId,
        $userRole,
        $emailPrefix,
        $reportType,
        $completed,
        $submitted,
        bool $ndr = false,
        ?\DateTime $startDate = null,
        ?int $satisfactionScore = null,
        ?string $caseNumber = null,
        bool $legacyPasswordHash = false,
        bool $isPrimary = true,
        ?int $deputyUid = null,
        bool $isMultiClientDeputy = false,
    ) {
        if ('prod' === $this->symfonyEnvironment) {
            throw new BehatException('Prod mode enabled - cannot create fixture users');
        }

        $this->testRunId = $testRunId;

        $deputy = $this->userTestHelper
            ->createUser(null, $userRole, sprintf('%s-%s@t.uk', $emailPrefix, $this->testRunId), $isPrimary, $deputyUid, $isMultiClientDeputy);

        if ($ndr) {
            $this->addClientsAndReportsToNdrLayDeputy($deputy, $completed, $submitted);
        } else {
            $this->addClientsAndReportsToLayDeputy($deputy, $completed, $submitted, $reportType, $startDate, $satisfactionScore, $caseNumber);
        }

        $this->setPassword($deputy, $legacyPasswordHash);

        return $deputy;
    }

    private function createAdminUser(string $testRunId, $userRole, $emailPrefix)
    {
        if ('prod' === $this->symfonyEnvironment) {
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
        $userRole,
        $emailPrefix,
        $reportType,
        $completed,
        $submitted,
        ?string $deputyEmail = null,
        ?string $caseNumber = null,
        ?string $deputyUid = null,
        ?\DateTime $startDate = null,
        ?int $satisfactionScore = null
    ) {
        if ('prod' === $this->symfonyEnvironment) {
            throw new BehatException('Prod mode enabled - cannot create fixture users');
        }

        $this->testRunId = $testRunId;
        $domain = $deputyEmail ? substr(strstr($deputyEmail, '@'), 1) : 't.uk';
        $emailIdentifier = 't.uk' !== $domain ? $domain : sprintf('prof-%s-%s', $this->orgEmailIdentifier, $this->testRunId);

        $organisation = $this->createOrganisation($this->testRunId, $emailIdentifier);

        $userEmail = sprintf('%s-%s@%s', $emailPrefix, $this->testRunId, $domain);

        $user = $this->userTestHelper
            ->createUser(null, $userRole, $userEmail);

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

    private function setPassword($user, $legacyPasswordHash = false)
    {
        if ($legacyPasswordHash) {
            $user->setPassword($this->fixtureParams['legacy_password_hash']);
        } else {
            $user->setPassword($this->hasher->hashPassword($user, $this->fixtureParams['account_password']));
        }

        $this->em->persist($user);
        $this->em->flush();
    }

    public function createPreRegistration(string $reportType = 'OPG102', string $orderType = 'PFA', string $clientLastname = 'Smith'): PreRegistration
    {
        $data = ['reportType' => $reportType, 'orderType' => $orderType, 'clientLastName' => $clientLastname];

        $preRegistration = $this->preRegistrationFactory->create($data);
        $this->em->persist($preRegistration);
        $this->em->flush();

        return $preRegistration;
    }

    public function getLegacyPasswordHash(): string
    {
        return $this->fixtureParams['legacy_password_hash'];
    }
}
