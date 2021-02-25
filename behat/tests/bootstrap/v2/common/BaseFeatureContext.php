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
    use CourtOrderTrait;
    use DebugTrait;
    use ReportTrait;
    use IShouldBeOnTrait;

    const BEHAT_FRONT_RESET_FIXTURES = '/behat/frontend/reset-fixtures?testRunId=%s';
    const BEHAT_FRONT_USER_DETAILS = '/behat/frontend/user/%s/details';

    public UserDetails $adminDetails;
    public UserDetails $superAdminDetails;

    public UserDetails $layDeputyNotStartedDetails;
    public UserDetails $layDeputyCompletedNotSubmittedDetails;
    public UserDetails $layDeputySubmittedDetails;

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
        $this->fixtureUsers[] = $this->layDeputyCompletedNotSubmittedDetails = new UserDetails($responseData['data']['lays']['completed-not-submitted']);
        $this->fixtureUsers[] = $this->layDeputySubmittedDetails = new UserDetails($responseData['data']['lays']['submitted']);
        var_dump($this->fixtureUsers);
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

    public function iAmOnPage(string $urlRegex)
    {
        $currentUrl = $this->getSession()->getCurrentUrl();
        $onExpectedPage = preg_match($urlRegex, $currentUrl);

        if (!$onExpectedPage) {
            $this->throwContextualException(sprintf('Not on expected page. Current URL is: %s', $currentUrl));
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
}
