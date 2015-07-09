<?php

namespace DigidepsBehat;

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
        $css = '#assets-section [data-group="' . $group . '"] .asset-item:nth-child('. $assetIndex . ') .asset-' . $field;
        $this->assertSession()->elementTextContains('css', $css, $text);
    }
}