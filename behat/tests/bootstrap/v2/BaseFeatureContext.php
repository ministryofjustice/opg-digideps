<?php declare(strict_types=1);

namespace DigidepsBehat\v2;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\GoutteDriver;
use Behat\MinkExtension\Context\MinkContext;
use Exception;

class BaseFeatureContext extends MinkContext
{
    use DebugTrait;

    const BEHAT_FRONT_RESET_FIXTURES = '/behat/frontend/reset-fixtures?testRunId=%s';
    const BEHAT_FRONT_USER_DETAILS = '/behat/frontend/user/%s/details';

    private string $adminEmail = '';
    private string $superAdminEmail = '';
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

        $activeReportId = json_decode($this->getPageContent(), true)['ActiveReportId'];
        $userId = json_decode($this->getPageContent(), true)['UserId'];

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
     * @Given the following court orders exist:
     *
     * @param TableNode $table
     */
    public function theFollowingCourtOrdersExist(TableNode $table)
    {
        $this->loginToAdminAs($this->superAdminEmail);

        foreach ($table as $row) {
            $queryString = http_build_query([
                'case-number' => $row['client'],
                'court-date' => $row['court_date'],
                'deputy-email' => $row['deputy'] . '@behat-test.com'
            ]);

            $url = sprintf('/admin/fixtures/court-orders?%s', $queryString);
            $this->visitAdminPath($url);

            $activated = is_null($row['activated']) || $row['activated'] == 'true';
            $this->fillField('court_order_fixture_activated', $activated);
            $this->fillField('court_order_fixture_deputyType', $row['deputy_type']);
            $this->fillField('court_order_fixture_reportType', $this->resolveReportType($row));
            $this->fillField('court_order_fixture_reportStatus', $row['completed'] ? 'readyToSubmit' : 'notStarted');
            $this->fillField('court_order_fixture_orgSizeClients', $row['orgSizeClients'] ? $row['orgSizeClients'] : 1);
            $this->fillField('court_order_fixture_orgSizeUsers', $row['orgSizeUsers'] ? $row['orgSizeUsers'] : 1);

            $this->pressButton('court_order_fixture_submit');
        }
    }

    /**
     * @param $row
     * @return string
     */
    private function resolveReportType($row): string
    {
        $typeFromFeatureFile = strtolower($row['report_type']);

        switch ($typeFromFeatureFile) {
            case 'health and welfare':
                return '104';
            case 'property and financial affairs high assets':
                return '102';
            case 'property and financial affairs low assets':
                return '103';
            case 'high assets with health and welfare':
                return '102-4';
            case 'low assets with health and welfare':
                return '103-4';
            case 'ndr':
                return 'ndr';
            default:
                return '102';
        }
    }
}
