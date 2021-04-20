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
    use AuthTrait;
    use AssertTrait;
    use CourtOrderTrait;
    use DebugTrait;
    use ReportTrait;
    use IShouldBeOnTrait;
    use PageUrlsTrait;
    use ElementSelectionTrait;
    use ErrorsTrait;
    use AlertsTrait;
    use IVisitTrait;

    const BEHAT_FRONT_RESET_FIXTURES = '/behat/frontend/reset-fixtures?testRunId=%s';
    const BEHAT_FRONT_USER_DETAILS = '/behat/frontend/user/%s/details';

    public UserDetails $adminDetails;
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

    public UserDetails $loggedInUserDetails;

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
        $this->fixtureUsers[] = $this->superAdminDetails = new UserDetails($responseData['data']['admin-users']['super-admin']);
        $this->fixtureUsers[] = $this->layDeputyNotStartedDetails = new UserDetails($responseData['data']['lays']['not-started']);
        $this->fixtureUsers[] = $this->layDeputyCompletedDetails = new UserDetails($responseData['data']['lays']['completed']);
        $this->fixtureUsers[] = $this->layDeputySubmittedDetails = new UserDetails($responseData['data']['lays']['submitted']);
        $this->fixtureUsers[] = $this->ndrLayDeputyNotStartedDetails = new UserDetails($responseData['data']['lays-ndr']['not-started']);
        $this->fixtureUsers[] = $this->ndrLayDeputyCompletedDetails = new UserDetails($responseData['data']['lays-ndr']['completed']);
        $this->fixtureUsers[] = $this->ndrLayDeputySubmittedDetails = new UserDetails($responseData['data']['lays-ndr']['submitted']);
        $this->fixtureUsers[] = $this->profAdminDeputyNotStartedDetails = new UserDetails($responseData['data']['professionals']['admin']['not-started']);
        $this->fixtureUsers[] = $this->profAdminDeputyCompletedDetails = new UserDetails($responseData['data']['professionals']['admin']['completed']);
        $this->fixtureUsers[] = $this->profAdminDeputySubmittedDetails = new UserDetails($responseData['data']['professionals']['admin']['submitted']);
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
        $loggedInEmail = !isset($this->loggedInUserDetails) ? 'Not logged in' : $this->loggedInUserDetails->getEmail();

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
