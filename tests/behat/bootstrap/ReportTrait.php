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
}