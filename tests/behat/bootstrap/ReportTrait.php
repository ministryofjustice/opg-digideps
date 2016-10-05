<?php

namespace DigidepsBehat;

use Behat\Gherkin\Node\TableNode;

trait ReportTrait
{
    /**
     * @Given I change the report :reportId court order type to :cotName
     */
    public function iChangeTheReportCourtOrderTypeTo($reportId, $cotName)
    {
        $cotNameToId = ['Health & Welfare' => 1, 'Property and Affairs' => 2];

        $this->visitBehatLink('report/'.$reportId.'/change-report-cot/'.$cotNameToId[$cotName]);
    }

    /**
     * @Given I set the report :reportId end date to :days days ago
     */
    public function iSetTheReportDue($reportId, $days)
    {
        $endDate = new \DateTime();
        $endDate->modify("-{$days} days");

        $this->visitBehatLink("report/{$reportId}/change-report-end-date/".$endDate->format('Y-m-d'));
        $this->visit('/');
    }

    /**
     * @Given I set the report :reportId end date to :days days ahead
     */
    public function iSetTheReportNotDue($reportId, $days)
    {
        $endDate = new \DateTime();
        $endDate->modify("+{$days} days");

        $this->visitBehatLink("report/{$reportId}/change-report-end-date/".$endDate->format('Y-m-d'));
        $this->visit('/');
    }

    /**
     * @Given I change the report :reportId submitted to :value
     */
    public function iChangeTheReportToNotSubmitted($reportId, $value)
    {
        $this->visitBehatLink('report/'.$reportId.'/set-sumbmitted/'.$value);
    }

    /**
     * @Then the :arg1 asset group should be :arg2
     */
    public function theAssetGroupShouldBe($index, $text)
    {
        $css = '#assets-section .asset-group:nth-child('.$index.')';
        $this->assertSession()->elementTextContains('css', $css, $text);
    }

    /**
     * @Then the :arg1 asset in the :arg2 asset group should have a :arg3 :arg4
     */
    public function theAssetInTheAssetGroupShouldHaveA($assetIndex, $group, $field, $text)
    {
        $css = '#assets-section [data-group="'.$group.'"] .asset-item:nth-child('.$assetIndex.') .asset-'.$field.' .value';
        $this->assertSession()->elementTextContains('css', $css, $text);
    }

    /**
     * @Then the :arg1 asset in the :arg2 asset group should have an empty :arg3
     */
    public function theAssetInTheAssetGroupShouldHaveEmpty($assetIndex, $group, $field)
    {
        $css = '#assets-section [data-group="'.$group.'"] .asset-item:nth-child('.$assetIndex.') .asset-'.$field.' .value';

        $elementsFound = $this->getSession()->getPage()->findAll('css', $css);
        if (count($elementsFound) === 0) {
            throw new \RuntimeException('Element not found');
        }

        if ($elementsFound[0]->getText() != '') {
            throw new \RuntimeException('Element should be empty but contains: '.$elementsFound[0]->getText());
        }
    }

    /**
     * @Then I view the formatted report
     */
    public function viewFormattedReport()
    {
        $this->visit('/client/show');
        $linksElementsFound = $this->getSession()->getPage()->findAll('css', '.view-report-link');

        if (count($linksElementsFound) === 0) {
            throw new \RuntimeException('Element .view-report-link not found');
        }

        if (count($linksElementsFound) > 1) {
            throw new \RuntimeException('Returned multiple elements');
        }

        $url = $linksElementsFound[0]->getAttribute('href');
        $epos = strpos($url, '/display');
        $length = $epos - 8;
        $reportNumber = substr($url, 8, $length);
        $reportNumberSplit = explode('/', $reportNumber);

        if (count($reportNumberSplit) > 1) {
            $reportNumber = $reportNumberSplit[count($reportNumberSplit) - 1];
        }
        $newUrl = '/report/'.$reportNumber.'/formatted';

        $this->visit($newUrl);
    }

    /**
     * @When I set the report end date to :endDateDMY
     */
    public function iSetTheReportEndDateToAndEndDateTo($endDateDMY)
    {
        /* $startDatePieces = explode('/', $startDateDMY);
          $this->fillField('report_startDate_day', $startDatePieces[0]);
          $this->fillField('report_startDate_month', $startDatePieces[1]);
          $this->fillField('report_startDate_year', $startDatePieces[2]); */

        $endDatePieces = explode('/', $endDateDMY);
        $this->fillField('report_endDate_day', $endDatePieces[0]);
        $this->fillField('report_endDate_month', $endDatePieces[1]);
        $this->fillField('report_endDate_year', $endDatePieces[2]);

        $this->pressButton('report_save');
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
    }

    /**
     * @When I set the report start date to :endDateDMY
     */
    public function iSetTheReportStartDateToAndEndDateTo($startDateDMY)
    {
        $startDatePieces = explode('/', $startDateDMY);
        $this->fillField('report_startDate_day', $startDatePieces[0]);
        $this->fillField('report_startDate_month', $startDatePieces[1]);
        $this->fillField('report_startDate_year', $startDatePieces[2]);
    }

    private function gotoOverview()
    {
        $overviewButton = $this->getSession()->getPage()->find('css', '#overview-button');

        if (isset($overviewButton)) {
            $overviewButton->click();
        }
    }

    /**
     * Click on contacts tab and add a contact
     * If the form is not shown, click first on "add-a-contact" button (with no exception thrown).
     *
     * @When I add the following contacts:
     */
    public function IAddtheFollowingContact(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $this->gotoOverview();
            $this->clickLink('edit-contacts');

            if (1 === count($this->getSession()->getPage()->findAll('css', '#add-contacts-button'))) {
                $this->clickLink('add-contacts-button');
            }

            $this->fillField('contact_contactName', $row['contactName']);
            $this->fillField('contact_relationship', $row['relationship']);
            $this->fillField('contact_explanation', $row['explanation']);
            $this->fillField('contact_address', $row['address']);
            $this->fillField('contact_address2', $row['address2']);
            $this->fillField('contact_county', $row['county']);
            $this->fillField('contact_postcode', $row['postcode']);
            $this->fillField('contact_country', $row['country']);

            $this->pressButton('contact_save');
            $this->theFormShouldBeValid();
            $this->assertResponseStatus(200);
        }
    }

    /**
     * Click on decisions tab and add a decision
     * If the form is not shown, click first on "add-a-decision" button (with no exception thrown).
     *
     * @When I add the following decisions:
     */
    public function IAddtheFollowingDecision(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $this->gotoOverview();
            $this->clickOnBehatLink('edit-decisions, decisions');

            if (1 === count($this->getSession()->getPage()->findAll('css', '#add-decisions-button'))) {
                $this->clickLink('add-decisions-button');
            }

            $this->fillField('decision_description', $row['description']);
            switch ($row['clientInvolved']) {
                case 'yes':
                    $this->fillField('decision_clientInvolvedBoolean_0', 1);
                    break;

                case 'no':
                    $this->fillField('decision_clientInvolvedBoolean_1', 0);
                    break;
                default:
                    throw new \RuntimeException('Invalid value for clientInvolved');
            }

            $this->fillField('decision_clientInvolvedDetails', $row['clientInvolvedDetails']);

            $this->pressButton('decision_save');
            $this->theFormShouldBeValid();
            $this->assertResponseStatus(200);
        }
    }

    /**
     * @When I add the following assets:
     */
    public function iAddTheFollowingAssets(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $this->gotoOverview();
            $this->clickLink('edit-assets');

            // click on "Add" if form not present
            if (0 === count($this->getSession()->getPage()->findAll('css', '#asset_title_title'))) {
                $this->clickLink('add-assets-button');
            }

            $this->fillField('asset_title_title', $row['title']);
            $this->pressButton('asset_title_next');
            $this->theFormShouldBeValid();
            $this->assertResponseStatus(200);

            $this->fillField('asset_value', $row['value']);
            $this->fillField('asset_description', $row['description']);

            if ($row['valuationDate']) {
                $datePieces = explode('/', $row['valuationDate']);
                $this->fillField('asset_valuationDate_day', $datePieces[0]);
                $this->fillField('asset_valuationDate_month', $datePieces[1]);
                $this->fillField('asset_valuationDate_year', $datePieces[2]);
            }

            $this->pressButton('asset_save');
            $this->theFormShouldBeValid();
            $this->assertResponseStatus(200);
        }
    }

    /**
     * @When I fill in the visits and care form with the following:
     */
    public function iSetTheFollowingVisitsCare(TableNode $table)
    {
        $this->gotoOverview();
        $this->clickLink('edit-visits_care');

        $rows = $table->getRowsHash();

        foreach ($rows as $key => $value) {
            $this->fillField($key, $value);
        }

        $this->pressButton('visits_care_save');
    }

    /**
     * @When I add the following bank account:
     */
    public function iAddTheFollowingBankAccount(TableNode $table)
    {
        $this->gotoOverview();
        $this->clickLink('edit-accounts');

        // expand form if collapsed
        //if (0 === count($this->getSession()->getPage()->findAll('css', '#account_bank'))) {
            $this->clickOnBehatLink('add-account');
        //}

        $rows = $table->getRowsHash();

        $this->fillField('account_bank', $rows['bank'][0]);
        $this->fillField('account_accountNumber', $rows['accountNumber'][0]);
        $this->fillField('account_accountType', $rows['accountType'][0]);
        $this->fillField('account_sortCode_sort_code_part_1', $rows['sortCode'][0]);
        $this->fillField('account_sortCode_sort_code_part_2', $rows['sortCode'][1]);
        $this->fillField('account_sortCode_sort_code_part_3', $rows['sortCode'][2]);

        $this->fillField('account_openingBalance', $rows['openingBalance'][0]);
        $this->fillField('account_closingBalance', $rows['closingBalance'][0]);
        $this->fillField('account_isJointAccount_1', 'no');

        $this->pressButton('account_save');
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
    }

    /**
     * @When I submit the report with further info :moreInfo
     */
    public function iSubmitTheReportWithFurtherInfo($moreInfo)
    {
        // get the report then goto the overview
        $css = 'meta[name="reportId"]';

        $element = $this->getSession()->getPage()->find('css', $css);

        $reportId = $element->getAttribute('content');
        $this->visit('/report/'.$reportId.'/overview');

        # more info page
        $this->clickLink('edit-report_add_further_info');
        $this->fillField('report_add_info_furtherInformation', $moreInfo);
        $this->pressButton('report_add_info_saveAndContinue');

        # declaration page
        $this->checkOption('report_declaration_agree');
        $this->fillField('report_declaration_allAgreed_0', 1);
        $this->pressButton('report_declaration_save');

        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
    }

    private function replace_dashes($string)
    {
        $string = str_replace(' ', '-', $string);

        return strtolower($string);
    }

    /**
     * @Then the report should indicate that the :checkboxvalue checkbox for :checkboxname is checked
     */
    public function theReportShouldIndicateThatTheCheckboxForIsChecked($checkboxvalue, $checkboxname)
    {
        $css = '[data-checkbox="'.$this->replace_dashes($checkboxname).'--'.$this->replace_dashes($checkboxvalue).'"]';
        $element = $this->getSession()->getPage()->find('css', $css);

        if (!isset($element)) {
            throw new \RuntimeException("Checkbox not found:$css");
        }

        if ($element->getText() != 'X') {
            throw new \RuntimeException('Checkbox not checked');
        }
    }

    /**
     * @Then the report should not indicate that the :checkboxvalue checkbox for :checkboxname is checked
     */
    public function theReportShouldNotIndicateThatTheCheckboxForIsChecked($checkboxvalue, $checkboxname)
    {
        $css = '[data-checkbox="'.$this->replace_dashes($checkboxname).'--'.$this->replace_dashes($checkboxvalue).'"]';
        $element = $this->getSession()->getPage()->find('css', $css);

        if (!isset($element)) {
            throw new \RuntimeException("Checkbox not found:$css");
        }

        if ($element->getText() == 'X') {
            throw new \RuntimeException('Checkbox not unchecked');
        }
    }

    /**
     * @Then the report should indicate that the :checkbox checkbox is checked
     */
    public function theReportShouldIndicateThatTheCheckboxIsChecked($checkbox)
    {
        $css = '[data-checkbox="'.$this->replace_dashes($checkbox).'"]';
        $element = $this->getSession()->getPage()->find('css', $css);

        if (!isset($element)) {
            throw new \RuntimeException("Checkbox not found:$css");
        }

        if ($element->getText() != 'X') {
            throw new \RuntimeException('Checkbox not checked');
        }
    }

    /**
     * @Then the report should not indicate that the :checkbox checkbox is checked
     */
    public function theReportShouldNotIndicateThatTheCheckboxIsChecked($checkbox)
    {
        $css = '[data-checkbox="'.$this->replace_dashes($checkbox).'"]';
        $element = $this->getSession()->getPage()->find('css', $css);

        if (!isset($element)) {
            throw new \RuntimeException("Checkbox not found: $css");
        }

        if ($element->getText() == 'X') {
            throw new \RuntimeException('Checkbox checked');
        }
    }

    /**
     * @When I view the users latest report
     */
    public function iViewTheUsersLatestReport()
    {
        $this->visit('/client/show');
        $linksElementsFound = $this->getSession()->getPage()->findAll('css', '.view-report-link');

        if (count($linksElementsFound) === 0) {
            throw new \RuntimeException('Element .view-report-link not found');
        }

        if (count($linksElementsFound) > 1) {
            throw new \RuntimeException('Returned multiple elements');
        }

        $url = $linksElementsFound[0]->getAttribute('href');
        $epos = strpos($url, '/display');
        $length = $epos - 8;
        $reportNumber = substr($url, 8, $length);
        $reportNumberSplit = explode('/', $reportNumber);

        if (count($reportNumberSplit) > 1) {
            $reportNumber = $reportNumberSplit[count($reportNumberSplit) - 1];
        }
        $newUrl = '/report/'.$reportNumber.'/display';

        $this->visit($newUrl);
    }

    /**
     * @Then the :question question should be answered with :answer
     */
    public function theQuestionShouldBeAnsweredWith($question, $answer)
    {
        $questionElement = $this->getSession()->getPage()->find('xpath', '//div[text()="'.$question.'"]');

        if (!isset($questionElement)) {
            throw new \RuntimeException("Can't find element with: $question");
        }

        $parent = $questionElement->getParent();
        $answerElement = $parent->find('css', '.answer');

        if (!isset($answerElement)) {
            throw new \RuntimeException('This question has no answers');
        }

        $text = strtolower($answerElement->getText());
        $answer = strtolower($answer);

        if ($text != $answer) {
            throw new \RuntimeException("Not the answer I had hoped for :(  $text -  $$answer");
        }
    }

    /**
     * @Then the :question question, in the :section section, should be answered with :answer
     */
    public function theQuestionInSectionShouldBeAnsweredWith($question, $section, $answer)
    {
        $section = $this->getSession()->getPage()->find('css', '#'.$section.'-section');
        if (!isset($section)) {
            throw new \RuntimeException("Can't find section with: $section");
        }

        $questionElement = $section->find('xpath', '//div[text()="'.$question.'"]');

        if (!isset($questionElement)) {
            throw new \RuntimeException("Can't find element with: $question");
        }

        $parent = $questionElement->getParent();
        $answerElement = $parent->find('css', '.answer');

        if (!isset($answerElement)) {
            throw new \RuntimeException('This question has no answers');
        }

        $text = strtolower($answerElement->getText());
        $answer = strtolower($answer);

        if ($text != $answer) {
            throw new \RuntimeException("Not the answer I had hoped for :(  $text -  $$answer");
        }
    }

    /**
     * @Then I say there were no decisions made because :text
     */
    public function noDecisionsMade($text)
    {
        $this->gotoOverview();
        $this->clickOnBehatLink('edit-decisions, decisions');
        $this->fillField('reason_for_no_decision_reason', $text);
        $this->pressButton('reason_for_no_decision_saveReason');
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
    }

    /**
     * @Then I say there were no contacts because :text
     */
    public function noContacts($text)
    {
        $this->gotoOverview();
        $this->clickLink('edit-contacts');
        $this->fillField('reason_for_no_contact_reason', $text);
        $this->pressButton('reason_for_no_contact_saveReason');
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
    }

    /**
     * @Then I say there no assets
     */
    public function noAssets()
    {
        $this->gotoOverview();
        $this->clickLink('edit-assets');
        $this->checkOption('report_noAssetToAdd');
        $this->pressButton('report_saveNoAsset');
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
    }

    /**
     * @Given the report should not be submittable
     */
    public function theReportShouldNotBeSubmittable()
    {
        $this->assertSession()->elementNotExists('css', '#edit-report_add_further_info');
    }
}
