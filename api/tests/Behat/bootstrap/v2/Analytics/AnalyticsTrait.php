<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Analytics;

trait AnalyticsTrait
{
    private int $runNumber = 0;
    private string $currentLinkText = '';
    private array $expectedMetrics = [];
    private array $metricXPaths = [
        'feedBack' => '//span[@aria-labelledby=\'metric-satisfaction-total-label\']',
        'totalReports' => '//span[@aria-labelledby=\'metric-reportsSubmitted-total-label\']',
        'totalRegistered' => '//span[@aria-labelledby=\'metric-registeredDeputies-total-label\']',
        'totalClients' => '//span[@aria-labelledby=\'metric-clients-total-label\']',
        'laySatisfaction' => '//span[@aria-labelledby=\'metric-satisfaction-deputyType-lay-label\']',
        'laySubmitted' => '//span[@aria-labelledby=\'metric-reportsSubmitted-deputyType-lay-label\']',
        'layDeputies' => '//span[@aria-labelledby=\'metric-registeredDeputies-deputyType-lay-label\']',
        'layClients' => '//span[@aria-labelledby=\'metric-clients-deputyType-lay-label\']',
        'profSatisfaction' => '//span[@aria-labelledby=\'metric-satisfaction-deputyType-prof-label\']',
        'profSubmitted' => '//span[@aria-labelledby=\'metric-reportsSubmitted-deputyType-prof-label\']',
        'profDeputies' => '//span[@aria-labelledby=\'metric-registeredDeputies-deputyType-prof-label\']',
        'profClients' => '//span[@aria-labelledby=\'metric-clients-deputyType-prof-label\']',
        'paSatisfaction' => '//span[@aria-labelledby=\'metric-satisfaction-deputyType-pa-label\']',
        'paSubmitted' => '//span[@aria-labelledby=\'metric-reportsSubmitted-deputyType-pa-label\']',
        'paDeputies' => '//span[@aria-labelledby=\'metric-registeredDeputies-deputyType-pa-label\']',
        'paClients' => '//span[@aria-labelledby=\'metric-clients-deputyType-pa-label\']',
    ];

    /**
     * @When reports exist that were submitted at different times
     */
    public function reportsExistThatWereSubmittedDifferentTimes()
    {
        ++$this->runNumber;
        $this->createAdditionalDataForAnalytics('14 months ago', $this->runNumber, 5);
    }

    /**
     * @When I add more clients, deputies are reports
     */
    public function iAddMoreClientsDeputiesReports()
    {
        ++$this->runNumber;
        $this->createAdditionalDataForAnalytics('2 years ago', $this->runNumber, 1);
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
     * @When I change reporting period to be last 30 days
     */
    public function iChangeReportingPeriodToLast30Days()
    {
        $this->selectOption('admin[period]', 'last-30');
        $this->pressButton('Update date range');
        $header = $this->getSession()->getPage()->find('xpath', '//h2[contains(.,"Last 30 days")]');
        if (is_null($header)) {
            $this->throwContextualException(sprintf('Missing correct text.'));
        }
        // Reports, Deputies and Feedback are affected by date constraints.
        // Clients will always be total clients as no date constraints exist.
        $this->expectedMetrics = [
            'feedBack' => 0,
            'totalReports' => 0,
            'totalRegistered' => 32,
            'totalClients' => 71,
            'laySatisfaction' => 0,
            'laySubmitted' => 0,
            'layDeputies' => 9,
            'layClients' => 23,
            'profSatisfaction' => 0,
            'profSubmitted' => 0,
            'profDeputies' => 15,
            'profClients' => 26,
            'paSatisfaction' => 0,
            'paSubmitted' => 0,
            'paDeputies' => 8,
            'paClients' => 22,
        ];
    }

    /**
     * @When I change reporting period to be all time
     */
    public function iChangeReportingPeriodToAllTime()
    {
        $this->selectOption('admin[period]', 'all-time');
        $this->pressButton('Update date range');
        $header = $this->getSession()->getPage()->find('xpath', '//h2[contains(.,"All time")]');
        if (is_null($header)) {
            $this->throwContextualException(sprintf('Missing correct text.'));
        }
        // This should show all the reports
        $this->expectedMetrics = [
            'feedBack' => 100,
            'totalReports' => 3,
            'totalRegistered' => 35,
            'totalClients' => 71,
            'laySatisfaction' => 100,
            'laySubmitted' => 1,
            'layDeputies' => 10,
            'layClients' => 23,
            'profSatisfaction' => 100,
            'profSubmitted' => 1,
            'profDeputies' => 16,
            'profClients' => 26,
            'paSatisfaction' => 100,
            'paSubmitted' => 1,
            'paDeputies' => 9,
            'paClients' => 22,
        ];
    }

    public function setupAllTimeAgain()
    {
        $this->selectOption('admin[period]', 'all-time');
        $this->pressButton('Update date range');
        $header = $this->getSession()->getPage()->find('xpath', '//h2[contains(.,"All time")]');
        if (is_null($header)) {
            $this->throwContextualException(sprintf('Missing correct text.'));
        }
        // This should show all the reports
        $this->expectedMetrics = [
            'feedBack' => 50,
            'totalReports' => 6,
            'totalRegistered' => 38,
            'totalClients' => 74,
            'laySatisfaction' => 50,
            'laySubmitted' => 2,
            'layDeputies' => 11,
            'layClients' => 24,
            'profSatisfaction' => 50,
            'profSubmitted' => 2,
            'profDeputies' => 17,
            'profClients' => 26,
            'paSatisfaction' => 50,
            'paSubmitted' => 2,
            'paDeputies' => 10,
            'paClients' => 24,
        ];
    }

    /**
     * @When I change reporting period to be all time again
     */
    public function iChangeReportingPeriodToAllTimeAgain()
    {
        $this->setupAllTimeAgain();
    }

    /**
     * @When I change reporting period to be all time again for admin manager
     */
    public function iChangeReportingPeriodToAllTimeAgainAdminManager()
    {
        $this->setupAllTimeAgain();
        $this->expectedMetrics['totalRegistered'] = 39;
        $this->expectedMetrics['layDeputies'] = 12;
    }

    /**
     * @When I change reporting period to be all time again for admin user
     */
    public function iChangeReportingPeriodToAllTimeAgainAdminUser()
    {
        $this->setupAllTimeAgain();
        $this->expectedMetrics['totalRegistered'] = 40;
        $this->expectedMetrics['layDeputies'] = 13;
    }

    /**
     * @When I change reporting period to be this year
     */
    public function iChangeReportingPeriodToThisYear()
    {
        $this->selectOption('admin[period]', 'this-year');
        $this->pressButton('Update date range');
        $header = $this->getSession()->getPage()->find('xpath', '//h2[contains(.,"This year")]');
        if (is_null($header)) {
            $this->throwContextualException(sprintf('Missing correct text.'));
        }
        // Deputies registration date is same as start date of report so less should show up
        $this->expectedMetrics = [
            'feedBack' => 100,
            'totalReports' => 3,
            'totalRegistered' => 32,
            'totalClients' => 71,
            'laySatisfaction' => 100,
            'laySubmitted' => 1,
            'layDeputies' => 9,
            'layClients' => 23,
            'profSatisfaction' => 100,
            'profSubmitted' => 1,
            'profDeputies' => 15,
            'profClients' => 26,
            'paSatisfaction' => 100,
            'paSubmitted' => 1,
            'paDeputies' => 8,
            'paClients' => 22,
        ];
    }

    /**
     * @When I change reporting period to apply only to our generated data
     */
    public function iChangeReportingPeriodToApplyOnlyToOurGeneratedData()
    {
        $fromDate = explode('-', strval(date('d-m-Y', strtotime('-2 year'))));
        $toDate = explode('-', strval(date('d-m-Y', strtotime('-7 day'))));

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
            $this->throwContextualException(sprintf('Missing correct text.'));
        }
        // We should only get back our generated reports
        $this->expectedMetrics = [
            'feedBack' => 100,
            'totalReports' => 3,
            'totalRegistered' => 3,
            'totalClients' => 71,
            'laySatisfaction' => 100,
            'laySubmitted' => 1,
            'layDeputies' => 1,
            'layClients' => 23,
            'profSatisfaction' => 100,
            'profSubmitted' => 1,
            'profDeputies' => 1,
            'profClients' => 26,
            'paSatisfaction' => 100,
            'paSubmitted' => 1,
            'paDeputies' => 1,
            'paClients' => 22,
        ];
    }

    /**
     * @When I should see the correct options in the actions dropdown
     */
    public function iShouldSeeCorrectOptionsInActionsDropdown()
    {
        $linkTextItems = [
            'Download DAT file',
            'Download satisfaction report',
            'Download user research report',
            'Download active lays report',
        ];
        foreach ($linkTextItems as $linkText) {
            $xpath = sprintf('//a[contains(.,"%s")]', $linkText);
            $downloadLink = $this->getSession()->getPage()->find('xpath', $xpath);
            if (is_null($downloadLink)) {
                $this->throwContextualException(sprintf('Missing the following text: %s', $linkText));
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
            $this->throwContextualException(sprintf('Number of download file option should be 1. Currently: %s', strval(count($downloadLinks))));
        }

        $xpath = '//a[contains(.,"Download DAT file")]';
        $downloadLink = $this->getSession()->getPage()->find('xpath', $xpath);
        if (is_null($downloadLink)) {
            $this->throwContextualException('Link to DAT file does not exist on page');
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
        $this->iVisitAdminAnalyticsPage();
        $this->currentLinkText = 'Download satisfaction report';

        $xpath = sprintf('//a[contains(.,"%s")]', $this->currentLinkText);
        $downloadLink = $this->getSession()->getPage()->find('xpath', $xpath);
        if (is_null($downloadLink)) {
            $this->throwContextualException(sprintf('Missing the following text: %s', $this->currentLinkText));
        }
        $downloadLink->click();
        $this->iAmOnAdminStatsSatisfactionPage();

        $this->fillInAnalyticsStartEndDates();
    }

    /**
     * @When I try to download user research report
     */
    public function iTryDownloadUserResearchReport()
    {
        $this->iVisitAdminAnalyticsPage();
        $this->currentLinkText = 'Download user research report';

        $xpath = sprintf('//a[contains(.,"%s")]', $this->currentLinkText);
        $downloadLink = $this->getSession()->getPage()->find('xpath', $xpath);
        if (is_null($downloadLink)) {
            $this->throwContextualException(sprintf('Missing the following text: %s', $this->currentLinkText));
        }
        $downloadLink->click();
        $this->iAmOnAdminStatsUserResearchPage();

        $this->fillInAnalyticsStartEndDates();
    }

    /**
     * @When I try to download active lays report
     */
    public function iTryDownloadActiveLaysReport()
    {
        $this->iVisitAdminAnalyticsPage();
        $this->currentLinkText = 'Download active lays report';

        $xpath = sprintf('//a[contains(.,"%s")]', $this->currentLinkText);
        $downloadLink = $this->getSession()->getPage()->find('xpath', $xpath);
        if (is_null($downloadLink)) {
            $this->throwContextualException(sprintf('Missing the following text: %s', $this->currentLinkText));
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
            $this->throwContextualException(sprintf('Missing the following text: %s', $this->currentLinkText));
        }
        $downloadLink->click();
        $this->iAmOnAdminStatsPage();

        $this->fillInAnalyticsStartEndDates();
    }

    /**
     * @When I should have no issues downloading the file
     */
    public function shouldHaveNoIssuesDownloadingFile()
    {
        $responseStatus = $this->getSession()->getStatusCode();
        if (200 != $responseStatus) {
            $this->throwContextualException(sprintf('%s, returned a %s response', $this->currentLinkText, strval($responseStatus)));
        }
    }
}
