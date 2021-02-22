<?php declare(strict_types=1);


namespace DigidepsBehat\v2\Common;

use Behat\Mink\Driver\GoutteDriver;
use Behat\MinkExtension\Context\MinkContext;
use Exception;

class BaseFeatureContext extends MinkContext
{
    use AuthTrait;
    use CourtOrderTrait;
    use DebugTrait;

    const BEHAT_FRONT_RESET_FIXTURES = '/behat/frontend/reset-fixtures?testRunId=%s';
    const BEHAT_FRONT_USER_DETAILS = '/behat/frontend/user/%s/details';

    private string $adminEmail = '';
    private string $superAdminEmail = '';

    private string $layDeputyNotStartedEmail = '';
    private string $layDeputyCompletedNotSubmittedEmail = '';
    private string $layDeputySubmittedEmail = '';

    private string $testRunId = '';

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

        $this->adminEmail = $responseData['data']['admin'];
        $this->superAdminEmail = $responseData['data']['super-admin'];

        $this->layDeputyNotStartedEmail = $responseData['data']['lay-not-started'];
        $this->layDeputyCompletedNotSubmittedEmail = $responseData['data']['lay-completed-not-submitted'];
        $this->layDeputySubmittedEmail = $responseData['data']['lay-submitted'];
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
