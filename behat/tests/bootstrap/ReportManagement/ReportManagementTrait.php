<?php

namespace DigidepsBehat\ReportManagement;

trait ReportManagementTrait
{
    /**
     * @When a case manager changes the report type for the active :startDate to :endDate report between :deputy and :client to :type
     */
    public function aCaseManagerChangesTheReportTypeForTheActiveReportOnTheCourtOrderBetweenAndTo($startDate, $endDate, $deputy, $client, $type)
    {
        // We don't want the deputy type suffix but it improves legibility of the test if it's included in the input. Here we jsut remove it..
        if (strpos($type, '-5') !== false || strpos($type, '-6') !== false) {
            $type = substr($type, 0, -2);
        }

        $this->iAmLoggedInToAdminAsWithPassword('casemanager@publicguardian.gov.uk', 'Abcd1234');
        $this->clickOnBehatLink('client-detail-'.$client);
        $this->clickOnLinkWithTextInRegion('Manage', 'report-'.$startDate.'-to-'.$endDate);
        $this->selectOption('unsubmit_report[type]', $type);
        $this->selectOption('unsubmit_report[dueDateChoice]', 'keep');
        $this->pressButton('unsubmit_report[save]');
        $this->pressButton('unsubmit_report_confirm[save]');
        $this->assertPageContainsText('OPG'.$type);
    }

    /**
     * @When a case manager changes the report type for the submitted :startDate to :endDate report between :deputy and :client to :type
     */
    public function aCaseManagerChangesTheReportTypeForTheSubmittedReportOnTheCourtOrderBetweenAndTo($startDate, $endDate, $deputy, $client, $type)
    {
        // We don't want the deputy type suffix but it improves legibility of the test if it's included in the input. Here we jsut remove it..
        if (strpos($type, '-5') !== false || strpos($type, '-6') !== false) {
            $type = substr($type, 0, -2);
        }

        $this->iAmLoggedInToAdminAsWithPassword('casemanager@publicguardian.gov.uk', 'Abcd1234');
        $this->clickOnBehatLink('client-detail-'.$client);
        $this->clickOnLinkWithTextInRegion('Manage', 'report-'.$startDate.'-to-'.$endDate);
        $this->selectOption('unsubmit_report[type]', $type);
        $this->selectOption('unsubmit_report[dueDateChoice]', 'keep');
        $this->checkOption('Any other information');
        $this->pressButton('unsubmit_report[save]');
        $this->selectOption('unsubmit_report_confirm[confirm]', 'yes');
        $this->pressButton('unsubmit_report_confirm[save]');
        $this->assertPageContainsText('OPG'.$type);
    }
}
