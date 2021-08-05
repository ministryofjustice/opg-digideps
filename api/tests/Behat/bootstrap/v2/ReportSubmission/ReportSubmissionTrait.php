<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\ReportSubmission;

use App\Tests\Behat\BehatException;

trait ReportSubmissionTrait
{
    /**
     * @Then I should see the case number of the user I'm interacting with
     */
    public function iShouldSeeInteractingWithCaseNumber()
    {
        $this->assertInteractingWithUserIsSet();

        $caseNumber = $this->interactingWithUserDetails->getCourtOrderNumber();
        var_dump($caseNumber);
        $locator = sprintf('//td[normalize-space()="%s"]/..', $caseNumber);
        var_dump($locator);

        $submissionRow = $this->getSession()->getPage()->find('xpath', $locator);

        if (is_null($submissionRow)) {
            throw new BehatException('Could not find a submission row that contained case number "%s"', $caseNumber);
        }
    }

    /**
     * @When I attach a supporting document :imageName to the report
     */
    public function iAttachedASupportingDocumentToTheCompletedReport(string $imageName)
    {
        $this->iAmOnUploadDocumentPage();
        $this->attachDocument($imageName);
    }

    private function attachDocument(string $imageName)
    {
        $this->attachFileToField('report_document_upload_files', $imageName);
        $this->pressButton('Upload');
    }

    /**
     * @When I view the pending submissions
     */
    public function iViewPendingSubmissions()
    {
        $this->clickLink('Pending');
    }

    /**
     * @Then the report PDF document should be queued
     */
    public function documentsAreSetToQueued()
    {
        $reportPrefix = 'ndr' === $this->interactingWithUserDetails->getCurrentReportNdrOrReport() ? 'NdrRep' : 'DigiRep';
        $reportPdfRow = $this->getSession()->getPage()->find(
            'css',
            sprintf('table tr:contains("%s-")', $reportPrefix)
        );

        if (is_null($reportPdfRow)) {
            throw new BehatException('Cannot find a table row that contains the report PDF');
        }

        if (false === strpos($reportPdfRow->getHtml(), 'Queued')) {
            throw new BehatException('The document does not appear to be queued');
        }
    }

    /**
     * @Then the document :filename should be queued
     */
    public function documentShouldBeQueued(string $fileName)
    {
        $reportPdfRow = $this->getSession()->getPage()->find('css', "table tr:contains('$fileName')");

        if (is_null($reportPdfRow)) {
            throw new BehatException("Cannot find a table row that contains the document with filename $fileName");
        }

        if (false === strpos($reportPdfRow->getHtml(), 'Queued')) {
            throw new BehatException('The document does not appear to be queued');
        }
    }

    /**
     * @Then the document :filename should be synced
     */
    public function documentShouldBeSynced(string $fileName)
    {
        $reportPdfRow = $this->getSession()->getPage()->find('css', "table tr:contains('$fileName')");

        if (is_null($reportPdfRow)) {
            throw new \Exception("Cannot find a table row that contains the document with filename $fileName");
        }

        if (false === strpos($reportPdfRow->getHtml(), 'Success')) {
            throw new \Exception('The document does not appear to be queued');
        }
    }

    /**
     * @Given /^I run the document\-sync command$/
     */
    public function iRunTheDocumentSyncCommand()
    {
        $this->visitAdminPath('/admin/behat/run-document-sync-command');

        if ($this->getSession()->getStatusCode() > 299) {
            throw new \Exception('There was an non successful response when running the document-sync command');
        }

        sleep(2);
    }

    /**
     * @Given /^the report PDF document should be synced$/
     */
    public function theReportPDFDocumentShouldBeSynced()
    {
        $reportPdfRow = $this->getSession()->getPage()->find('css', 'table tr:contains("DigiRep-")');

        if (is_null($reportPdfRow)) {
            throw new \Exception('Cannot find a table row that contains the report PDF');
        }

        if (false === strpos($reportPdfRow->getHtml(), 'Success')) {
            throw new \Exception('The document has not been synced');
        }
    }

    /**
     * @When I attached a supporting document :imageName to the submitted report
     */
    public function attachSupportingDocumentToSubmittedReport(string $imageName)
    {
        $reportId = $this->interactingWithUserDetails->getPreviousReportId();
        $this->visit(sprintf('/report/%s/documents/step/2', $reportId));
        $this->attachDocument($imageName);

        $this->clickLink('Continue to send documents');

        $this->clickLink('Send documents');
    }
}
