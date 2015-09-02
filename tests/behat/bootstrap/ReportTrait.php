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

        $this->visitBehatLink('report/' . $reportId . '/change-report-cot/' . $cotNameToId[$cotName]);
    }

    /**
     * @Given I set the report :reportId end date to :days days ago
     */
    public function iSetTheReportDue($reportId, $days)
    {
        $endDate = new \DateTime;
        $endDate->modify("-{$days} days");

        $this->visitBehatLink("report/{$reportId}/change-report-end-date/" . $endDate->format('Y-m-d'));
        $this->visit("/");
    }

    /**
     * @Given I set the report :reportId end date to :days days ahead
     */
    public function iSetTheReportNotDue($reportId, $days)
    {
        $endDate = new \DateTime;
        $endDate->modify("+{$days} days");

        $this->visitBehatLink("report/{$reportId}/change-report-end-date/" . $endDate->format('Y-m-d'));
        $this->visit("/");
    }

    /**
     * @Given I change the report :reportId submitted to :value
     */
    public function iChangeTheReportToNotSubmitted($reportId, $value)
    {
        $this->visitBehatLink('report/' . $reportId . '/set-sumbmitted/' . $value);
    }

    /**
     * @Then the :arg1 asset group should be :arg2
     */
    public function theAssetGroupShouldBe($index, $text)
    {
        $css = '#assets-section .asset-group:nth-child(' . $index . ')';
        $this->assertSession()->elementTextContains('css', $css, $text);
    }

    /**
     * @Then the :arg1 asset in the :arg2 asset group should have a :arg3 :arg4
     */
    public function theAssetInTheAssetGroupShouldHaveA($assetIndex, $group, $field, $text)
    {
        $css = '#assets-section [data-group="' . $group . '"] .asset-item:nth-child(' . $assetIndex . ') .asset-' . $field . ' .value';
        $this->assertSession()->elementTextContains('css', $css, $text);
    }

    /**
     * @Then the :arg1 asset in the :arg2 asset group should have an empty :arg3
     */
    public function theAssetInTheAssetGroupShouldHaveEmpty($assetIndex, $group, $field)
    {
        $css = '#assets-section [data-group="' . $group . '"] .asset-item:nth-child(' . $assetIndex . ') .asset-' . $field . ' .value';

        $elementsFound = $this->getSession()->getPage()->findAll('css', $css);
        if (count($elementsFound) === 0) {
            throw new \RuntimeException("Element not found");
        }

        if ($elementsFound[0]->getText() != '') {
            throw new \RuntimeException("Element should be empty but contains: " . $elementsFound[0]->getText());
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
            throw new \RuntimeException("Element .view-report-link not found");
        }

        if (count($linksElementsFound) > 1) {
            throw new \RuntimeException("Returned multiple elements");
        }

        $url = $linksElementsFound[0]->getAttribute('href');
        $epos = strpos($url, '/display');
        $length = $epos - 8;
        $reportNumber = substr($url, 8, $length);
        $reportNumberSplit = explode('/', $reportNumber);

        if (count($reportNumberSplit) > 1) {
            $reportNumber = $reportNumberSplit[count($reportNumberSplit) - 1];
        }
        $newUrl = '/report/' . $reportNumber . '/formatted';

        $this->visit($newUrl);
    }

    /**
     * @Given I edit lastest active report
     */
    public function iClickActiveReportEditLink()
    {
        $this->visit('/client/show');
        $linksElementsFound = $this->getSession()->getPage()->findAll('css', '.edit-report');

        if (count($linksElementsFound) === 0) {
            throw new \RuntimeException("Element .edit-report not found");
        }

        if (count($linksElementsFound) > 1) {
            throw new \RuntimeException("Returned multiple elements");
        }

        $url = $linksElementsFound[0]->getAttribute('href');

        $this->visit($url);
    }

    /**
     * @When I set the report end date to :endDateDMY
     */
    public function iSetTheReportStartDateToAndEndDateTo($endDateDMY)
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


    private function gotoOverview() {

        $overviewButton = $this->getSession()->getPage()->find('css', '#overview-button');
        
        if (isset($overviewButton)) {
            $overviewButton->click();
        }
        
    }

    /**
     * Click on contacts tab and add a contact
     * If the form is not shown, click first on "add-a-contact" button (with no exception thrown)
     *
     * @When I add the following contacts:
     */
    public function IAddtheFollowingContact(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $this->gotoOverview();
            $this->clickLink('edit-contacts');

            // expand form if collapsed
            if (0 === count($this->getSession()->getPage()->findAll('css', '#contact_contactName'))) {
                $this->clickOnBehatLink('add-a-contact');
            }

            $this->fillField('contact_contactName', $row['contactName']);
            $this->fillField('contact_relationship', $row['relationship']);
            $this->fillField('contact_explanation', $row['explanation']);
            $this->fillField('contact_address', $row['address']);
            $this->fillField('contact_address2', $row['address2']);
            $this->fillField('contact_county', $row['county']);
            $this->fillField('contact_postcode', $row['postcode']);
            $this->fillField('contact_country', $row['country']);

            $this->pressButton("contact_save");
            $this->theFormShouldBeValid();
            $this->assertResponseStatus(200);
        }
    }

    /**
     * Click on decisions tab and add a decision
     * If the form is not shown, click first on "add-a-decision" button (with no exception thrown)
     *
     * @When I add the following decisions:
     */
    public function IAddtheFollowingDecision(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $this->gotoOverview();
            $this->clickLink("edit-decisions");

            // expand form if collapsed
            if (0 === count($this->getSession()->getPage()->findAll('css', '#decision_clientInvolvedBoolean_0'))) {
                $this->clickOnBehatLink('add-a-decision');
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
                    throw new \RuntimeException("Invalid value for clientInvolved");
            }

            $this->fillField('decision_clientInvolvedDetails', $row['clientInvolvedDetails']);

            $this->pressButton("decision_save");
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
            $this->clickLink("edit-assets");

            // click on "Add" if form not present
            if (0 === count($this->getSession()->getPage()->findAll('css', '#asset_title_title'))) {
                $this->clickOnBehatLink('add-an-asset');
            }

            $this->fillField('asset_title_title', $row['title']);
            $this->pressButton("asset_title_next");
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

            $this->pressButton("asset_save");
            $this->theFormShouldBeValid();
            $this->assertResponseStatus(200);
        }
    }
    
    /**
     * @When I set the following safeguarding information:
     */
    public function iSetTheFollowingSafeguarding(TableNode $table)
    {
        $this->gotoOverview();
        $this->clickLink("edit-safeguarding");

        $rows = $table->getRowsHash();

        foreach ($rows as $key => $value) {
            $this->fillField($key, $value);
        }

        $this->pressButton("safeguarding_save");
    }

    /**
     * @When I add the following bank account:
     */
    public function iAddTheFollowingBankAccount(TableNode $table)
    {
        $this->gotoOverview();
        $this->clickLink("edit-accounts");

        // expand form if collapsed
        if (0 === count($this->getSession()->getPage()->findAll('css', '#account_bank'))) {
            $this->clickOnBehatLink('add-account');
        }

        $rows = $table->getRowsHash();

        $this->fillField('account_bank', $rows['bank']);
        $this->fillField('account_accountNumber_part_1', $rows['accountNumber'][0]);
        $this->fillField('account_accountNumber_part_2', $rows['accountNumber'][1]);
        $this->fillField('account_accountNumber_part_3', $rows['accountNumber'][2]);
        $this->fillField('account_accountNumber_part_4', $rows['accountNumber'][3]);
        $this->fillField('account_sortCode_sort_code_part_1', $rows['sortCode'][0]);
        $this->fillField('account_sortCode_sort_code_part_2', $rows['sortCode'][1]);
        $this->fillField('account_sortCode_sort_code_part_3', $rows['sortCode'][2]);

        $datePieces = explode('/', $rows['openingDate']);
        $this->fillField('account_openingDate_day', $datePieces[0]);
        $this->fillField('account_openingDate_month', $datePieces[1]);
        $this->fillField('account_openingDate_year', $datePieces[2]);
        if (isset($rows['openingDateExplanation'])) {
            $this->fillField('account_openingDateExplanation', $rows['openingDateExplanation']);
        }
        $this->fillField('account_openingBalance', $rows['openingBalance']);

        $this->pressButton("account_save");
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);

        // open account and add transactions
        $this->clickOnBehatLink('account-' . implode('', $rows['accountNumber']));
        $this->addTransactions($rows, 'moneyIn_', 'transactions_saveMoneyIn');
        $this->addTransactions($rows, 'moneyOut_', 'transactions_saveMoneyOut');

        // add closing balance
        if (isset($rows['closingDate'])) {
            $closingDatePieces = explode('/', $rows['closingDate']);
            $this->fillField('accountBalance_closingDate_day', $closingDatePieces[0]);
            $this->fillField('accountBalance_closingDate_month', $closingDatePieces[1]);
            $this->fillField('accountBalance_closingDate_year', $closingDatePieces[2]);
            $this->fillField('accountBalance_closingBalance', $rows['closingBalance']);
            $this->pressButton("accountBalance_save");

            if (isset($rows['closingBalanceExplanation']) || isset($rows['closingDateExplanation'])) {
                $this->theFormShouldBeInvalid();

                if (isset($rows['closingBalanceExplanation'])) {
                    $this->fillField('accountBalance_closingBalanceExplanation', $rows['closingBalanceExplanation']);
                }
                if (isset($rows['closingDateExplanation'])) {
                    $this->fillField('accountBalance_closingDateExplanation', $rows['closingDateExplanation']);
                }

                $this->pressButton("accountBalance_save");
            }

            $this->theFormShouldBeValid();
            $this->assertResponseStatus(200);
        }
    }

    private function addTransactions(array $rows, $prefix, $buttonId)
    {
        $records = $this->getRowsMatching($rows, $prefix);
        if (!$records) {
            return;
        }

        foreach ($records as $key => $value) {
            if (is_array($value)) {
                $this->fillField("transactions_{$key}_amount", $value[0]);
                $this->fillField("transactions_{$key}_moreDetails", $value[1]);
            } else {
                $this->fillField("transactions_{$key}_amount", $value);
            }
        }

        // save and return to page
        $this->pressButton($buttonId);
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
    }

    /**
     * @param array $rows
     * @param string $needle
     *
     * @return array
     */
    private function getRowsMatching(array $rows, $needle)
    {
        $ret = $rows;
        foreach ($ret as $k => $value) {
            if (strpos($k, $needle) === false) {
                unset($ret[$k]);
            }
        }

        return $ret;
    }

     /**
     * @When I submit the report with further info :moreInfo
     */
    public function iSubmitTheReportWithFurtherInfo($moreInfo)
    {
        // checkbox top page
        $this->checkOption("report_submit_reviewed_n_checked");
        $this->pressButton("report_submit_submitReport");

        # more info page
        $this->fillField('report_add_info_furtherInformation', $moreInfo);
        $this->pressButton("report_add_info_saveAndContinue");

        # declaration page
        $this->checkOption("report_declaration_agree");
        $this->pressButton("report_declaration_save");

        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
    }

    private function replace_dashes($string) {
        $string = str_replace(" ", "-", $string);
        return strtolower($string);
    }


    /**
     * @Then the report should indicate that the :checkboxvalue checkbox for :checkboxname is checked
     */
    public function theReportShouldIndicateThatTheCheckboxForIsChecked($checkboxvalue, $checkboxname)
    {
        $css = '[data-checkbox="' . $this->replace_dashes($checkboxname) . '--' . $this->replace_dashes($checkboxvalue) . '"]';
        $element = $this->getSession()->getPage()->find('css',$css);

        if(!isset($element)) {
            throw new \RuntimeException("Checkbox not found:$css");
        }

        if ($element->getText() != "X") {
            throw new \RuntimeException("Checkbox not checked");
        }

    }

    /**
     * @Then the report should not indicate that the :checkboxvalue checkbox for :checkboxname is checked
     */
    public function theReportShouldNotIndicateThatTheCheckboxForIsChecked($checkboxvalue, $checkboxname)
    {
        $css = '[data-checkbox="' . $this->replace_dashes($checkboxname) . '--' . $this->replace_dashes($checkboxvalue) . '"]';
        $element = $this->getSession()->getPage()->find('css',$css);

        if(!isset($element)) {
            throw new \RuntimeException("Checkbox not found:$css");
        }

        if ($element->getText() == "X") {
            throw new \RuntimeException("Checkbox not unchecked");
        }

    }

    /**
     * @Then the report should indicate that the :checkbox checkbox is checked
     */
    public function theReportShouldIndicateThatTheCheckboxIsChecked($checkbox)
    {
        $css = '[data-checkbox="' . $this->replace_dashes($checkbox) . '"]';
        $element = $this->getSession()->getPage()->find('css',$css);

        if(!isset($element)) {
            throw new \RuntimeException("Checkbox not found:$css");
        }

        if ($element->getText() != "X") {
            throw new \RuntimeException("Checkbox not checked");
        }
    }

    /**
     * @Then the report should not indicate that the :checkbox checkbox is checked
     */
    public function theReportShouldNotIndicateThatTheCheckboxIsChecked($checkbox)
    {
        $css = '[data-checkbox="' . $this->replace_dashes($checkbox) . '"]';
        $element = $this->getSession()->getPage()->find('css',$css);

        if(!isset($element)) {
            throw new \RuntimeException("Checkbox not found: $css");
        }

        if ($element->getText() == "X") {
            throw new \RuntimeException("Checkbox checked");
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
            throw new \RuntimeException("Element .view-report-link not found");
        }

        if (count($linksElementsFound) > 1) {
            throw new \RuntimeException("Returned multiple elements");
        }

        $url = $linksElementsFound[0]->getAttribute('href');
        $epos = strpos($url, '/display');
        $length = $epos - 8;
        $reportNumber = substr($url, 8, $length);
        $reportNumberSplit = explode('/', $reportNumber);

        if (count($reportNumberSplit) > 1) {
            $reportNumber = $reportNumberSplit[count($reportNumberSplit) - 1];
        }
        $newUrl = '/report/' . $reportNumber . '/display';

        $this->visit($newUrl);
    }


    /**
     * @Then the :question question should be answered with :answer
     */
    public function theQuestionShouldBeAnsweredWith($question, $answer)
    {

        $questionElement = $this->getSession()->getPage()->find('xpath', '//div[text()="' . $question . '"]');

        if (!isset($questionElement)) {
            throw new \RuntimeException("Can't find element with: $question");
        }

        $parent = $questionElement->getParent();
        $answerElement = $parent->find('css', '.answer');

        if (!isset($answerElement)) {
            throw new \RuntimeException("This question has no answers");
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
        $section = $this->getSession()->getPage()->find('css', '#' . $section . '-section');
        if (!isset($section)) {
            throw new \RuntimeException("Can't find section with: $section");
        }

        $questionElement = $section->find('xpath', '//div[text()="' . $question . '"]');

        if (!isset($questionElement)) {
            throw new \RuntimeException("Can't find element with: $question");
        }


        $parent = $questionElement->getParent();
        $answerElement = $parent->find('css', '.answer');

        if (!isset($answerElement)) {
            throw new \RuntimeException("This question has no answers");
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
        $this->clickLink('edit-decisions');
        $this->fillField('reason_for_no_decision_reason', $text);
        $this->pressButton("reason_for_no_decision_saveReason");
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
        $this->pressButton("reason_for_no_contact_saveReason");
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
        $this->checkOption("report_no_assets_no_assets");
        $this->pressButton("report_no_assets_saveNoAsset");
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
    }

}
