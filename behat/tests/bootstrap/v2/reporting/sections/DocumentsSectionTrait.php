<?php declare(strict_types=1);

namespace DigidepsBehat\v2\Reporting\Sections;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Exception;
use Faker\Factory;

trait DocumentsSectionTrait
{
    private string $validJpegFilename = 'good.jpg';
    private string $validPngFilename = 'good.png';
    private string $validPdfFilename = 'good.pdf';

    private array $uploadedDocumentFilenames = [];

    /**
     * @Given I view the documents report section
     */
    public function iViewDocumentsSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $activeReportId, 'documents');

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

        if (count($descriptionLists) === 0) {
            $this->throwContextualException('A dl element was not found on the page - make sure the current url is as expected');
        }

        $this->findFileNamesInDls($descriptionLists);
    }

    private function findFileNamesInDls(array $descriptionLists)
    {
        $missingText = [];
        $foundAllFiles = true;

        foreach ($descriptionLists as $descriptionList) {
            $html = $descriptionList->getHtml();
            $foundFile = false;

            foreach ($this->uploadedDocumentFilenames as $uploadedDocumentFilename) {
                $textVisible = str_contains($html, $uploadedDocumentFilename);

                if (!$textVisible) {
                    $missingText[] = $uploadedDocumentFilename;
                    $foundAllFiles = false;
                } else {
                    $foundFile = true;
                }
            }


            if ($foundFile) {
                $missingText = [];
            }
        }

        if (!$foundAllFiles && !empty($missingText)) {
            $this->throwContextualException(
                sprintf(
                    'A dl was found but the row with the expected text was not found. Missing text: %s. HTML found: %s',
                    implode(', ', $missingText),
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
    public function whenIHaveNoFurtherDocumentsToUpload()
    {
        $this->clickLink('Continue');
    }
}
