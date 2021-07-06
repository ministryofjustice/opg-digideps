<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use App\Tests\Behat\BehatException;
use App\Tests\Behat\v2\Helpers\FixtureHelper;
use Behat\Behat\Hook\Call\BeforeScenario;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Driver\GoutteDriver;
use Behat\MinkExtension\Context\MinkContext;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\HttpKernel\KernelInterface;

class BaseFeatureContext extends MinkContext
{
    use AlertsTrait;
    use AuthTrait;
    use AssertTrait;
    use DebugTrait;
    use ElementSelectionTrait;
    use ErrorsTrait;
    use ExpectedResultsTrait;
    use FixturesTrait;
    use FormFillingTrait;
    use INavigateToAdminTrait;
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

    public UserDetails $profNamedDeputyNotStartedHealthWelfareDetails;
    public UserDetails $profNamedDeputyCompletedHealthWelfareDetails;
    public UserDetails $profNamedDeputySubmittedHealthWelfareDetails;

    public UserDetails $profTeamDeputyNotStartedHealthWelfareDetails;
    public UserDetails $profTeamDeputyCompletedHealthWelfareDetails;
    public UserDetails $profTeamDeputySubmittedHealthWelfareDetails;

    public UserDetails $profAdminDeputyNotStartedDetails;
    public UserDetails $profAdminDeputyCompletedDetails;
    public UserDetails $profAdminDeputySubmittedDetails;

    public UserDetails $layNdrDeputyNotStartedDetails;
    public UserDetails $layNdrDeputyCompletedDetails;
    public UserDetails $layNdrDeputySubmittedDetails;

    public ?UserDetails $loggedInUserDetails = null;
    public ?UserDetails $interactingWithUserDetails = null;

    public array $fixtureUsers = [];

    public string $testRunId = '';

    public Generator $faker;

    private KernelInterface $symfonyKernel;

    protected FixtureHelper $fixtureHelper;
    public EntityManagerInterface $em;

    public function __construct(
        FixtureHelper $fixtureHelper,
        KernelInterface $symfonyKernel,
        EntityManagerInterface $em
    ) {
        $this->symfonyKernel = $symfonyKernel;

        if ('prod' === $this->symfonyKernel->getEnvironment()) {
            throw new Exception('Unable to run behat tests in prod mode. Change the apps mode to dev or test and try again');
        }

        $this->fixtureHelper = $fixtureHelper;
        $this->em = $em;
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
        $this->submittedAnswersByFormSections = [];
    }

    /**
     * @BeforeScenario @lay-pfa-high-not-started
     */
    public function createPfaHighNotStarted(?string $caseNumber = null)
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
     * @BeforeScenario @lay-pfa-low-completed
     */
    public function createPfaLowCompleted()
    {
        $userDetails = $this->fixtureHelper->createLayPfaLowAssetsCompleted($this->testRunId);
        $this->fixtureUsers[] = $this->layDeputyCompletedPfaLowAssetsDetails = new UserDetails($userDetails);
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
     * @BeforeScenario @prof-admin-not-started
     */
    public function createProfAdminNotStarted(?BeforeScenarioScope $scenario = null, ?string $namedDeputyEmail = null, ?string $caseNumber = null, ?string $deputyNumber = null)
    {
        $userDetails = $this->fixtureHelper->createProfAdminNotStarted($this->testRunId, $namedDeputyEmail, $caseNumber, $deputyNumber);
        $this->fixtureUsers[] = $this->profAdminDeputyNotStartedDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @prof-admin-completed
     */
    public function createProfAdminCompleted(?BeforeScenarioScope $scenario = null, ?string $namedDeputyEmail = null, ?string $caseNumber = null, ?string $deputyNumber = null)
    {
        $userDetails = $this->fixtureHelper->createProfAdminCompleted($this->testRunId, $namedDeputyEmail, $caseNumber, $deputyNumber);
        $this->fixtureUsers[] = $this->profAdminDeputyCompletedDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @prof-admin-submitted
     */
    public function createProfAdminSubmitted(?BeforeScenarioScope $scenario = null, ?string $namedDeputyEmail = null, ?string $caseNumber = null, ?string $deputyNumber = null)
    {
        $userDetails = $this->fixtureHelper->createProfAdminSubmitted($this->testRunId, $namedDeputyEmail, $caseNumber, $deputyNumber);
        $this->fixtureUsers[] = $this->profAdminDeputySubmittedDetails = new UserDetails($userDetails);
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
        if ($this->getSession()->getDriver() instanceof GoutteDriver) {
            return $this->getSession()->getPage()->getContent();
        } else {
            return $this->getSession()->getPage()->getText();
        }
    }

    public function throwContextualException(string $message)
    {
        throw new BehatException($message);
    }

    public function getCurrentUrl(): string
    {
        return $this->getSession()->getCurrentUrl();
    }
}
