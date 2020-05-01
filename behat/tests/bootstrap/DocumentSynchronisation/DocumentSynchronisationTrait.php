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
    }

    /**
     * @Then the documents should be queued
     */
    public function documentsAreSetToQueued()
    {
        $reportPdfRow = $this->getSession()->getPage()->find('css', 'table tr:contains("DigiRep-")');

        if (is_null($reportPdfRow)) {
            throw new \Exception("Cannot find a table row that contains the report PDF");
        }

        $supportingDocRow = $this->getSession()->getPage()->find('css', 'table tr:contains("supporting-document.pdf")');

        if (is_null($supportingDocRow)) {
            throw new \Exception("Cannot find a table row that contains the supporting document");
        }

        foreach([$reportPdfRow, $supportingDocRow] as $row) {
            if (strpos($row->getHtml(), 'Queued') === false) {
                throw new \Exception("The document does not appear to be queued");
            }
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

        $this->attachFileToField('report_document_upload_files', $imageName);
        $this->pressButton('Upload');
        $this->clickLink('Continue to send documents');
        $this->clickLink('Send documents');

    }

}
