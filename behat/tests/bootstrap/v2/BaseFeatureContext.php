<?php

namespace DigidepsBehat\v2;

use Behat\MinkExtension\Context\MinkContext;
use Exception;

class BaseFeatureContext extends MinkContext
{
    use DebugTrait;

    const ADMIN = 'admin@publicguardian.gov.uk';
    const SUPER_ADMIN = 'super-admin@publicguardian.gov.uk';

    const BEHAT_ADMIN_RESET_FIXTURES = '/admin/behat/reset-fixtures';

    const BEHAT_FRONT_USER_DETAILS = '/behat/frontend/user/%s/details';

    const REPORT_SECTION_ENDPOINT = 'report/%s/%s';

    protected static $dbName = 'api';

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

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return getenv('API_HOST');
    }

    public function visitAdminPath(string $path)
    {
        $adminUrl = $this->getAdminUrl();
        $this->visitPath($adminUrl . $path);
    }

    /**
     * @Given :email logs in
     */
    public function loginToFrontendAs(string $email)
    {
        $this->visitPath('/logout');
        $this->visitPath('/login');
        $this->fillField('login_email', $email);
        $this->fillField('login_password', 'Abcd1234');
        $this->pressButton('login_login');

        $this->visitPath(sprintf(self::BEHAT_FRONT_USER_DETAILS, $email));

        $activeReportId = json_decode($this->getSession()->getPage()->getContent(), true)['ActiveReportId'];
        $userId = json_decode($this->getSession()->getPage()->getContent(), true)['UserId'];

        $this->getSession()->setCookie('ActiveReportId', $activeReportId);
        $this->getSession()->setCookie('UserId', $userId);
    }

    /**
     * @Given :email logs in to admin
     */
    public function loginToAdminAs(string $email)
    {
        $this->visitAdminPath('/logout');
        $this->visitAdminPath('/login');
        $this->fillField('login_email', $email);
        $this->fillField('login_password', 'Abcd1234');
        $this->pressButton('login_login');
    }

    /**
     * @Given I view and start the contacts report section
     */
    public function iViewContactsSection()
    {
        $activeReportId = $this->getSession()->getCookie('ActiveReportId');
        $userId = $this->getSession()->getCookie('UserId');
        var_dump($activeReportId);
        var_dump($userId);

        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $activeReportId, 'contacts');
        var_dump($reportSectionUrl);

        $this->visitPath($reportSectionUrl);

        $currentUrl = $this->getSession()->getCurrentUrl();
        $onSummaryPage = preg_match('/report\/.*\/contacts$/', $currentUrl);

        if (!$onSummaryPage) {
            throw new Exception(sprintf('Not on contacts start page. Current URL is: %s', $currentUrl));
        }

        $this->clickLink('Start contacts');
    }

    /**
     * @Then I should be on the contacts summary page
     */
    public function iAmOnContactsSummaryPage()
    {
        $currentUrl = $this->getSession()->getCurrentUrl();
        $onSummaryPage = preg_match('/report\/.*\/contacts\/summary$/', $currentUrl);

        if (!$onSummaryPage) {
            throw new Exception(sprintf('Not on contacts summary page. Current URL is: %s', $currentUrl));
        }
    }

    /**
     * @BeforeScenario
     */
    public function clearData()
    {
        $this->loginToAdminAs(self::SUPER_ADMIN);
        $this->visitAdminPath(self::BEHAT_ADMIN_RESET_FIXTURES);
        $pageContent = $this->getSession()->getPage()->getContent();

        $fixturesLoaded = preg_match('/^Behat fixtures loaded$/', $pageContent);

        if (!$fixturesLoaded) {
            throw new Exception($pageContent);
        }
    }
}
