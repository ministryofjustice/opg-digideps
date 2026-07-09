<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\Reporting\Sections;

use OPG\Digideps\Backend\Entity\Report\Document;
use Tests\OPG\Digideps\Backend\Behat\BehatException;

trait DocumentsSectionTrait
{
    // Valid files
    private string $validJpegFilename = 'good-image.jpg';
    private string $validPngFilename = 'good.png';
    private string $validPdfFilename = 'good.pdf';
    private string $validHeicFilename = 'good-heic.heic';
    private string $validJfifFilename = 'good-jfif.jfif';

    // Invalid files
    private string $tooLargeFilename = 'too-big.jpg';
    private string $txtFilename = 'eicar.txt';
    private string $pngFilenameWithJpegFileExtension = 'png-file.jpeg';

    // Expected validation errors
    private string $invalidFileTypeErrorMessage = 'Please upload a valid file type';
    private string $fileTooBigErrorMessage = 'The file you selected to upload is too big';
    private string $answerNotUpdatedErrorMessage = 'Your answer could not be updated to \'No\' because you have attached documents';
    private string $mimeTypeAndFileExtensionDoNotMatchErrorMessage = 'Your file type and file extension do not match';
    private string $orgCostCertificateMessage = 'Send your final cost certificate for the previous reporting period';
    private string $fileDuplicationMessage = 'You have already uploaded a file with this name. Please rename your file before uploading again.';

    private array $uploadedDocumentFilenames = [];

    /**
     * @Given I view and start the documents report section
     */
    public function iViewAndStartDocumentsSection(): void
    {
        $this->iViewDocumentsSection();

        $this->clickLink('Start');
    }

    /**
     * @Given I view the documents report section
     */
    public function iViewDocumentsSection(): void
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $documentsUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'documents');

        $this->visitPath($documentsUrl);
    }

    /**
     * @Given I have documents to upload
     */
    public function iHaveDocumentsToUpload(): void
    {
        if (str_contains($this->getSession()->getCurrentUrl(), 'documents/summary')) {
            $this->clickLink('Edit');
        }

        $this->fillField('document_wishToProvideDocumentation_0', 'yes');
        $this->pressButton('Save and continue');
    }

    private function findFileNamesInDls(array $descriptionLists, array $convertedFileNames = []): void
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
     * @Given I upload one valid document with the filename :filename
     */
    public function iUploadOneValidDocumentWithTheFilename(string $filename): void
    {
        $this->uploadFiles([$filename]);
    }

    /**
     * @Given the document uploads page should contain a document with the filename :filename
     */
    public function theDocumentUploadsPageShouldContainADocumentWithFilename(string $filename): void
    {
        $descriptionLists = $this->findAllCssElements('dl');
        $this->findFileNamesInDls($descriptionLists, [$filename]);
    }

    private function uploadFiles(array $filenames): void
    {
        $this->uploadedDocumentFilenames = $filenames;

        foreach ($filenames as $filename) {
            $this->attachFileToField('report_document_upload_files', $filename);
            $this->pressButton('Upload');
        }
    }

    /**
     * @Given I have no documents to upload
     */
    public function iHaveNoDocumentsToUpload(): void
    {
        $this->fillField('document_wishToProvideDocumentation_1', 'no');

        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see guidance on providing the final cost certificate for the previous reporting period
     */
    public function iShouldSeeFeeGuidance(): void
    {
        $this->assertOnAlertMessage($this->orgCostCertificateMessage);
    }

    /**
     * @Then I should see a 'duplicate file name' error
     */
    public function IShouldSeeADuplicateFileNameError(): void
    {
        $this->assertOnErrorMessage($this->fileDuplicationMessage);
    }

    /**
     * @Given /^the supporting document has expired and is no longer stored in the S3 bucket$/
     */
    public function theSupportingDocumentHasExpiredAndIsNoLongerStoredInTheS3bucket(): void
    {
        $reportId = $this->loggedInUserDetails->getCurrentReportId();

        $docs = $this->em->getRepository(Document::class)->findBy(['report' => $reportId]);

        foreach ($docs as $doc) {
            $this->fixtureHelper->deleteFilesFromS3($doc->getStorageReference());
        }
    }

    /**
     * @Given /^I try to submit my report with the expired document$/
     */
    public function iTryToSubmitMyReportWithTheExpiredDocument(): void
    {
        $this->visitFrontendPath($this->getReportOverviewUrl($this->loggedInUserDetails->getCurrentReportId()));
        $this->clickLink('Preview and check report');
    }

    /**
     * @Then /^I should be redirected to the re\-upload page$/
     */
    public function iShouldBeRedirectedToTheReUploadPage(): void
    {
        $this->iAmOnReUploadPage();
    }

    /**
     * @Given I delete the missing document and re-upload :document to the report
     */
    public function iDeleteTheMissingDocumentAndReUploadToTheReport(string $document): void
    {
        $fileNameSplit = pathinfo($document);
        $fileName = $fileNameSplit['filename'];

        $endSpaces = preg_replace('/\s+(\.[^.]+)$/', '$1', $fileName);
        $remainingSpaces = preg_replace('[[[:blank:]]]', '_', $endSpaces);
        $specialChars = preg_replace('/[^\w_.-]/', '', $remainingSpaces ?? '');
        $underScoresAndPeriods = preg_replace('/([.-])/', '_', $specialChars ?? '') ?? '';

        $formattedDocName = isset($fileNameSplit['extension']) ?
            $underScoresAndPeriods . '.' . $fileNameSplit['extension'] :
            $underScoresAndPeriods;

        $parentOfDtWithTextSelector = sprintf('//dt[contains(text(),"%s")]/..', $formattedDocName);
        $documentRowDiv = $this->getSession()->getPage()->find('xpath', $parentOfDtWithTextSelector);

        if (is_null($documentRowDiv)) {
            throw new BehatException(sprintf('An element containing a dt with the text %s was not found', $document));
        }

        $removeLinkSelector = '//a[contains(text(),"Remove")]';
        $removeLink = $documentRowDiv->find('xpath', $removeLinkSelector);

        if (is_null($removeLink)) {
            throw new BehatException('A link with the text remove was not found in the document row');
        }

        $removeLink->click();
        $this->iAmOnReUploadPage();

        // re-upload document
        $this->attachFileToField('report_document_upload_files', $document);
        $this->pressButton('Upload');

        $descriptionLists = $this->findAllCssElements('dl');
        $this->findFileNamesInDls($descriptionLists, [$formattedDocName]);

        $this->clickLink('Save and continue');
    }
}
