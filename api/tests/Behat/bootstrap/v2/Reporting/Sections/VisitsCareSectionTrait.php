<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait VisitsCareSectionTrait
{
    /**
     * @Given I view the visits and care report section
     */
    public function iViewVisitsCareSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'visits-care');
        $this->visitPath($reportSectionUrl);
    }

    /**
     * @Given I view and start the visits and care report section
     */
    public function iViewAndStartVisitsCareSection()
    {
        $driver = $this->getSession()->getDriver();
        if ('Behat\Mink\Driver\Selenium2Driver' == get_class($driver)) {
            $this->getSession()->maximizeWindow();
        }
        $this->iViewVisitsCareSection();
        $this->clickLink('Start visits and care');
        $this->iAmOnVisitsCarePage1();
    }

    /**
     * @Given I confirm I live with the client
     */
    public function iChooseYesOnLiveWithTheClientSection()
    {
        $this->chooseOption('visits_care[doYouLiveWithClient]', 'yes', 'LiveWithClient');
        $this->pressButton('Save and continue');
        $this->iAmOnVisitsCarePage2();
    }

    /**
     * @Given I confirm I do not live with the client
     */
    public function iChooseNoOnLiveWithTheClientSection()
    {
        $info = 'Information on how often there is contact with the client';

        $driver = $this->getSession()->getDriver();
        if ('Behat\Mink\Driver\Selenium2Driver' == get_class($driver)) {
            $this->iFillFieldForCrossBrowser('visits_care_doYouLiveWithClient_1', 'no');
            $this->iFillFieldForCrossBrowser('visits_care_howOftenDoYouContactClient', $info);
        } else {
            $this->chooseOption('visits_care[doYouLiveWithClient]', 'no', 'LiveWithClient');
            $this->fillInField('visits_care[howOftenDoYouContactClient]', $info, 'LiveWithClient');
        }

        $this->pressButton('Save and continue');
        $this->iAmOnVisitsCarePage2();
    }

    /**
     * @Given I confirm the client does not receive paid care
     */
    public function iChooseNoOnReceivePaidCareSection()
    {
        $this->chooseOption('visits_care[doesClientReceivePaidCare]', 'no', 'DoesClientReceiveCare');

        $this->pressButton('Save and continue');
        $this->iAmOnVisitsCarePage3();
    }

    /**
     * @Given I confirm the client receives paid care which is funded by themselves
     */
    public function iChooseYesOnReceivePaidCareSection()
    {
        $fromSummaryPage = false;

        if ($this->iAmOnPage(sprintf('/%s\/.*\/visits-care\/step\/[0-9]\?from=summary$/', $this->reportUrlPrefix))) {
            $fromSummaryPage = true;
        }

        $this->chooseOption('visits_care[doesClientReceivePaidCare]', 'yes', 'DoesClientReceiveCare');
        $this->chooseOption(
            'visits_care[howIsCareFunded]',
            'client_pays_for_all',
            'DoesClientReceiveCare',
            'pays for all the care'
        );

        $this->pressButton('Save and continue');

        true === $fromSummaryPage ? $this->iAmOnVisitsCareSummaryPage() : $this->iAmOnVisitsCarePage3();
    }

    /**
     * @Given I confirm the client receives paid care which is partially funded by someone else
     */
    public function iChooseYesAndOptionTwoOnReceivePaidCareSection()
    {
        $this->chooseOption('visits_care[doesClientReceivePaidCare]', 'yes', 'DoesClientReceiveCare');
        $this->chooseOption(
            'visits_care[howIsCareFunded]',
            'client_gets_financial_help',
            'DoesClientReceiveCare',
            'gets some financial help'
        );

        $this->pressButton('Save and continue');
        $this->iAmOnVisitsCarePage3();
    }

    /**
     * @Given I confirm the client receives paid care which is fully funded by someone else
     */
    public function iChooseYesAndOptionThreeOnReceivePaidCareSection()
    {
        $this->chooseOption('visits_care[doesClientReceivePaidCare]', 'yes', 'DoesClientReceiveCare');
        $this->chooseOption(
            'visits_care[howIsCareFunded]',
            'all_care_is_paid_by_someone_else',
            'DoesClientReceiveCare',
            'care is paid for by someone else'
        );

        $this->pressButton('Save and continue');
        $this->iAmOnVisitsCarePage3();
    }

    /**
     * @Given I provide details on who is doing the caring
     */
    public function iFillOutWhoIsDoingCaringSection()
    {
        $this->fillInField('visits_care[whoIsDoingTheCaring]',
            'Information on who is doing the caring',
            'WhoIsGivingCare'
        );

        $this->pressButton('Save and continue');

        $this->iAmOnVisitsCarePage4();
    }

    /**
     * @Given I confirm the client does not have a care plan
     */
    public function iChooseNoOnHasCarePlanSection()
    {
        $this->chooseOption('visits_care[doesClientHaveACarePlan]', 'no', 'HasCarePlan');
        $this->pressButton('Save and continue');

        if ('ndr' == $this->reportUrlPrefix) {
            $this->iAmOnVisitsCarePage5();
        } else {
            $this->iAmOnVisitsCareSummaryPage();
        }
    }

    /**
     * @Given I confirm the client has a care plan
     */
    public function iChooseYesOnHasCarePlanSection()
    {
        $this->chooseOption('visits_care[doesClientHaveACarePlan]', 'yes', 'HasCarePlan');
        $this->fillInDateFields(
            'visits_care[whenWasCarePlanLastReviewed]',
            null,
            12,
            2015,
            'HasCarePlan'
        );

        $this->pressButton('Save and continue');

        if ('ndr' == $this->reportUrlPrefix) {
            $this->iAmOnVisitsCarePage5();
        } else {
            $this->iAmOnVisitsCareSummaryPage();
        }
    }

    /**
     * @Given I confirm there are no plans to move the client to a new residence
     */
    public function iChooseNoOnPlansToMoveClient()
    {
        $this->chooseOption('visits_care[planMoveNewResidence]', 'no', 'MoveResidence');
        $this->pressButton('Save and continue');

        $this->iAmOnVisitsCareSummaryPage();
    }

    /**
     * @Given I confirm there are plans to to move the client to a new residence
     */
    public function iChooseYesOnPlansToMoveClient()
    {
        $this->chooseOption('visits_care[planMoveNewResidence]', 'yes', 'MoveResidence');
        $this->fillInField('visits_care[planMoveNewResidenceDetails]',
            'Information on plans to move the client',
            'MoveResidence'
        );

        $this->pressButton('Save and continue');

        $this->iAmOnVisitsCareSummaryPage();
    }

    /**
     * @Then I should see the expected visits and care report section responses
     */
    public function iSeeExpectedVisitCareSectionResponses()
    {
        $this->iAmOnVisitsCareSummaryPage();

        $this->expectedResultsDisplayedSimplified('LiveWithClient');
        $this->expectedResultsDisplayedSimplified('DoesClientReceiveCare', true);
        $this->expectedResultsDisplayedSimplified('WhoIsGivingCare');
        $this->expectedResultsDisplayedSimplified('HasCarePlan');

        if (!is_null($this->getSectionAnswers('MoveResidence'))) {
            $this->expectedResultsDisplayedSimplified('MoveResidence');
        }
    }

    /**
     * @Then I follow edit link for does client receive paid care page
     */
    public function iFollowEditLinkClientReceivePaidCarePage()
    {
        // Click on the edit button for the client receive paid care page
        $urlRegex = sprintf('/%s\/.*\/visits-care\/step\/2\?from\=summary$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 1);

        $this->iAmOnVisitsCarePage2();
    }
}
