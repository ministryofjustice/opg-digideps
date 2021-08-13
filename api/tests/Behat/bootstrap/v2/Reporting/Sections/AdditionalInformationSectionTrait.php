<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

use App\Tests\Behat\BehatException;

trait AdditionalInformationSectionTrait
{
    /**
     * @Given I view the additional information report section
     */
    public function iViewAdditionalInformationSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'any-other-info');

        $this->visitPath($reportSectionUrl);

        $currentUrl = $this->getCurrentUrl();
        $onSummaryPage = preg_match('/report\/.*\/any-other-info$/', $currentUrl);

        if (!$onSummaryPage) {
            throw new BehatException(sprintf('Not on additional information start page. Current URL is: %s', $currentUrl));
        }
    }

    /**
     * @Given I view and start the additional information report section
     */
    public function iViewAndStartAdditionalInformationSection()
    {
        $this->iViewAdditionalInformationSection();

        $this->clickLink('Start any other information');
    }

    /**
     * @Given there is additional information to add
     */
    public function thereIsAdditionalInformationToAdd()
    {
        $this->chooseOption('more_info[actionMoreInfo]', 'yes', 'additionalInfo');
        $this->fillInField('more_info_actionMoreInfoDetails', $this->faker->text(200), 'additionalInfo');

        $this->pressButton('Save and continue');
    }

    /**
     * @Given there is no additional information to add
     */
    public function thereIsNoAdditionalInformationToAdd()
    {
        $this->chooseOption('more_info[actionMoreInfo]', 'no', 'additionalInfo');
        $this->pressButton('Save and continue');
    }

    /**
     * @Then the additional information summary page should contain the details I entered
     */
    public function additionalInformationSummaryPageContainsExpectedText()
    {
        $this->iAmOnAnyOtherInfoSummaryPage();
        $this->expectedResultsDisplayedSimplified();
    }
}
