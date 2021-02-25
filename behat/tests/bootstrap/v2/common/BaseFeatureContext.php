<?php declare(strict_types=1);


namespace DigidepsBehat\v2\Common;

use Behat\Mink\Driver\GoutteDriver;
use Behat\MinkExtension\Context\MinkContext;
use Exception;
use Symfony\Component\Serializer\Serializer;

class BaseFeatureContext extends MinkContext
{
    use AuthTrait;
    use CourtOrderTrait;
    use DebugTrait;
    use ReportTrait;

    const BEHAT_FRONT_RESET_FIXTURES = '/behat/frontend/reset-fixtures?testRunId=%s';
    const BEHAT_FRONT_USER_DETAILS = '/behat/frontend/user/%s/details';

    public UserDetails $adminDetails;
    public UserDetails $superAdminDetails;

    public UserDetails $layDeputyNotStartedDetails;
    public UserDetails $layDeputyCompletedNotSubmittedDetails;
    public UserDetails $layDeputySubmittedDetails;

    public string $testRunId = '';

    /**
     * @BeforeScenario
     */
    public function resetFixturesAndDropDatabase()
    {
        $this->testRunId = (string) (time() + rand());
        $this->visitAdminPath(sprintf(self::BEHAT_FRONT_RESET_FIXTURES, $this->testRunId));

        $responseData = json_decode($this->getPageContent(), true);

        $fixturesLoaded = preg_match('/Behat fixtures loaded/', $responseData['response']);

        if (!$fixturesLoaded) {
            throw new Exception($responseData['response']);
        }

        $this->adminDetails = new UserDetails($responseData['data']['admin-users']['admin']);
        var_dump($this->adminDetails);
        $this->superAdminDetails = new UserDetails($responseData['data']['admin-users']['super-admin']);
        var_dump($this->superAdminDetails);


        $this->layDeputyNotStartedDetails = new UserDetails($responseData['data']['lays']['not-started']);
        var_dump($this->layDeputyNotStartedDetails);

        $this->layDeputyCompletedNotSubmittedDetails = new UserDetails($responseData['data']['lays']['completed-not-submitted']);
        var_dump($this->layDeputyCompletedNotSubmittedDetails);

        $this->layDeputySubmittedDetails = new UserDetails($responseData['data']['lays']['submitted']);
        var_dump($this->layDeputySubmittedDetails);
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
}
