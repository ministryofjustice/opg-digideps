<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use App\Tests\Behat\BehatException;
use App\Tests\Behat\v2\Helpers\FixtureHelper;
use Behat\Mink\Driver\GoutteDriver;
use Behat\MinkExtension\Context\MinkContext;
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
    use INavigateToAdminTrait;
    use IShouldBeOnTrait;
    use IVisitAdminTrait;
    use IVisitFrontendTrait;
    use PageUrlsTrait;
    use ReportTrait;

    public const REPORT_SECTION_ENDPOINT = '/%s/%s/%s';

    public UserDetails $adminDetails;
    public UserDetails $elevatedAdminDetails;
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

    private FixtureHelper $fixtureHelper;

    public function __construct(
        FixtureHelper $fixtureHelper,
        KernelInterface $symfonyKernel
    ) {
        $this->symfonyKernel = $symfonyKernel;

        if ('prod' === $this->symfonyKernel->getEnvironment()) {
            throw new Exception('Unable to run behat tests in prod mode. Change the apps mode to dev or test and try again');
        }

        $this->fixtureHelper = $fixtureHelper;
    }

//    /**
//     * @BeforeScenario
//     */
//    public function resetFixturesAndDropDatabase()
//    {
//        $this->faker = Factory::create('en_GB');
//
//        $this->testRunId = (string) (time() + rand());
//        $userDetails = $this->fixtureHelper->loadFixtures($this->testRunId);
//
//        $this->fixtureUsers[] = $this->adminDetails = new UserDetails($userDetails['admin-users']['admin']);
//        $this->fixtureUsers[] = $this->elevatedAdminDetails = new UserDetails($userDetails['admin-users']['elevated-admin']);
//        $this->fixtureUsers[] = $this->superAdminDetails = new UserDetails($userDetails['admin-users']['super-admin']);
//        $this->fixtureUsers[] = $this->layDeputyNotStartedPfaHighAssetsDetails = new UserDetails($userDetails['lays']['pfa-high-assets']['not-started']);
//        $this->fixtureUsers[] = $this->layDeputyCompletedPfaHighAssetsDetails = new UserDetails($userDetails['lays']['pfa-high-assets']['completed']);
//        $this->fixtureUsers[] = $this->layDeputySubmittedPfaHighAssetsDetails = new UserDetails($userDetails['lays']['pfa-high-assets']['submitted']);
//        $this->fixtureUsers[] = $this->layDeputyNotStartedPfaLowAssetsDetails = new UserDetails($userDetails['lays']['pfa-low-assets']['not-started']);
//        $this->fixtureUsers[] = $this->layDeputyCompletedPfaLowAssetsDetails = new UserDetails($userDetails['lays']['pfa-low-assets']['completed']);
//        $this->fixtureUsers[] = $this->layDeputySubmittedPfaLowAssetsDetails = new UserDetails($userDetails['lays']['pfa-low-assets']['submitted']);
//        $this->fixtureUsers[] = $this->layNdrDeputyNotStartedDetails = new UserDetails($userDetails['lays-ndr']['not-started']);
//        $this->fixtureUsers[] = $this->layNdrDeputyCompletedDetails = new UserDetails($userDetails['lays-ndr']['completed']);
//        $this->fixtureUsers[] = $this->layNdrDeputySubmittedDetails = new UserDetails($userDetails['lays-ndr']['submitted']);
//        $this->fixtureUsers[] = $this->profAdminDeputyNotStartedDetails = new UserDetails($userDetails['professionals']['admin']['not-started']);
//        $this->fixtureUsers[] = $this->profAdminDeputyCompletedDetails = new UserDetails($userDetails['professionals']['admin']['completed']);
//        $this->fixtureUsers[] = $this->profAdminDeputySubmittedDetails = new UserDetails($userDetails['professionals']['admin']['submitted']);
//
//        $this->loggedInUserDetails = null;
//        $this->interactingWithUserDetails = null;
//    }

    /**
     * @BeforeScenario
     */
    public function resetFixturesAndDropDatabase()
    {
        $this->faker = Factory::create('en_GB');
        $this->testRunId = (string) (time() + rand());
        $this->loggedInUserDetails = null;
        $this->interactingWithUserDetails = null;
    }

    /**
     * @BeforeScenario @pfa-high-not-started
     */
    public function createPfaHighNotStarted()
    {
        $userDetails = $this->fixtureHelper->createLayPfaHighAssetsNotStarted($this->testRunId);
        $this->fixtureUsers[] = $this->layDeputyNotStartedPfaHighAssetsDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @pfa-high-completed
     */
    public function createPfaHighCompleted()
    {
        $userDetails = $this->fixtureHelper->createLayPfaHighAssetsCompleted($this->testRunId);
        $this->fixtureUsers[] = $this->layDeputyCompletedPfaHighAssetsDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @pfa-high-submitted
     */
    public function createPfaHighSubmitted()
    {
        $userDetails = $this->fixtureHelper->createLayPfaHighAssetsSubmitted($this->testRunId);
        $this->fixtureUsers[] = $this->layDeputySubmittedPfaHighAssetsDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @pfa-low-not-started
     */
    public function createPfaLowNotStarted()
    {
        $userDetails = $this->fixtureHelper->createLayPfaLowAssetsNotStarted($this->testRunId);
        $this->fixtureUsers[] = $this->layDeputyNotStartedPfaLowAssetsDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @pfa-low-completed
     */
    public function createPfaLowCompleted()
    {
        $userDetails = $this->fixtureHelper->createLayPfaLowAssetsCompleted($this->testRunId);
        $this->fixtureUsers[] = $this->layDeputyCompletedPfaLowAssetsDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @health-welfare-not-started
     */
    public function createHealthWelfareNotStarted()
    {
        $userDetails = $this->fixtureHelper->createLayHealthWelfareNotStarted($this->testRunId);
        $this->fixtureUsers[] = $this->layDeputyNotStartedHealthWelfareDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @health-welfare-completed
     */
    public function createHealthWelfareCompleted()
    {
        $userDetails = $this->fixtureHelper->createLayHealthWelfareCompleted($this->testRunId);
        $this->fixtureUsers[] = $this->layDeputyCompletedHealthWelfareDetails = new UserDetails($userDetails);
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
    public function createProfAdminNotStarted()
    {
        $userDetails = $this->fixtureHelper->createProfAdminNotStarted($this->testRunId);
        $this->fixtureUsers[] = $this->profAdminDeputyNotStartedDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @prof-admin-completed
     */
    public function createProfAdminCompleted()
    {
        $userDetails = $this->fixtureHelper->createProfAdminCompleted($this->testRunId);
        $this->fixtureUsers[] = $this->profAdminDeputyCompletedDetails = new UserDetails($userDetails);
    }

    /**
     * @BeforeScenario @prof-admin-submitted
     */
    public function createProfAdminSubmitted()
    {
        $userDetails = $this->fixtureHelper->createProfAdminSubmitted($this->testRunId);
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
     * @BeforeScenario @elevated-admin
     */
    public function createElevatedAdmin()
    {
        $userDetails = $this->fixtureHelper->createElevatedAdmin($this->testRunId);
        $this->fixtureUsers[] = $this->elevatedAdminDetails = new UserDetails($userDetails);
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

    public function getSiteUrl()
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

    public function getPageContent()
    {
        if ($this->getSession()->getDriver() instanceof GoutteDriver) {
            return $this->getSession()->getPage()->getContent();
        } else {
            return $this->getSession()->getPage()->getText();
        }
    }

    public function throwContextualException(string $message)
    {
        $loggedInEmail = !isset($this->loggedInUserDetails) ? 'Not logged in' : $this->loggedInUserDetails->getUserEmail();

        $contextMessage = <<<CONTEXT
$message

Logged in user is: $loggedInEmail
Test run ID is: $this->testRunId
CONTEXT;

        throw new BehatException($contextMessage);
    }

    public function getCurrentUrl()
    {
        return $this->getSession()->getCurrentUrl();
    }
}
