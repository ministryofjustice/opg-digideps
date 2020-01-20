<?php

namespace DigidepsBehat\ReportManagement;

trait ReportManagementTrait
{
    /**
     * @When a case manager changes the report type for the :startDate to :endDate report between :deputy and :client to :type
     */
    public function aCaseManagerChangesTheReportTypeForTheToReportOnTheCourtOrderBetweenAndTo($startDate, $endDate, $deputy, $client, $type)
    {
        // We don't want the deputy type suffix but it improves legibility of the test if it's included in the input. Here we jsut remove it..
        if (strpos($type, '-5') !== false || strpos($type, '-6') !== false) {
            $type = substr($type, 0, -2);
        }

        $this->iAmLoggedInToAdminAsWithPassword('casemanager@publicguardian.gov.uk', 'Abcd1234');
        $this->clickOnBehatLink('client-detail-'.$client);
        $this->clickOnLinkWithTextInRegion('Edit report type', 'report-'.$startDate.'-to-'.$endDate);
        $this->selectOption('manage_report_type[type]', $type);
        $this->selectOption('manage_report_type[dueDateChoice]', 'keep');
        $this->pressButton('manage_report_type[save]');
        $this->assertPageContainsText('OPG'.$type);
    }
}
