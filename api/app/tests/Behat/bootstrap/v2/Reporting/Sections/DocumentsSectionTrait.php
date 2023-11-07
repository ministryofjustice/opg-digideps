<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

use App\Tests\Behat\BehatException;

trait DocumentsSectionTrait
{
    // Valid files
    private string $validJpegFilename = 'good.jpg';
    private string $validPngFilename = 'good.png';
    private string $validPdfFilename = 'good.pdf';
    private string $validHeicFilename = 'good-heic.heic';
    private string $validJfifFilename = 'good-jfif.jfif';

    // Invalid files
    private string $tooLargeFilename = 'too-big.jpg';
    private string $txtFilename = 'eicar.txt';
    private string $csvFilename = 'behat-pa.csv';
    private string $pngFilenameWithJpegFileExtension = 'png-file.jpeg';

    // Expected validation errors
    private string $invalidFileTypeErrorMessage = 'Please upload a valid file type';
    private string $fileTooBigErrorMessage = 'The file you selected to upload is too big';
    private string $answerNotUpdatedErrorMessage = "Your answer could not be updated to 'No' because you have attached documents";
    private string $mimeTypeAndFileExtensionDoNotMatchErrorMessage = 'Your file type and file extension do not match';
    private string $orgCostCertificateMessage = 'Send your final cost certificate for the previous reporting period';

    private array $uploadedDocumentFilenames = [];

    /**
     * @Given I view and start the documents report section
     */
    public function iViewAndStartDocumentsSection()
    {
        $this->iViewDocumentsSection();

        $this->clickLink('Start');
    }

    /**
     * @Given I view the documents report section
     */
    public function iViewDocumentsSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $documentsUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'documents');

        $this->visitPath($documentsUrl);
    }

    /**
     * @Given I have documents to upload
     */
    public function iHaveDocumentsToUpload()
    {
        if (str_contains($this->getSession()->getCurrentUrl(), 'documents/summary')) {
            $this->clickLink('Edit');
        }

        $this->fillField('document_wishToProvideDocumentation_0', 'yes');
        $this->pressButton('Save and continue');
    }

    /**
     * @Then the documents summary page should not contain any documents
     * @Then the send more documents page should not contain any documents to upload
     */
    public function theDocumentsSummaryPageShouldNotContainDocuments()
    {
        $descriptionLists = $this->getSession()->getPage()->findAll('css', 'dl');

        if (count($descriptionLists) > 1) {
            throw new BehatException('Multiple dl elements found on the page - this suggests documents have been uploaded');
        }
    }

    /**
     * @Then the documents summary page should contain the documents I uploaded
     * @Then the documents uploads page should contain the documents I uploaded
     */
    public function theDocumentsSummaryPageShouldContainDocumentsIUploaded()
    {
        if (empty($this->uploadedDocumentFilenames)) {
            throw new BehatException('$this->uploadedDocumentFilenames is empty. This suggests no documents were uploaded.');
        }

        $descriptionLists = $this->findAllCssElements('dl');

        $this->findFileNamesInDls($descriptionLists);
    }

    /**
     * @Then the documents summary page should contain the documents I uploaded with converted filenames
     * @Then the documents uploads page should contain the documents I uploaded with converted filenames
     */
    public function theDocumentsSummaryPageShouldContainDocumentsIUploadedConvertedFilenames()
    {
        if (empty($this->uploadedDocumentFilenames)) {
            throw new BehatException('$this->uploadedDocumentFilenames is empty. This suggests no documents were uploaded.');
        }

        $descriptionLists = $this->findAllCssElements('dl');

        $this->findFileNamesInDls($descriptionLists, ['goodheic.jpeg', 'goodjfif.jpeg']);
    }

    private function findFileNamesInDls(array $descriptionLists, array $convertedFileNames = [])
    {
        $missingFilenames = [];

        $fileNamesToFind = empty($convertedFileNames) ? $this->uploadedDocumentFilenames : $convertedFileNames;

        foreach ($fileNamesToFind as $uploadedDocumentFilename) {
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
            throw new BehatException(sprintf('A dl was found but the row with the expected text was not found. Missing text: %s. HTML found: %s', implode(', ', array_unique($missingFilenames)), $html));
        }
    }

    /**
     * @When I upload one valid document
     */
    public function iUploadOneValidDocument()
    {
        $this->uploadFiles([$this->validJpegFilename]);
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
     * @When I upload multiple valid documents that do not require conversion
     */
    public function iUploadMultipleValidDocumentsNoConvesion()
    {
        $this->uploadFiles([$this->validJpegFilename, $this->validPdfFilename, $this->validPngFilename]);
    }

    /**
     * @When I upload multiple valid documents that require conversion
     */
    public function iUploadMultipleValidDocumentsRequireConversion()
    {
        $this->uploadFiles([$this->validHeicFilename, $this->validJfifFilename]);
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
        ini_set('memory_limit', '512M');

        $this->uploadFiles([$this->tooLargeFilename]);
    }

    /**
     * @When I have no further documents to upload
     */
    public function iHaveNoFurtherDocumentsToUpload()
    {
        try {
            $this->clickLink('Continue');
        } catch (\Throwable $e) {
            $this->clickLink('Continue to send documents');
        }
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
            throw new BehatException(sprintf('An element containing a dt with the text %s was not found', $documentToPop));
        }

        $removeLinkSelector = '//a[contains(text(),"Remove")]';
        $removeLink = $documentRowDiv->find('xpath', $removeLinkSelector);

        if (is_null($removeLink)) {
            throw new BehatException('A link with the text remove was not found in the document row');
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
     * @Given I have no documents to upload
     */
    public function iHaveNoDocumentsToUpload()
    {
        $this->fillField('document_wishToProvideDocumentation_1', 'no');

        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see guidance on providing the final cost certificate for the previous reporting period
     */
    public function iShouldSeeFeeGuidance()
    {
        $this->assertOnAlertMessage($this->orgCostCertificateMessage);
    }

    /**
     * @Given I upload a file where the mimetype and file extension do not match
     */
    public function filesMimetypeAndExtensionDoesNotMatch()
    {
        $this->uploadFiles([$this->pngFilenameWithJpegFileExtension]);
    }

    /**
     * @Then I should see a 'mimetype and file type do not match' error
     */
    public function iShouldSeeAMimetypeAndFileDoNotMatchError()
    {
        $this->assertOnErrorMessage($this->mimeTypeAndFileExtensionDoNotMatchErrorMessage);
    }

    /**
     * @When /^I continue to submit the empty form$/
     */
    public function iContinueToSubmitTheEmptyForm()
    {
        $this->clickLink('Send documents');
        $this->iAmOnLayMainPage();
    }
}
