<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait DocumentsSectionTrait
{
    // Valid files
    private string $validJpegFilename = 'good.jpg';
    private string $validPngFilename = 'good.png';
    private string $validPdfFilename = 'good.pdf';

    // Invalid files
    private string $tooLargeFilename = 'too-big.jpg';
    private string $txtFilename = 'eicar.txt';
    private string $csvFilename = 'behat-pa.csv';

    // Expected validation errors
    private string $invalidFileTypeErrorMessage = 'Please upload a valid file type';
    private string $fileTooBigErrorMessage = 'The file you selected to upload is too big';
    private string $answerNotUpdatedErrorMessage = "Your answer could not be updated to 'No' because you have attached documents";
    private string $orgCostCertificateMessage = 'Send your final cost certificate for the previous reporting period';

    private array $uploadedDocumentFilenames = [];

    /**
     * @Given I view the documents report section
     */
    public function iViewDocumentsSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'documents');

        $this->visitPath($reportSectionUrl);

        $currentUrl = $this->getCurrentUrl();
        $onSectionPage = preg_match('/report\/.*\/documents$/', $currentUrl);

        if (!$onSectionPage) {
            $this->throwContextualException(sprintf('Not on documents section page. Current URL is: %s', $currentUrl));
        }
    }

    /**
     * @Given I view and start the documents report section
     */
    public function iViewAndStartDocumentsSection()
    {
        $this->iViewDocumentsSection();

        $this->clickLink('Start');
    }

    /**
     * @Given I have no documents to upload
     */
    public function iHaveNoDocumentsToUpload()
    {
        $this->fillField('document_wishToProvideDocumentation_1', 'no');

        $this->pressButton('Save and continue');
    }

    /**
     * @Given I have documents to upload
     */
    public function iHaveDocumentsToUpload()
    {
        $this->fillField('document_wishToProvideDocumentation_0', 'yes');

        $this->pressButton('Save and continue');
    }

    /**
     * @Then the documents summary page should not contain any documents
     */
    public function theDocumentsSummaryPageShouldNotContainDocuments()
    {
        $descriptionLists = $this->getSession()->getPage()->findAll('css', 'dl');

        if (count($descriptionLists) > 1) {
            $this->throwContextualException('Multiple dl elements found on the page - this suggests documents have been uploaded');
        }
    }

    /**
     * @Then the documents summary page should contain the documents I uploaded
     * @Then the documents uploads page should contain the documents I uploaded
     */
    public function theDocumentsSummaryPageShouldContainDocumentsIUploaded()
    {
        if (empty($this->uploadedDocumentFilenames)) {
            $this->throwContextualException(
                '$this->uploadedDocumentFilenames is empty. This suggests no documents were uploaded.'
            );
        }

        $descriptionLists = $this->getSession()->getPage()->findAll('css', 'dl');

        if (0 === count($descriptionLists)) {
            $this->throwContextualException('A dl element was not found on the page - make sure the current url is as expected');
        }

        $this->findFileNamesInDls($descriptionLists);
    }

    private function findFileNamesInDls(array $descriptionLists)
    {
        $missingFilenames = [];

        foreach ($this->uploadedDocumentFilenames as $uploadedDocumentFilename) {
            $foundFilename = false;

            foreach ($descriptionLists as $descriptionList) {
                $html = $descriptionList->getHtml();
                $textVisible = str_contains($html, $uploadedDocumentFilename);

                if (!$textVisible) {
                    $missingFilenames[] = $uploadedDocumentFilename;
                } else {
                    $foundFilename = true;
                    break;
                }
            }

            if ($foundFilename) {
                $key = array_search($uploadedDocumentFilename, $missingFilenames);
                unset($missingFilenames[$key]);
            }
        }

        if (!empty($missingFilenames)) {
            $this->throwContextualException(
                sprintf(
                    'A dl was found but the row with the expected text was not found. Missing text: %s. HTML found: %s',
                    implode(', ', array_unique($missingFilenames)),
                    $html
                )
            );
        }
    }

    /**
     * @When I upload one valid document
     */
    public function iUploadOneValidDocument()
    {
        $this->uploadFiles([$this->validJpegFilename]);
    }

    /**
     * @When I upload multiple valid documents
     */
    public function iUploadMultipleValidDocuments()
    {
        $this->uploadFiles([$this->validJpegFilename, $this->validPdfFilename, $this->validPngFilename]);
    }

    /**
     * @When I upload one document with an unsupported file type
     */
    public function iUploadOneDocumentWithUnsupportedFileType()
    {
        $this->uploadFiles([$this->txtFilename]);
    }

    /**
     * @When I upload one document that is too large
     */
    public function iUploadOneDocumentThatIsTooLarge()
    {
        $this->uploadFiles([$this->tooLargeFilename]);
    }

    private function uploadFiles(array $filenames)
    {
        $this->uploadedDocumentFilenames = $filenames;

        foreach ($filenames as $filename) {
            $this->attachFileToField('report_document_upload_files', $filename);
            $this->pressButton('Upload');
        }
    }

    /**
     * @When I have no further documents to upload
     */
    public function iHaveNoFurtherDocumentsToUpload()
    {
        $this->clickLink('Continue');
    }

    /**
     * @When I remove one document I uploaded
     */
    public function iRemoveOneDocumentIUploaded()
    {
        $filenames = $this->uploadedDocumentFilenames;
        $documentToPop = $filenames[0];
        unset($filenames[0]);

        $parentOfDtWithTextSelector = sprintf('//dt[contains(text(),"%s")]/..', $documentToPop);
        $documentRowDiv = $this->getSession()->getPage()->find('xpath', $parentOfDtWithTextSelector);

        if (is_null($documentRowDiv)) {
            $this->throwContextualException(
                sprintf('An element containing a dt with the text %s was not found', $documentToPop)
            );
        }

        $removeLinkSelector = '//a[contains(text(),"Remove")]';
        $removeLink = $documentRowDiv->find('xpath', $removeLinkSelector);

        if (is_null($removeLink)) {
            $this->throwContextualException('A link with the text remove was not found in the document row');
        }

        $removeLink->click();
        $this->pressButton('confirm_delete_confirm');

        $this->uploadedDocumentFilenames = $filenames;
    }

    /**
     * @Then I should see an 'invalid file type' error
     */
    public function iShouldSeeInvalidFileTypeError()
    {
        $this->assertOnErrorMessage($this->invalidFileTypeErrorMessage);
    }

    /**
     * @Then I should see a 'file too large' error
     */
    public function iShouldSeeFileTooLargeError()
    {
        $this->assertOnErrorMessage($this->fileTooBigErrorMessage);
    }

    /**
     * @Then I should see an 'answer could not be updated' error
     */
    public function iShouldSeeAnswerCouldNotBeUpdatedError()
    {
        $this->assertOnErrorMessage($this->answerNotUpdatedErrorMessage);
    }

    /**
     * @When I change my mind and confirm I have no documents to upload
     */
    public function changeMindNoDocumentsToUpload()
    {
        $this->clickLink('Edit');
        $this->iHaveNoDocumentsToUpload();
    }

    /**
     * @Then I should see guidance on providing the final cost certificate for the previous reporting period
     */
    public function iShouldSeeFeeGuidance()
    {
        $this->assertOnAlertMessage($this->orgCostCertificateMessage);
    }
}
