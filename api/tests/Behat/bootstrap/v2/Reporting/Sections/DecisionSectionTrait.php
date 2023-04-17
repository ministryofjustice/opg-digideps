<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

use App\Tests\Behat\BehatException;

// use App\Tests\Behat\MinkExtension\Context;

trait DecisionSectionTrait
{
    /**
     * @When /^I view and start the decisions report section$/
     */
    public function iViewAndStartTheDecisionsReportSection()
    {
        $this->iViewDecisionsSection();
        $this->clickLink('Start decisions');
    }

    /**
     * @Given I view the decisions report section
     */
    public function iViewDecisionsSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'decisions');

        $this->visitPath($reportSectionUrl);

        $currentUrl = $this->getCurrentUrl();
        $onSummaryPage = preg_match('/report\/.*\/decisions$/', $currentUrl);

        if (!$onSummaryPage) {
            throw new BehatException(sprintf('Not on decisions start page. Current URL is: %s', $currentUrl));
        }
    }
}
