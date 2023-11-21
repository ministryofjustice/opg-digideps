<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait ActionsSectionTrait
{
    /**
     * @Given I view the actions report section
     */
    public function iViewActionsSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'actions');
        $this->visitPath($reportSectionUrl);
    }

    /**
     * @Given I view and start the actions report section
     */
    public function iViewAndStartActionsSection()
    {
        $this->iViewActionsSection();
        $this->clickLink('Start actions');
    }

    /**
     * @Given I choose no and save on financial decision actions section
     */
    public function iChooseNoOnFinancialDecisionActionsSection1()
    {
        $this->fillInActionsForm(
            'no',
            'action[doYouExpectFinancialDecisions]'
        );
    }

    /**
     * @Given I choose yes and save on financial decision actions section
     */
    public function iChooseYesOnFinancialDecisionActionsSection1()
    {
        $this->fillInActionsForm(
            'yes',
            'action[doYouExpectFinancialDecisions]',
            'first comment',
            'action[doYouExpectFinancialDecisionsDetails]'
        );
    }

    /**
     * @Given I choose no and save on concerns actions section
     */
    public function iChooseNoOnDoYouHaveConcernsActionsSection()
    {
        $this->fillInActionsForm(
            'no',
            'action[doYouHaveConcerns]',
        );
    }

    /**
     * @Given I choose yes and save on concerns actions section
     */
    public function iChooseYesOnDoYouHaveConcernsActionsSection()
    {
        $this->fillInActionsForm(
            'yes',
            'action[doYouHaveConcerns]',
            'second comment',
            'action[doYouHaveConcernsDetails]'
        );
    }

    /**
     * @Given I choose no and save on gifts actions section
     */
    public function iChooseNoAndSaveOnGiftsActionsSection()
    {
        $this->fillInActionsForm(
            'no',
            'actions[actionGiveGiftsToClient]'
        );
    }

    /**
     * @Given I choose yes and fill in details about the gifts and then save on gifts actions section
     */
    public function iChooseYesAndSaveOnGiftsActionsSection()
    {
        $this->chooseOption(
            'actions[actionGiveGiftsToClient]',
            'yes'
        );

        $this->fillField(
            'actions[actionGiveGiftsToClientDetails]',
            'Lorem Ipsum'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @Given I choose :yesNo and save on the :ndrActionsPage page
     */
    public function iChooseYesNoAndSaveOnTheNDRActionsPage(string $yesNo, string $ndrActionsPage)
    {
        // Make ndrActionPage value the form field name
        $value = str_replace('-', ' ', $ndrActionsPage);
        $value = ucwords($value);
        $value = str_replace(' ', '', $value);

        $this->fillInActionsForm(
            $yesNo,
            "actions[action$value]"
        );
    }

    public function fillInActionsForm($answer, $actionName, $comment = null, $commentName = null)
    {
        $this->chooseOption($actionName, $answer, 'actions');

        if (null != $comment) {
            $this->fillInField($commentName, $comment, 'actions');
        }

        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see the expected action report section responses
     */
    public function iSeeExpectedActionSectionResponses()
    {
        $this->expectedResultsDisplayedSimplified();
    }

    /**
     * @Then I follow edit link on concerns question
     */
    public function iFollowEditLinkConcernActionsPage()
    {
        // this should be replaced with actual link click but could not identify it properly
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportConcernsUrl = 'report/'.$activeReportId.'/actions/step/2?from=summary';
        $this->visitPath($reportConcernsUrl);
    }
}
