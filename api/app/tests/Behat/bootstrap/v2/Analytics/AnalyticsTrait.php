<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Analytics;

use App\Entity\User;
use App\Tests\Behat\BehatException;

trait AnalyticsTrait
{
    private int $runNumber = 0;
    private string $currentLinkText = '';
    private array $expectedMetrics = [];
    private array $metricXPaths = [
        'feedBack' => '//span[@aria-labelledby=\'metric-satisfaction-total-label\']',
        'totalRespondents' => '//span[@aria-labelledby=\'metric-respondents-total-label\']',
        'totalReports' => '//span[@aria-labelledby=\'metric-reportsSubmitted-total-label\']',
        'totalRegistered' => '//span[@aria-labelledby=\'metric-registeredDeputies-total-label\']',
        'laySatisfaction' => '//span[@aria-labelledby=\'metric-satisfaction-deputyType-lay-label\']',
        'laySubmitted' => '//span[@aria-labelledby=\'metric-reportsSubmitted-deputyType-lay-label\']',
        'layDeputies' => '//span[@aria-labelledby=\'metric-registeredDeputies-deputyType-lay-label\']',
        'profSatisfaction' => '//span[@aria-labelledby=\'metric-satisfaction-deputyType-prof-label\']',
        'profSubmitted' => '//span[@aria-labelledby=\'metric-reportsSubmitted-deputyType-prof-label\']',
        'profDeputies' => '//span[@aria-labelledby=\'metric-registeredDeputies-deputyType-prof-label\']',
        'paSatisfaction' => '//span[@aria-labelledby=\'metric-satisfaction-deputyType-pa-label\']',
        'paSubmitted' => '//span[@aria-labelledby=\'metric-reportsSubmitted-deputyType-pa-label\']',
        'paDeputies' => '//span[@aria-labelledby=\'metric-registeredDeputies-deputyType-pa-label\']',
    ];

    private array $dateRangeOfFixtures = ['from' => null, 'to' => null];
    /** @var User[] */
    private array $fixtureUsersCreated = [];

    /**
     * @When reports exist that were submitted :numOfYears years ago
     */
    public function reportsExistThatWereSubmittedYearsAgo(int $numOfYears)
    {
        $this->dateRangeOfFixtures['from'] = $numOfYears;
        $this->dateRangeOfFixtures['to'] = $numOfYears - 1;

        ++$this->runNumber;
        $this->fixtureUsersCreated = $this->createAdditionalDataForAnalytics("$numOfYears years ago", $this->runNumber, 5);

        $this->expectedMetrics = [
            'feedBack' => 100,
            'totalRespondents' => 3,
            'totalReports' => 3,
            'totalRegistered' => 3,
            'laySatisfaction' => 100,
            'laySubmitted' => 1,
            'layDeputies' => 1,
            'profSatisfaction' => 100,
            'profSubmitted' => 1,
            'profDeputies' => 1,
            'paSatisfaction' => 100,
            'paSubmitted' => 1,
            'paDeputies' => 1,
        ];
    }

    /**
     * @When I add more clients, deputies and reports
     */
    public function iAddMoreClientsDeputiesReports()
    {
        $fromYearsAgo = $this->dateRangeOfFixtures['from'];

        ++$this->runNumber;
        $this->createAdditionalDataForAnalytics("$fromYearsAgo years ago", $this->runNumber, 1);

        $this->expectedMetrics = [
            'feedBack' => 50,
            'totalRespondents' => 6,
            'totalReports' => 6,
            'totalRegistered' => 6,
            'laySatisfaction' => 50,
            'laySubmitted' => 2,
            'layDeputies' => 2,
            'profSatisfaction' => 50,
            'profSubmitted' => 2,
            'profDeputies' => 2,
            'paSatisfaction' => 50,
            'paSubmitted' => 2,
            'paDeputies' => 2,
        ];
    }

    /**
     * @When I should see the correct metric values displayed
     */
    public function iShouldSeeTheCorrectMetricValuesDisplayed()
    {
        foreach ($this->metricXPaths as $metric => $xpath) {
            $actualValue = str_replace('%', '', trim(strval($this->getSession()->getPage()->find('xpath', $xpath)->getHtml())));
            $realActualValue = '-' == $actualValue ? 0 : intval($actualValue);
            $expectedValue = $this->expectedMetrics[$metric];
            $this->assertIntEqualsInt($expectedValue, $realActualValue, 'Analytics Values - '.$metric);
        }
    }

    /**
     * @When there are existing reports submitted
     */
    public function thereAreExistingReportsSubmitted()
    {
        $this->expectedMetrics = [
            'feedBack' => 50,
            'totalRespondents' => 6,
            'totalReports' => 6,
            'totalRegistered' => 3,
            'laySatisfaction' => 50,
            'laySubmitted' => 2,
            'layDeputies' => 1,
            'profSatisfaction' => 50,
            'profSubmitted' => 2,
            'profDeputies' => 1,
            'paSatisfaction' => 50,
            'paSubmitted' => 2,
            'paDeputies' => 1,
        ];
    }

    /**
     * @When I change reporting period to apply only to our generated data
     */
    public function iChangeReportingPeriodToApplyOnlyToOurGeneratedData()
    {
        $fromYears = $this->dateRangeOfFixtures['from'];
        $toYears = $this->dateRangeOfFixtures['to'];

        $fromDate = explode('-', strval(date('d-m-Y', strtotime("-$fromYears years"))));
        $toDate = explode('-', strval(date('d-m-Y', strtotime("-$toYears years"))));

        $this->selectOption('admin[period]', 'custom');
        $this->fillField('admin[startDate][day]', $fromDate[0]);
        $this->fillField('admin[startDate][month]', $fromDate[1]);
        $this->fillField('admin[startDate][year]', $fromDate[2]);
        $this->fillField('admin[endDate][day]', $toDate[0]);
        $this->fillField('admin[endDate][month]', $toDate[1]);
        $this->fillField('admin[endDate][year]', $toDate[2]);
        $this->pressButton('Update date range');

        $header = $this->getSession()->getPage()->find('xpath', '//h2[contains(.,"Custom")]');

        if (is_null($header)) {
            throw new BehatException(sprintf('Missing correct text.'));
        }
    }

    /**
     * @When I should see the correct options in the actions dropdown
     */
    public function iShouldSeeCorrectOptionsInActionsDropdown()
    {
        $linkTextItems = [
            'Download DAT file',
            'View reports',
        ];
        foreach ($linkTextItems as $linkText) {
            $xpath = sprintf('//a[contains(.,"%s")]', $linkText);
            $downloadLink = $this->getSession()->getPage()->find('xpath', $xpath);
            if (is_null($downloadLink)) {
                throw new BehatException(sprintf('Missing the following text: %s', $linkText));
            }
        }
    }

    /**
     * @When I should only see the download DAT button
     */
    public function iShouldOnlySeeDownloadDATButton()
    {
        $xpath = '//div[@class="moj-page-header-actions"]//a';
        $downloadLinks = $this->getSession()->getPage()->findAll('xpath', $xpath);

        if (1 != count($downloadLinks)) {
            throw new BehatException(sprintf('Number of download file option should be 1. Currently: %s', strval(count($downloadLinks))));
        }

        $xpath = '//a[contains(.,"Download DAT file")]';
        $downloadLink = $this->getSession()->getPage()->find('xpath', $xpath);
        if (is_null($downloadLink)) {
            throw new BehatException('Link to DAT file does not exist on page');
        }
    }

    private function fillInAnalyticsStartEndDates()
    {
        $fromDate = explode('-', strval(date('d-m-Y', strtotime('-2 year'))));
        $toDate = explode('-', strval(date('d-m-Y', strtotime('-7 day'))));

        $this->fillField('admin[startDate][day]', $fromDate[0]);
        $this->fillField('admin[startDate][month]', $fromDate[1]);
        $this->fillField('admin[startDate][year]', $fromDate[2]);
        $this->fillField('admin[endDate][day]', $toDate[0]);
        $this->fillField('admin[endDate][month]', $toDate[1]);
        $this->fillField('admin[endDate][year]', $toDate[2]);
    }

    /**
     * @When I try to download satisfaction report
     */
    public function iTryDownloadSatisfactionReport()
    {
        $this->iVisitAdminStatsReportsPage();
        $this->currentLinkText = 'Download satisfaction report';

        $xpath = sprintf('//a[contains(.,"%s")]', $this->currentLinkText);
        $downloadLink = $this->getSession()->getPage()->find('xpath', $xpath);
        if (is_null($downloadLink)) {
            throw new BehatException(sprintf('Missing the following text: %s', $this->currentLinkText));
        }
        $downloadLink->click();
        $this->iAmOnAdminStatsSatisfactionPage();
    }

    /**
     * @When I try to download user research report
     */
    public function iTryDownloadUserResearchReport()
    {
        $this->iVisitAdminStatsReportsPage();
        $this->currentLinkText = 'Download user research report';

        $xpath = sprintf('//a[contains(.,"%s")]', $this->currentLinkText);
        $downloadLink = $this->getSession()->getPage()->find('xpath', $xpath);
        if (is_null($downloadLink)) {
            throw new BehatException(sprintf('Missing the following text: %s', $this->currentLinkText));
        }
        $downloadLink->click();
        $this->iAmOnAdminStatsUserResearchPage();
    }

    /**
     * @When I try to download active lays report
     */
    public function iTryDownloadActiveLaysReport()
    {
        $this->iVisitAdminStatsReportsPage();
        $this->currentLinkText = 'Download active lays report';

        $xpath = sprintf('//a[contains(.,"%s")]', $this->currentLinkText);
        $downloadLink = $this->getSession()->getPage()->find('xpath', $xpath);
        if (is_null($downloadLink)) {
            throw new BehatException(sprintf('Missing the following text: %s', $this->currentLinkText));
        }
        $downloadLink->click();
    }

    /**
     * @When I try to download the DAT file
     */
    public function iTryDownloadDATReport()
    {
        $this->iVisitAdminAnalyticsPage();
        $this->currentLinkText = 'Download DAT file';

        $xpath = sprintf('//a[contains(.,"%s")]', $this->currentLinkText);
        $downloadLink = $this->getSession()->getPage()->find('xpath', $xpath);
        if (is_null($downloadLink)) {
            throw new BehatException(sprintf('Missing the following text: %s', $this->currentLinkText));
        }
        $downloadLink->click();
        $this->iAmOnAdminStatsPage();

        $this->fillInAnalyticsStartEndDates();
    }

    /**
     * @When I try to view the reports page
     */
    public function iTryViewTheReportsPage()
    {
        $this->iVisitAdminAnalyticsPage();
        $this->currentLinkText = 'View reports';

        $xpath = sprintf('//a[contains(.,"%s")]', $this->currentLinkText);
        $link = $this->getSession()->getPage()->find('xpath', $xpath);
        if (is_null($link)) {
            throw new BehatException(sprintf('Missing the following text: %s', $this->currentLinkText));
        }
        $link->click();
        $this->iAmOnAdminStatsReportsPage();
    }

    /**
     * @Then I should have no issues downloading the file
     * @Then I should have no issues viewing the page
     */
    public function shouldHaveNoIssuesDownloadingFile()
    {
        $responseStatus = $this->getSession()->getStatusCode();
        if (200 != $responseStatus) {
            throw new BehatException(sprintf('%s, returned a %s response', $this->currentLinkText, strval($responseStatus)));
        }
    }
}
