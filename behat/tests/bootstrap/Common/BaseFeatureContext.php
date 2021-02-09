<?php

namespace DigidepsBehat\Common;

use Behat\MinkExtension\Context\MinkContext;

class BaseFeatureContext extends MinkContext
{
    use AuthenticationTrait;
    use DebugTrait;
    use FormTrait;
    use SiteNavigationTrait;

    const ADMIN = 'admin@publicguardian.gov.uk';
    const SUPER_ADMIN = 'super-admin@publicguardian.gov.uk';
    const BEHAT_AUTH_ENDPOINT = '/v2/fixture/auth-as';
    const BEHAT_ADMIN_AUTH_ENDPOINT = '/admin/fixtures/auth-as';
    const BEHAT_FRONT_AUTH_ENDPOINT = '/front/behat/auth-as';
    const BEHAT_GET_ACTIVE_REPORT_ID_ENDPOINT = '/v2/fixture/get-active-report-id';
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

    public function makeApiCall($path)
    {
        $this->authAs(self::SUPER_ADMIN);
        $this->visitPath(sprintf('%s%s', $this->getApiUrl(), $path));
    }

    /**
     * @Given :email logs in
     */
    public function authAs(string $email)
    {
        $url = sprintf('%s%s?email=%s', $this->getSiteUrl(), self::BEHAT_FRONT_AUTH_ENDPOINT, $email);
        $this->visitPath($url);

        $authToken = json_decode($this->getSession()->getPage()->getContent(), true)['AuthToken'];
        $activeReportId = json_decode($this->getSession()->getPage()->getContent(), true)['ActiveReportId'];
        $userId = json_decode($this->getSession()->getPage()->getContent(), true)['UserId'];

        var_dump($activeReportId);
        var_dump($userId);
        var_dump($authToken);

        $this->getSession()->setCookie('AuthToken', $authToken);
        $this->getSession()->setCookie('ActiveReportId', $activeReportId);
        $this->getSession()->setCookie('UserId', $userId);
    }

    /**
     * @Given I view the :sectionName report section
     */
    public function iViewSection(string $sectionName)
    {
        $authToken = $this->getSession()->getCookie('AuthToken');
        $activeReportId = $this->getSession()->getCookie('ActiveReportId');
        $userId = $this->getSession()->getCookie('UserId');
        var_dump($activeReportId);
        var_dump($userId);
        var_dump($authToken);

        $this->getSession()->setRequestHeader('AuthToken', $authToken);

        $frontendUrl = sprintf($this->getSiteUrl() . '/' . self::REPORT_SECTION_ENDPOINT, $activeReportId, $sectionName);
        var_dump($frontendUrl);

        $this->visitPath($frontendUrl);
//        $this->visitPath('/lay');
//        var_dump(json_decode($this->getSession()->getPage()->getContent(), true));
    }
}
