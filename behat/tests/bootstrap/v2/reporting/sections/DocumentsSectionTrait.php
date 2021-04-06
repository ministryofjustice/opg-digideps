<?php declare(strict_types=1);

namespace DigidepsBehat\v2\Reporting\Sections;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Exception;
use Faker\Factory;

trait DocumentsSectionTrait
{
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
     */
    public function theDocumentsSummaryPageShouldContainDocumentsIUploaded()
    {
        $descriptionLists = $this->getSession()->getPage()->findAll('css', 'dl');

        if (count($descriptionLists) === 0) {
            $this->throwContextualException('A dl element was not found on the page - make sure the current url is as expected');
        }

        $missingText = [];

        foreach ($descriptionLists as $descriptionList) {
            $html = $descriptionList->getHtml();
            $found = false;

            foreach ($this->formValuesEntered as $documentFormValues) {
                $textVisible = str_contains($html, $documentFormValues);

                if (!$textVisible) {
                    $missingText[] = $documentFormValues;
                }
            }

            if ($found) {
                $missingText = [];
            }
        }

        if (!empty($missingText)) {
            $this->throwContextualException(
                sprintf(
                    'A dl was found but the row with the expected text was not found. Missing text: %s. HTML found: %s',
                    implode(', ', $missingText),
                    $html
                )
            );
        }
    }
}
