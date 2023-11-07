<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Tools;

trait ToolsTrait
{
    /**
     * @Given /^I select the Report Reassignment tool$/
     */
    public function iSelectTheReportReassignmentTool()
    {
        $this->iAmOnAdminToolsPage();

        $this->clickLink('Report Reassignment');
    }

    /**
     * @Given /^I reassign the previous reports from a client to the new client$/
     */
    public function iReassignThePreviousReportsFromAClientToTheNewClient()
    {
        $this->iAmOnReportReassignmentPage();

        $firstClientId = $this->layDeputyNotStartedPfaHighAssetsDetails->getClientId();
        $secondClientId = $this->layDeputySubmittedPfaHighAssetsDetails->getClientId();

        $this->changeCaseNumber($secondClientId, $this->layDeputyNotStartedPfaHighAssetsDetails->getClientCaseNumber());

        $this->fillField('report_reassignment[firstClientId]', $firstClientId);
        $this->fillField('report_reassignment[secondClientId]', $secondClientId);
        $this->pressButton('report_reassignment[submit]');
    }
}
