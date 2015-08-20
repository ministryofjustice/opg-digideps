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

    /**
     * Click on contacts tab and add a contact
     * If the form is not shown, click first on "add-a-contact" button (with no exception thrown)
     * 
     * @When I add the following contacts:
     */
    public function IAddtheFollowingContact(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $this->clickLink('tab-contacts');

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
            $this->clickLink("tab-decisions");

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
            $this->clickLink("tab-assets");

            // expand form if collapsed
            if (0 === count($this->getSession()->getPage()->findAll('css', '#asset_title'))) {
                $this->clickOnBehatLink('add-an-asset');
            }

            $this->fillField('asset_title', $row['title']);
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
     * @When I add the following bank account:
     */
    public function iAddTheFollowingBankAccountAssets(TableNode $table)
    {
        $this->clickLink("tab-accounts");

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

}