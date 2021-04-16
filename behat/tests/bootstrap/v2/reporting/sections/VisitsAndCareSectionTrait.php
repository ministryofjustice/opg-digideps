<?php declare(strict_types=1);

namespace DigidepsBehat\v2\Reporting\Sections;

trait VisitsAndCareSectionTrait
{
    /**
     * @Given I view visits and care section
     */
    public function iViewVisitsAndCareSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $activeReportId, 'visits-care');
        $this->visitPath($reportSectionUrl);
    }

    /**
     * @Given I view and start visits and care section
     */
    public function iViewAndStartVisitsAndCareSection()
    {
        $this->getSession()->maximizeWindow();
        $this->iViewVisitsAndCareSection();
        $this->clickLink('Start visits and care');
    }

    /**
     * @When I enter that I do not live with client
     */
    public function iDoNotLiveWithClientVisitsAndCareSection()
    {
        $this->iFillFieldForCrossBrowser('visits_care_doYouLiveWithClient_1', 'no');
    }

    /**
     * @When I can fill in a text box with how often I visit client
     */
    public function iCanFillInVisitsTextBox()
    {
        $this->iFillFieldForCrossBrowser('visits_care_howOftenDoYouContactClient', 'daily');
        $this->pressButton('Save and continue');
        assert($this->iAmOnVisitsAndCareStep2Page());
    }
}
