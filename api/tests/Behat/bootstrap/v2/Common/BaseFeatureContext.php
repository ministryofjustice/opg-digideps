<?php

declare(strict_types=1);

namespace DigidepsBehat\v2\Common;

use Behat\Mink\Driver\GoutteDriver;
use Behat\MinkExtension\Context\MinkContext;
use DigidepsBehat\BehatException;
use DigidepsBehat\v2\Helpers\FixtureHelper;
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
    use FixturesTrait;
    use INavigateToAdminTrait;
    use IShouldBeOnTrait;
    use IVisitAdminTrait;
    use IVisitFrontendTrait;
    use PageUrlsTrait;
    use ReportTrait;

    public const BEHAT_FRONT_RESET_FIXTURES = '/behat/frontend/reset-fixtures?testRunId=%s';
    public const BEHAT_FRONT_USER_DETAILS = '/behat/frontend/user/%s/details';
    public const REPORT_SECTION_ENDPOINT = '/%s/%s/%s';

    public UserDetails $adminDetails;
    public UserDetails $elevatedAdminDetails;
    public UserDetails $superAdminDetails;

    public UserDetails $layDeputyNotStartedDetails;
    public UserDetails $layDeputyCompletedDetails;
    public UserDetails $layDeputySubmittedDetails;

    public UserDetails $profAdminDeputyNotStartedDetails;
    public UserDetails $profAdminDeputyCompletedDetails;
    public UserDetails $profAdminDeputySubmittedDetails;

    public UserDetails $ndrLayDeputyNotStartedDetails;
    public UserDetails $ndrLayDeputyCompletedDetails;
    public UserDetails $ndrLayDeputySubmittedDetails;

    public ?UserDetails $loggedInUserDetails = null;
    public ?UserDetails $interactingWithUserDetails = null;

    public array $fixtureUsers = [];

    public string $testRunId = '';

    public Generator $faker;

    private KernelInterface $symfonyKernel;

    private FixtureHelper $fixtureHelper;

    public function __construct(KernelInterface $symfonyKernel, FixtureHelper $fixtureHelper)
    {
        $this->symfonyKernel = $symfonyKernel;

        if ('prod' === $this->symfonyKernel->getEnvironment()) {
            throw new Exception('Unable to run behat tests in prod mode. Change the apps mode to dev or test and try again');
        }

        $this->fixtureHelper = $fixtureHelper;
    }

    /**
     * @BeforeScenario
     */
    public function resetFixturesAndDropDatabase()
    {
        $this->faker = Factory::create('en_GB');

        $this->testRunId = (string) (time() + rand());
        $userDetails = $this->fixtureHelper->loadFixtures($this->testRunId);

        $this->fixtureUsers[] = $this->adminDetails = new UserDetails($userDetails['admin-users']['admin']);
        $this->fixtureUsers[] = $this->elevatedAdminDetails = new UserDetails($userDetails['admin-users']['elevated-admin']);
        $this->fixtureUsers[] = $this->superAdminDetails = new UserDetails($userDetails['admin-users']['super-admin']);
        $this->fixtureUsers[] = $this->layDeputyNotStartedDetails = new UserDetails($userDetails['lays']['not-started']);
        $this->fixtureUsers[] = $this->layDeputyCompletedDetails = new UserDetails($userDetails['lays']['completed']);
        $this->fixtureUsers[] = $this->layDeputySubmittedDetails = new UserDetails($userDetails['lays']['submitted']);
        $this->fixtureUsers[] = $this->ndrLayDeputyNotStartedDetails = new UserDetails($userDetails['lays-ndr']['not-started']);
        $this->fixtureUsers[] = $this->ndrLayDeputyCompletedDetails = new UserDetails($userDetails['lays-ndr']['completed']);
        $this->fixtureUsers[] = $this->ndrLayDeputySubmittedDetails = new UserDetails($userDetails['lays-ndr']['submitted']);
        $this->fixtureUsers[] = $this->profAdminDeputyNotStartedDetails = new UserDetails($userDetails['professionals']['admin']['not-started']);
        $this->fixtureUsers[] = $this->profAdminDeputyCompletedDetails = new UserDetails($userDetails['professionals']['admin']['completed']);
        $this->fixtureUsers[] = $this->profAdminDeputySubmittedDetails = new UserDetails($userDetails['professionals']['admin']['submitted']);

        $this->loggedInUserDetails = null;
        $this->interactingWithUserDetails = null;
    }

    public function getAdminUrl(): string
    {
        return getenv('ADMIN_HOST');
    }

    /**
     * @return string
     */
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
