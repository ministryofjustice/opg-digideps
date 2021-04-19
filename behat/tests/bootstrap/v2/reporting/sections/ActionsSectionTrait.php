<?php declare(strict_types=1);

namespace DigidepsBehat\v2\Reporting\Sections;

trait ActionsSectionTrait
{
    private int $answeredYes = 0;
    private int $answeredNo = 0;
    private array $comments = [];

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

    public function fillInActionsForm($answer, $actionName, $comment = null, $commentName = null)
    {
        $this->selectOption($actionName, $answer);

        if ($answer === "no") {
            $this->answeredNo += 1;
        } elseif ($answer === "yes") {
            $this->answeredYes += 1;
        }
        if ($comment != null) {
            $this->fillField($commentName, $comment);
            array_push($this->comments, $comment);
        }

        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see the expected action report section responses
     */
    public function iSeeExpectedActionSectionResponses()
    {
        $table = $this->getSession()->getPage()->find('css', 'dl');

        if (!$table) {
            $this->throwContextualException('A dl element was not found on the page');
        }

        $tableEntry = $table->findAll('css', 'dd');

        if (!$tableEntry) {
            $this->throwContextualException('A dd element was not found on the page');
        }

        $countNegativeReponse = 0;
        $countPositiveReponse = 0;

        foreach ($tableEntry as $entry) {
            if (trim(strtolower($entry->getHtml())) === "no") {
                $countNegativeReponse += 1;
            } elseif (strtolower(trim($entry->getHtml())) === "yes") {
                $countPositiveReponse += 1;
            }
        }

        $this->bespokeAssert($countNegativeReponse, $this->answeredNo, 'Actions "No" Counts', true);
        $this->bespokeAssert($countPositiveReponse, $this->answeredYes, 'Actions "Yes" Counts', true);
    }

    /**
     * @Then I should see the expected action comments
     */
    public function iShouldSeeTheExpectedActionComments()
    {
        $table = $this->getSession()->getPage()->find('css', 'dl');

        if (!$table) {
            $this->throwContextualException('A dl element was not found on the page');
        }

        foreach ($this->comments as $comment) {
            $this->bespokeAssert($comment, $table->getHtml(), 'Actions Comments', false);
        }
    }

    /**
     * @Then I follow edit link on concerns question
     */
    public function iFollowEditLinkConcernActionsPage()
    {
        //this should be replaced with actual link click but could not identify it properly
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportConcernsUrl = 'report/' . $activeReportId . '/actions/step/2?from=summary';
        $this->visitPath($reportConcernsUrl);
    }
}
