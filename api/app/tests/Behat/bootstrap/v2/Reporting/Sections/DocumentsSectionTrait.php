<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\Reporting\Sections;

use Tests\OPG\Digideps\Backend\Behat\BehatException;

trait DocumentsSectionTrait
{
    // Expected validation errors
    private string $orgCostCertificateMessage = 'Send your final cost certificate for the previous reporting period';

    private array $uploadedDocumentFilenames = [];

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
     * @Then I should see guidance on providing the final cost certificate for the previous reporting period
     */
    public function iShouldSeeFeeGuidance(): void
    {
        $this->assertOnAlertMessage($this->orgCostCertificateMessage);
    }
}
