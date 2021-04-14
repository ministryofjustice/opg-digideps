<?php declare(strict_types=1);


namespace DigidepsBehat\v2\Common;

use Behat\Mink\Driver\GoutteDriver;
use Behat\MinkExtension\Context\MinkContext;
use DigidepsBehat\BehatException;
use Exception;
use Faker\Factory;
use Faker\Generator;

class BaseFeatureContext extends MinkContext
{
    use AlertsTrait;
    use AuthTrait;
    use DebugTrait;
    use ElementSelectionTrait;
    use ErrorsTrait;
    use FixturesTrait;
    use IShouldBeOnTrait;
    use INavigateToTrait;
    use IVisitAdminTrait;
    use IVisitFrontendTrait;
    use PageUrlsTrait;
    use ReportTrait;

    const BEHAT_FRONT_RESET_FIXTURES = '/behat/frontend/reset-fixtures?testRunId=%s';
    const BEHAT_FRONT_USER_DETAILS = '/behat/frontend/user/%s/details';

    public UserDetails $adminDetails;
    public UserDetails $elevatedAdminDetails;
    public UserDetails $superAdminDetails;

    public UserDetails $layDeputyNotStartedDetails;
    public UserDetails $layDeputyCompletedDetails;
    public UserDetails $layDeputySubmittedDetails;

    /** @var UserDetails $profAdminDeputyNotStartedDetails */
    public UserDetails $profAdminDeputyNotStartedDetails;
    public UserDetails $profAdminDeputyCompletedDetails;
    public UserDetails $profAdminDeputySubmittedDetails;

    public ?UserDetails $loggedInUserDetails = null;
    public ?UserDetails $interactingWithUserDetails = null;

    public array $fixtureUsers = [];

    public string $testRunId = '';

    public Generator $faker;

    /**
     * @BeforeScenario
     */
    public function resetFixturesAndDropDatabase()
    {
        $this->faker = Factory::create('en_GB');

        $this->testRunId = (string) (time() + rand());
        $this->visitAdminPath(sprintf(self::BEHAT_FRONT_RESET_FIXTURES, $this->testRunId));

        $responseData = json_decode($this->getPageContent(), true);

        $fixturesLoaded = preg_match('/Behat fixtures loaded/', $responseData['response']);

        if (!$fixturesLoaded) {
            throw new Exception($responseData['response']);
        }

        $this->fixtureUsers[] = $this->adminDetails = new UserDetails($responseData['data']['admin-users']['admin']);
        $this->fixtureUsers[] = $this->elevatedAdminDetails = new UserDetails($responseData['data']['admin-users']['elevated-admin']);
        $this->fixtureUsers[] = $this->superAdminDetails = new UserDetails($responseData['data']['admin-users']['super-admin']);
        $this->fixtureUsers[] = $this->layDeputyNotStartedDetails = new UserDetails($responseData['data']['lays']['not-started']);
        $this->fixtureUsers[] = $this->layDeputyCompletedDetails = new UserDetails($responseData['data']['lays']['completed']);
        $this->fixtureUsers[] = $this->layDeputySubmittedDetails = new UserDetails($responseData['data']['lays']['submitted']);
        $this->fixtureUsers[] = $this->profAdminDeputyNotStartedDetails = new UserDetails($responseData['data']['professionals']['admin']['not-started']);
        $this->fixtureUsers[] = $this->profAdminDeputyCompletedDetails = new UserDetails($responseData['data']['professionals']['admin']['completed']);
        $this->fixtureUsers[] = $this->profAdminDeputySubmittedDetails = new UserDetails($responseData['data']['professionals']['admin']['submitted']);

        $this->loggedInUserDetails = null;
        $this->interactingWithUserDetails = null;
    }

    /**
     * @return string
     */
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
        $this->visitPath($siteUrl . $path);
    }

    public function visitAdminPath(string $path)
    {
        $adminUrl = $this->getAdminUrl();
        $this->visitPath($adminUrl . $path);
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
