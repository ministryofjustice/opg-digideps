<?php declare(strict_types=1);

namespace DigidepsBehat\DocumentSynchronisation;

use Behat\Gherkin\Node\TableNode;

trait DocumentSynchronisationTrait
{
    /**
     * @Given I view the submissions page
     */
    public function iAmOnSubmissionsPage()
    {
        $this->visitAdminPath('/admin/documents/list');

        if ($this->getSession()->getStatusCode() > 299) {
            throw new \Exception("There was an non successful response when accessing /admin/documents/list");
        }
    }

    /**
     * @Then the report PDF document should be queued
     */
    public function documentsAreSetToQueued()
    {
        $reportPdfRow = $this->getSession()->getPage()->find('css', 'table tr:contains("DigiRep-")');

        if (is_null($reportPdfRow)) {
            throw new \Exception("Cannot find a table row that contains the report PDF");
        }

        if (strpos($reportPdfRow->getHtml(), 'Queued') === false) {
            throw new \Exception("The document does not appear to be queued");
        }
    }

    /**
     * @Then the document :filename should be queued
     */
    public function documentShouldBeQueued(string $fileName)
    {
        $reportPdfRow = $this->getSession()->getPage()->find('css', "table tr:contains('$fileName')");

        if (is_null($reportPdfRow)) {
            throw new \Exception("Cannot find a table row that contains the document with filename $fileName");
        }

        if (strpos($reportPdfRow->getHtml(), 'Queued') === false) {
            throw new \Exception("The document does not appear to be queued");
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

        if (strpos($reportPdfRow->getHtml(), 'Success') === false) {
            throw new \Exception("The document does not appear to be queued");
        }
    }

    /**
     * @When I attached a supporting document :imageName to the submitted report
     */
    public function attachSupportingDocumentToSubmittedReport(string $imageName)
    {
        $this->visit('/');

        try {
            $this->clickLink('Attach documents');
        } catch(\Throwable $e) {
            $this->clickOnBehatLink('pa-report-open');
            $this->clickLink('Attach documents');
        }

        $this->attachDocument($imageName);
    }

    /**
     * @When I attached a supporting document :imageName to the completed report
     */
    public function iAttachedASupportingDocumentToTheCompletedReport(string $imageName)
    {
        $this->visit('/');

        try {
            $this->clickOnBehatLink('report-start');
        } catch(\Throwable $e) {
            $this->clickOnBehatLink('pa-report--open');
        }

        $this->clickOnBehatLink('edit-documents');
        $this->clickOnBehatLink('edit');
        $this->selectOption('document[wishToProvideDocumentation]','yes');
        $this->clickOnBehatLink('save-and-continue');

        $this->attachDocument($imageName);
    }

    /**
     * @Given /^I run the document\-sync command$/
     */
    public function iRunTheDocumentSyncCommand()
    {
        $this->visitAdminPath('/admin/run-document-sync-command');

        if ($this->getSession()->getStatusCode() > 299) {
            throw new \Exception("There was an non successful response when running the document-sync command");
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
            throw new \Exception("Cannot find a table row that contains the report PDF");
        }

        if (strpos($reportPdfRow->getHtml(), 'Success') === false) {
            throw new \Exception("The document has not been synced");
        }
    }

    private function attachDocument(string $imageName)
    {
        $this->attachFileToField('report_document_upload_files', $imageName);
        $this->pressButton('Upload');
    }
}
