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
        $css = '#assets-section .asset-group:nth-child(' . $index .')';
        $this->assertSession()->elementTextContains('css', $css, $text);
    }

    /**
     * @Then the :arg1 asset in the :arg2 asset group should have a :arg3 :arg4
     */
    public function theAssetInTheAssetGroupShouldHaveA($assetIndex, $group, $field, $text)
    {
        $css = '#assets-section [data-group="' . $group . '"] .asset-item:nth-child('. $assetIndex . ') .asset-' . $field . ' .value';
        $this->assertSession()->elementTextContains('css', $css, $text);
    }

    /**
     * @Then the :arg1 asset in the :arg2 asset group should have an empty :arg3
     */
    public function theAssetInTheAssetGroupShouldHaveEmpty($assetIndex, $group, $field)
    {
        $css = '#assets-section [data-group="' . $group . '"] .asset-item:nth-child('. $assetIndex . ') .asset-' . $field . ' .value';

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
        
        if(count($reportNumberSplit) > 1){
            $reportNumber = $reportNumberSplit[count($reportNumberSplit)-1];
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
        /*$startDatePieces = explode('/', $startDateDMY);
        $this->fillField('report_startDate_day', $startDatePieces[0]);
        $this->fillField('report_startDate_month', $startDatePieces[1]);
        $this->fillField('report_startDate_year', $startDatePieces[2]);*/
        
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
     * @When I add the following contact:
     */
    public function IAddtheFollowingContact(TableNode $table)
    {
        $this->clickLink('tab-contacts');
        
        // expand form if collapsed
        if (0 === count($this->getSession()->getPage()->findAll('css', '#contact_contactName'))) {
            $this->clickOnBehatLink('add-a-contact');
        }
        
        $rows = $table->getRowsHash();
        
        $this->fillField('contact_contactName', $rows['contactName']);
        $this->fillField('contact_relationship', $rows['relationship']);
        $this->fillField('contact_explanation', $rows['explanation']);
        $this->fillField('contact_address', $rows['address'][0]);
        $this->fillField('contact_address2', $rows['address'][1]);
        $this->fillField('contact_county', $rows['address'][2]);
        $this->fillField('contact_postcode', $rows['address'][3]);
        if (isset($rows['address'][4])) {
            $this->fillField('contact_country', $rows['address'][4]);
        }
        
        $this->pressButton("contact_save");
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
    }
    
    /**
     * Click on decisions tab and add a decision
     * If the form is not shown, click first on "add-a-decision" button (with no exception thrown)
     * 
     * @When I add the following decision:
     */
    public function IAddtheFollowingDecision(TableNode $table)
    {
        $this->clickLink("tab-decisions");
        
        // expand form if collapsed
        if (0 === count($this->getSession()->getPage()->findAll('css', '#decision_clientInvolvedBoolean_0'))) {
            $this->clickOnBehatLink('add-a-decision');
        }
        
        $rows = $table->getRowsHash();
        
        $this->fillField('decision_description', $rows['description']);
        switch ($rows['clientInvolved'][0]) {
            case 'yes':
                $this->fillField('decision_clientInvolvedBoolean_0', 1);
                break;
            
            case 'no':
                $this->fillField('decision_clientInvolvedBoolean_1', 0);
                break;
            default:
                throw new \RuntimeException("Invalid value for clientInvolved");
        }
        
        $this->fillField('decision_clientInvolvedDetails',  $rows['clientInvolved'][1]);
        
        $this->pressButton("decision_save");
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
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


}