<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use App\Entity\Client;
use App\Service\File\Storage\S3Storage;
use App\Service\ParameterStoreService;
use App\TestHelpers\ReportTestHelper;
use App\Tests\Behat\v2\Analytics\AnalyticsTrait;
use App\Tests\Behat\v2\Helpers\FixtureHelper;
use Aws\S3\S3Client;
use Behat\Behat\Hook\Call\BeforeScenario;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\MinkExtension\Context\MinkContext;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class BaseFeatureContext extends MinkContext
{
    use AlertsTrait;
    use AnalyticsTrait;
    use AuthTrait;
    use AssertTrait;
    use DebugTrait;
    use ElementSelectionTrait;
    use ErrorsTrait;
    use ExpectedResultsTrait;
    use FixturesTrait;
    use FormFillingTrait;
    use INavigateToAdminTrait;
    use INavigateToFrontendTrait;
    use IShouldBeOnAdminTrait;
    use IShouldBeOnFrontendTrait;
    use IVisitAdminTrait;
    use IVisitFrontendTrait;
    use PageUrlsTrait;
    use ReportTrait;
    use UserExistsTrait;

    public const REPORT_SECTION_ENDPOINT = '/%s/%s/%s';

    public UserDetails $adminDetails;
    public UserDetails $adminManagerDetails;
    public UserDetails $superAdminDetails;

    public UserDetails $layDeputyNotStartedPfaHighAssetsDetails;
    public UserDetails $layDeputyCompletedPfaHighAssetsDetails;
    public UserDetails $layDeputySubmittedPfaHighAssetsDetails;

    public UserDetails $layDeputyNotStartedPfaLowAssetsDetails;
    public UserDetails $layDeputyCompletedPfaLowAssetsDetails;
    public UserDetails $layDeputySubmittedPfaLowAssetsDetails;

    public UserDetails $layDeputyNotStartedHealthWelfareDetails;
    public UserDetails $layDeputyCompletedHealthWelfareDetails;
    public UserDetails $layDeputySubmittedHealthWelfareDetails;

    public UserDetails $layDeputyNotStartedCombinedHighDetails;
    public UserDetails $layDeputyCompletedCombinedHighDetails;
    public UserDetails $layDeputySubmittedCombinedHighDetails;

    public UserDetails $layPfaHighNotStartedMultiClientDeputyPrimaryUser;
    public UserDetails $layPfaHighNotStartedMultiClientDeputyNonPrimaryUser;
    public UserDetails $layPfaHighNotStartedMultiClientDeputySecondNonPrimaryUser;

    public UserDetails $profNamedDeputyNotStartedHealthWelfareDetails;
    public UserDetails $profNamedDeputyCompletedHealthWelfareDetails;
    public UserDetails $profNamedDeputySubmittedHealthWelfareDetails;

    public UserDetails $publicAuthorityNamedNotStartedPfaHighDetails;
    public UserDetails $publicAuthorityNamedCompletedPfaHighDetails;
    public UserDetails $publicAuthorityNamedSubmittedPfaHighDetails;

    public UserDetails $profNamedDeputyNotStartedPfaHighDetails;
    public UserDetails $profNamedDeputyCompletedPfaHighDetails;
    public UserDetails $profNamedDeputySubmittedPfaHighDetails;

    public UserDetails $profTeamDeputyNotStartedHealthWelfareDetails;
    public UserDetails $profTeamDeputyCompletedHealthWelfareDetails;
    public UserDetails $profTeamDeputySubmittedHealthWelfareDetails;

    public UserDetails $profAdminDeputyHealthWelfareNotStartedDetails;
    public UserDetails $profAdminDeputyHealthWelfareCompletedDetails;
    public UserDetails $profAdminDeputyHealthWelfareSubmittedDetails;
    public UserDetails $profAdminDeputyNotStartedPfaLowAssetsDetails;
    public UserDetails $profAdminDeputyCompletedPfaLowAssetsDetails;

    public UserDetails $profAdminCombinedHighNotStartedDetails;
    public UserDetails $profAdminCombinedHighCompletedDetails;
    public UserDetails $profAdminCombinedHighSubmittedDetails;

    public UserDetails $publicAuthorityNamedDeputyNotStartedDetails;
    public UserDetails $publicAuthorityNamedDeputyCompletedDetails;
    public UserDetails $publicAuthorityNamedDeputySubmittedDetails;

    public UserDetails $publicAuthorityAdminCombinedHighNotStartedDetails;
    public UserDetails $publicAuthorityAdminCombinedHighCompletedDetails;
    public UserDetails $publicAuthorityAdminCombinedHighSubmittedDetails;

    public UserDetails $paAdminDeputyNotStartedDetails;
    public UserDetails $paAdminDeputyCompletedDetails;
    public UserDetails $paAdminDeputySubmittedDetails;

    public UserDetails $layNdrDeputyNotStartedDetails;
    public UserDetails $layNdrDeputyCompletedDetails;
    public UserDetails $layNdrDeputySubmittedDetails;

    public ?UserDetails $loggedInUserDetails = null;
    public ?UserDetails $interactingWithUserDetails = null;

    public array $fixtureUsers = [];

    public string $testRunId = '';
    public string $appEnvironment = '';

    public Generator $faker;

    protected Application $application;
    public BufferedOutput $output;
    private array $activeClientIds;

    public function __construct(
        protected readonly FixtureHelper $fixtureHelper,
        protected readonly KernelInterface $symfonyKernel,
        protected readonly EntityManagerInterface $em,
        protected readonly ReportTestHelper $reportTestHelper,
        protected readonly ParameterStoreService $parameterStoreService,
        protected readonly KernelInterface $kernel,
        protected readonly S3Storage $s3,
        protected readonly S3Client $s3Client
    ) {
        // Required so we can run tests against commands
        $this->application = new Application($kernel);
        $this->application->setCatchExceptions(true);
        $this->application->setAutoExit(true);
        $this->output = new BufferedOutput();

        $this->appEnvironment = $this->symfonyKernel->getEnvironment();

        if ('prod' === $this->appEnvironment) {
            throw new \Exception('Unable to run behat tests in prod mode. Change the apps mode to dev or test and try again');
        }
    }

    /**
     * @BeforeScenario
     */
    public function initialiseFixtureDetails()
    {
        $this->faker = Factory::create('en_GB');
        $this->testRunId = (string) (time() + rand());
        $this->resetCommonProperties();
    }

    private function resetCommonProperties()
    {
        $this->loggedInUserDetails = null;
        $this->interactingWithUserDetails = null;
        $this->submittedAnswersByFormSections = ['totals' => ['grandTotal' => 0]];
    }

    /**
     * @BeforeScenario @lay-pfa-high-not-started
     */
    public function createPfaHighNotStarted(?BeforeScenarioScope $scenario = null, ?string $caseNumber = null)
    {
        $userDetails = $this->fixtureHelper->createLayPfaHighAssetsNotStarted($this->testRunId, $caseNumber);
        $this->fixtureUsers[] = $this->layDeputyNotStartedPfaHighAssetsDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @lay-pfa-high-completed
     */
    public function createPfaHighCompleted()
    {
        $userDetails = $this->fixtureHelper->createLayPfaHighAssetsCompleted($this->testRunId);
        $this->fixtureUsers[] = $this->layDeputyCompletedPfaHighAssetsDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @lay-pfa-high-submitted
     */
    public function createPfaHighSubmitted()
    {
        $userDetails = $this->fixtureHelper->createLayPfaHighAssetsSubmitted($this->testRunId);
        $this->fixtureUsers[] = $this->layDeputySubmittedPfaHighAssetsDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @lay-pfa-low-not-started
     */
    public function createPfaLowNotStarted()
    {
        $userDetails = $this->fixtureHelper->createLayPfaLowAssetsNotStarted($this->testRunId);
        $this->fixtureUsers[] = $this->layDeputyNotStartedPfaLowAssetsDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @prof-pfa-low-not-started
     */
    public function createProfPfaLowNotStarted()
    {
        $userDetails = $this->fixtureHelper->createProfPfaLowAssetsNotStarted($this->testRunId);
        $this->fixtureUsers[] = $this->profAdminDeputyNotStartedPfaLowAssetsDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @lay-pfa-low-completed
     */
    public function createPfaLowCompleted()
    {
        $userDetails = $this->fixtureHelper->createLayPfaLowAssetsCompleted($this->testRunId);
        $this->fixtureUsers[] = $this->layDeputyCompletedPfaLowAssetsDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @prof-pfa-low-completed
     */
    public function createProfPfaLowCompleted()
    {
        $userDetails = $this->fixtureHelper->createProfPfaLowAssetsCompleted($this->testRunId);
        $this->fixtureUsers[] = $this->profAdminDeputyCompletedPfaLowAssetsDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @lay-health-welfare-not-started
     */
    public function createHealthWelfareNotStarted()
    {
        $userDetails = $this->fixtureHelper->createLayHealthWelfareNotStarted($this->testRunId);
        $this->fixtureUsers[] = $this->layDeputyNotStartedHealthWelfareDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @lay-health-welfare-completed
     */
    public function createHealthWelfareCompleted()
    {
        $userDetails = $this->fixtureHelper->createLayHealthWelfareCompleted($this->testRunId);
        $this->fixtureUsers[] = $this->layDeputyCompletedHealthWelfareDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @lay-combined-high-not-started
     */
    public function createLayCombinedHighNotStarted()
    {
        $userDetails = $this->fixtureHelper->createLayCombinedHighAssetsNotStarted($this->testRunId);
        $this->fixtureUsers[] = $this->layDeputyNotStartedCombinedHighDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @lay-combined-high-completed
     */
    public function createLayCombinedHighCompleted()
    {
        $userDetails = $this->fixtureHelper->createLayCombinedHighAssetsCompleted($this->testRunId);
        $this->fixtureUsers[] = $this->layDeputyCompletedCombinedHighDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @lay-combined-high-submitted
     */
    public function createLayCombinedHighSubmitted(?BeforeScenarioScope $obj, ?string $testRunId = null)
    {
        $userDetails = new UserDetails($this->fixtureHelper->createLayCombinedHighAssetsSubmitted($testRunId ?: $this->testRunId));
        $this->fixtureUsers[] = $this->layDeputySubmittedCombinedHighDetails = $userDetails;

        return $userDetails;
    }

    /**
     * @BeforeScenario @lay-health-welfare-submitted
     */
    public function createHealthWelfareSubmitted()
    {
        $userDetails = $this->fixtureHelper->createLayHealthWelfareSubmitted($this->testRunId);
        $this->fixtureUsers[] = $this->layDeputySubmittedHealthWelfareDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @prof-named-hw-not-started
     */
    public function createProfNamedHealthWelfareNotStarted()
    {
        $userDetails = $this->fixtureHelper->createProfNamedHealthWelfareNotStarted($this->testRunId);
        $this->fixtureUsers[] = $this->profNamedDeputyNotStartedHealthWelfareDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @prof-named-hw-completed
     */
    public function createProfNamedHealthWelfareCompleted()
    {
        $userDetails = $this->fixtureHelper->createProfNamedHealthWelfareCompleted($this->testRunId);
        $this->fixtureUsers[] = $this->profNamedDeputyCompletedHealthWelfareDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @prof-named-hw-submitted
     */
    public function createProfNamedHealthWelfareSubmitted()
    {
        $userDetails = $this->fixtureHelper->createProfNamedHealthWelfareSubmitted($this->testRunId);
        $this->fixtureUsers[] = $this->profNamedDeputySubmittedHealthWelfareDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @prof-named-pfa-high-not-started
     */
    public function createProfNamedPfaHighNotStarted()
    {
        $userDetails = $this->fixtureHelper->createProfNamedPfaHighNotStarted($this->testRunId);
        $this->fixtureUsers[] = $this->profNamedDeputyNotStartedPfaHighDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @prof-named-pfa-high-submitted
     */
    public function createProfNamedPfaHighSubmitted()
    {
        $userDetails = $this->fixtureHelper->createProfNamedPfaHighSubmitted($this->testRunId);
        $this->fixtureUsers[] = $this->profNamedDeputySubmittedPfaHighDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @pa-named-pfa-high-not-started
     */
    public function createPaNamedPfaHighNotStarted()
    {
        $userDetails = $this->fixtureHelper->createPaNamedPfaHighNotStarted($this->testRunId);
        $this->fixtureUsers[] = $this->publicAuthorityNamedNotStartedPfaHighDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @pa-named-pfa-high-submitted
     */
    public function createPaNamedPfaHighSubmitted()
    {
        $userDetails = $this->fixtureHelper->createPaNamedPfaHighSubmitted($this->testRunId);
        $this->fixtureUsers[] = $this->publicAuthorityNamedSubmittedPfaHighDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @prof-team-hw-not-started
     */
    public function createProfTeamHealthWelfareNotStarted()
    {
        $userDetails = $this->fixtureHelper->createProfTeamHealthWelfareNotStarted($this->testRunId);
        $this->fixtureUsers[] = $this->profTeamDeputyNotStartedHealthWelfareDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @prof-team-hw-completed
     */
    public function createProfTeamHealthWelfareCompleted()
    {
        $userDetails = $this->fixtureHelper->createProfTeamHealthWelfareCompleted($this->testRunId);
        $this->fixtureUsers[] = $this->profTeamDeputyCompletedHealthWelfareDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @ndr-not-started
     */
    public function createNdrNotStarted()
    {
        $userDetails = $this->fixtureHelper->createLayNdrNotStarted($this->testRunId);
        $this->fixtureUsers[] = $this->layNdrDeputyNotStartedDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @ndr-completed
     */
    public function createNdrCompleted()
    {
        $userDetails = $this->fixtureHelper->createLayNdrCompleted($this->testRunId);
        $this->fixtureUsers[] = $this->layNdrDeputyCompletedDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @prof-admin-health-welfare-not-started
     */
    public function createProfAdminNotStarted(?BeforeScenarioScope $scenario = null, ?string $deputyEmail = null, ?string $caseNumber = null, ?string $deputyUid = null)
    {
        $userDetails = $this->fixtureHelper->createProfAdminNotStarted($this->testRunId, $deputyEmail, $caseNumber, $deputyUid);
        $this->fixtureUsers[] = $this->profAdminDeputyHealthWelfareNotStartedDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @prof-admin-health-welfare-completed
     */
    public function createProfAdminCompleted(?BeforeScenarioScope $scenario = null, ?string $deputyEmail = null, ?string $caseNumber = null, ?string $deputyUid = null)
    {
        $userDetails = $this->fixtureHelper->createProfAdminCompleted($this->testRunId, $deputyEmail, $caseNumber, $deputyUid);
        $this->fixtureUsers[] = $this->profAdminDeputyHealthWelfareCompletedDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @prof-admin-health-welfare-submitted
     */
    public function createProfAdminSubmitted(?BeforeScenarioScope $scenario = null, ?string $deputyEmail = null, ?string $caseNumber = null, ?string $deputyUid = null)
    {
        $userDetails = $this->fixtureHelper->createProfAdminSubmitted($this->testRunId, $deputyEmail, $caseNumber, $deputyUid);
        $this->fixtureUsers[] = $this->profAdminDeputyHealthWelfareSubmittedDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @pa-named-health-welfare-not-started
     */
    public function createPaNamedNotStarted()
    {
        $userDetails = $this->fixtureHelper->createPaNamedHealthWelfareNotStarted($this->testRunId);
        $this->fixtureUsers[] = $this->publicAuthorityNamedDeputyNotStartedDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @pa-named-health-welfare-completed
     */
    public function createPaNamedCompleted()
    {
        $userDetails = $this->fixtureHelper->createPaNamedHealthWelfareCompleted($this->testRunId);
        $this->fixtureUsers[] = $this->publicAuthorityNamedDeputyCompletedDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @pa-named-health-welfare-submitted
     */
    public function createPaNamedSubmitted()
    {
        $userDetails = $this->fixtureHelper->createPaNamedHealthWelfareSubmitted($this->testRunId);
        $this->fixtureUsers[] = $this->publicAuthorityNamedDeputySubmittedDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @pa-admin-combined-high-not-started
     */
    public function createPaAdminCombinedHighNotStarted()
    {
        $userDetails = $this->fixtureHelper->createPaAdminCombinedHighNotStarted($this->testRunId);
        $this->fixtureUsers[] = $this->publicAuthorityAdminCombinedHighNotStartedDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @pa-admin-combined-high-completed
     */
    public function createPaAdminCombinedHighCompleted()
    {
        $userDetails = $this->fixtureHelper->createPaAdminCombinedHighCompleted($this->testRunId);
        $this->fixtureUsers[] = $this->publicAuthorityAdminCombinedHighCompletedDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @pa-admin-combined-high-submitted
     */
    public function createPaAdminCombinedHighSubmitted()
    {
        $userDetails = $this->fixtureHelper->createPaAdminCombinedHighSubmitted($this->testRunId);
        $this->fixtureUsers[] = $this->publicAuthorityAdminCombinedHighSubmittedDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @prof-admin-combined-high-not-started
     */
    public function createProfAdminCombinedHighNotStarted()
    {
        $userDetails = $this->fixtureHelper->createProfAdminCombinedHighNotStarted($this->testRunId);
        $this->fixtureUsers[] = $this->profAdminCombinedHighNotStartedDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @prof-admin-combined-high-completed
     */
    public function createProfAdminCombinedHighCompleted()
    {
        $userDetails = $this->fixtureHelper->createProfAdminCombinedHighCompleted($this->testRunId);
        $this->fixtureUsers[] = $this->profAdminCombinedHighCompletedDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @prof-admin-combined-high-submitted
     */
    public function createProfAdminCombinedHighSubmitted()
    {
        $userDetails = $this->fixtureHelper->createProfAdminCombinedHighSubmitted($this->testRunId);
        $this->fixtureUsers[] = $this->profAdminCombinedHighSubmittedDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @pa-admin-health-welfare-not-started
     */
    public function createPAAdminNotStarted()
    {
        $userDetails = $this->fixtureHelper->createPAAdminHealthWelfareNotStarted($this->testRunId);
        $this->fixtureUsers[] = $this->paAdminDeputyNotStartedDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @pa-admin-health-welfare-completed
     */
    public function createPAAdminCompleted()
    {
        $userDetails = $this->fixtureHelper->createPAAdminHealthWelfareCompleted($this->testRunId);
        $this->fixtureUsers[] = $this->paAdminDeputyCompletedDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @pa-admin-health-welfare-submitted
     */
    public function createPAAdminSubmitted()
    {
        $userDetails = $this->fixtureHelper->createPAAdminHealthWelfareSubmitted($this->testRunId);
        $this->fixtureUsers[] = $this->paAdminDeputySubmittedDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @admin
     */
    public function createAdmin()
    {
        $userDetails = $this->fixtureHelper->createAdmin($this->testRunId);
        $this->fixtureUsers[] = $this->adminDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @admin-manager
     */
    public function createAdminManager()
    {
        $userDetails = $this->fixtureHelper->createAdminManager($this->testRunId);
        $this->fixtureUsers[] = $this->adminManagerDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @super-admin
     */
    public function createSuperAdmin()
    {
        $userDetails = $this->fixtureHelper->createSuperAdmin($this->testRunId);
        $this->fixtureUsers[] = $this->superAdminDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @lay-pfa-high-not-started-legacy-password-hash
     */
    public function createPfaHighNotStartedLegacyPasswordHash(?BeforeScenarioScope $scenario = null, ?string $caseNumber = null)
    {
        $userDetails = $this->fixtureHelper->createLayPfaHighAssetsNotStartedLegacyPasswordHash($this->testRunId, $caseNumber);
        $this->fixtureUsers[] = $this->layDeputyNotStartedPfaHighAssetsDetails = new UserDetails($userDetails);
    }

    public function getAdminUrl(): string
    {
        return getenv('ADMIN_HOST');
    }

    public function getSiteUrl(): string
    {
        return getenv('NONADMIN_HOST');
    }

    public function visitFrontendPath(string $path)
    {
        $siteUrl = $this->getSiteUrl();
        $this->visitPath($siteUrl.$path);
    }

    public function visitAdminPath(string $path)
    {
        $adminUrl = $this->getAdminUrl();
        $this->visitPath($adminUrl.$path);
    }

    public function getPageContent(): string
    {
        if ($this->getSession()->getDriver() instanceof BrowserKitDriver) {
            return $this->getSession()->getPage()->getContent();
        } else {
            return $this->getSession()->getPage()->getText();
        }
    }

    public function getCurrentUrl(): string
    {
        return $this->getSession()->getCurrentUrl();
    }

    public function createAdditionalDataForAnalytics(string $timeAgo, int $runNumber, int $satisfactionScore)
    {
        $rndKey = mt_rand(0, 99999);

        return $this->fixtureHelper->createDataForAnalytics('a_'.$rndKey.$runNumber, $timeAgo, $satisfactionScore);
    }

    public function createAdditionalDataForUserSearchTests()
    {
        $this->fixtureHelper->createDataForAdminUserTests('search');
    }

    public function createAdditionalDataForUserEditTests()
    {
        $this->fixtureHelper->createDataForAdminUserTests('edit');
    }

    public function expireDocumentFromUnSubmittedDeputyReport(string $storageReference): void
    {
        $this->fixtureHelper->deleteFilesFromS3($storageReference);
    }

    /**
     * @BeforeScenario @multi-feature-flag-enabled
     */
    public function theMultiAccountFeatureFlagIsSetToTrue(): void
    {
        $this->parameterStoreService->putFeatureFlag(ParameterStoreService::FLAG_MULTI_ACCOUNTS, '1');
    }

    /**
     * @AfterScenario @multi-feature-flag-enabled
     */
    public function theMultiAccountFeatureFlagIsSetToFalse(): void
    {
        $this->parameterStoreService->putFeatureFlag(ParameterStoreService::FLAG_MULTI_ACCOUNTS, '0');
    }

    /**
     * @BeforeScenario @lay-pfa-high-not-started-multi-client-deputy
     */
    public function createLayPfaHighNotStartedMultiClientDeputy()
    {
        $deputyUid = 123456789000 + rand(1, 999);
        $primaryUserDetails = new UserDetails($this->fixtureHelper->createLayPfaHighAssetsNotStarted($this->testRunId, null, $deputyUid));
        $nonPrimaryUserDetails = new UserDetails($this->fixtureHelper->createLayPfaHighAssetsNonPrimaryUser($this->testRunId, null, $deputyUid));

        $this->fixtureUsers[] = $this->layPfaHighNotStartedMultiClientDeputyPrimaryUser = $primaryUserDetails;
        $this->fixtureUsers[] = $this->layPfaHighNotStartedMultiClientDeputyNonPrimaryUser = $nonPrimaryUserDetails;
    }

    /**
     * @BeforeScenario @lay-pfa-high-not-started-multi-client-deputy-discharged-client
     */
    public function createLayPfaHighNotStartedMultiClientDeputyWithDischargedClient()
    {
        $deputyUid = 123456789000 + rand(1, 999);
        $primaryUserDetails = new UserDetails($this->fixtureHelper->createLayPfaHighAssetsNotStarted($this->testRunId, null, $deputyUid));
        $nonPrimaryUserDetails = new UserDetails($this->fixtureHelper->createLayPfaHighAssetsNonPrimaryUser($this->testRunId, null, $deputyUid));
        $nonPrimaryUserDetails->setClientDeletedAt(new \DateTime());
      
        $this->fixtureUsers[] = $this->layPfaHighNotStartedMultiClientDeputyPrimaryUser = $primaryUserDetails;
        $this->fixtureUsers[] = $this->layPfaHighNotStartedMultiClientDeputyNonPrimaryUser = $nonPrimaryUserDetails;
    }

    /**
     * @BeforeScenario @lay-pfa-high-not-started-multi-client-deputy-with-ndr
     */
    public function createLayPfaHighNotStartedMultiClientDeputyWithNdr()
    {
        $deputyUid = 123456788000 + rand(1, 999);
        $primaryUserDetails = new UserDetails($this->fixtureHelper->createLayPfaHighAssetsNotStartedWithNdr($this->testRunId, null, $deputyUid));
        $nonPrimaryUserDetails = new UserDetails($this->fixtureHelper->createLayPfaHighAssetsNonPrimaryUser($this->testRunId, null, $deputyUid));

        $this->fixtureUsers[] = $this->layPfaHighNotStartedMultiClientDeputyPrimaryUser = $primaryUserDetails;
        $this->fixtureUsers[] = $this->layPfaHighNotStartedMultiClientDeputyNonPrimaryUser = $nonPrimaryUserDetails;
    }

    /**
     * @BeforeScenario @lay-pfa-high-started-multi-client-deputy-primary-client-discharged-two-active-clients
     */
    public function createLayPfaHighNotStartedMultiClientDeputyWithActiveAndDischargedClients()
    {
        $deputyUid = 123456789000 + rand(1, 999);

        // generate a second test run id for second non-primary user
        $testRunId = (string) (time() + rand());

        $primaryUserDetails = new UserDetails($this->fixtureHelper->createLayPfaHighAssetsNotStarted($this->testRunId, null, $deputyUid));
        $nonPrimaryUserDetailsOne = new UserDetails($this->fixtureHelper->createLayPfaHighAssetsNonPrimaryUser($this->testRunId, null, $deputyUid));
        $nonPrimaryUserDetailsTwo = new UserDetails($this->fixtureHelper->createLayPfaHighAssetsNonPrimaryUser($testRunId, null, $deputyUid));

        // Discharge client linked to primary account
        $primaryDischargedClientId = $primaryUserDetails->getClientId();
        $client = $this->em->getRepository(Client::class)->find($primaryDischargedClientId);
        $client->setDeletedAt(new \DateTime('now'));
        $this->em->persist($client);
        $this->em->flush();

        $this->fixtureUsers[] = $this->layPfaHighNotStartedMultiClientDeputyPrimaryUser = $primaryUserDetails;
        $this->fixtureUsers[] = $this->layPfaHighNotStartedMultiClientDeputyNonPrimaryUser = $nonPrimaryUserDetailsOne;
        $this->fixtureUsers[] = $this->layPfaHighNotStartedMultiClientDeputySecondNonPrimaryUser = $nonPrimaryUserDetailsTwo;
    }
}
