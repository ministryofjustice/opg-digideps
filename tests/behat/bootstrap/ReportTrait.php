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
     * @Given I set the report :reportId due
     */
    public function iSetTheReportDue($reportId)
    {
       $endDate = new \DateTime;
       $endDate->modify('-3 days');
       
       $this->visitBehatLink("report/{$reportId}/change-report-end-date/" . $endDate->format('Y-m-d'));
       $this->visit("/");
    }
    
    /**
     * @Given I set the report :reportId not due
     */
    public function iSetTheReportNotDue($reportId)
    {
       $endDate = new \DateTime;
       $endDate->modify('+3 days');
       
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
}