<?php

declare(strict_types=1);

namespace DigidepsBehat\v2\Reporting\Sections;

trait AdditionalInformationSectionTrait
{
    private string $hasAdditionalInformation;
    private string $additionalInformation;

    private array $formValuesEntered = [];

    private function setAdditionalInformationFormValues(bool $hasAdditionalInformation)
    {
        if ($hasAdditionalInformation) {
            $this->formValuesEntered[] = $this->$hasAdditionalInformation = 'Yes';
            $this->formValuesEntered[] = $this->additionalInformation = $this->faker->text(200);
        } else {
            $this->formValuesEntered[] = $this->$hasAdditionalInformation = 'No';
        }
    }

    /**
     * @Given I view the additional information report section
     */
    public function iViewAdditionalInformationSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'any-other-info');

        $this->visitPath($reportSectionUrl);

        $currentUrl = $this->getCurrentUrl();
        $onSummaryPage = preg_match('/report\/.*\/any-other-info$/', $currentUrl);

        if (!$onSummaryPage) {
            $this->throwContextualException(sprintf('Not on additional information start page. Current URL is: %s', $currentUrl));
        }
    }

    /**
     * @Given I view and start the additional information report section
     */
    public function iViewAndStartAdditionalInformationSection()
    {
        $this->iViewAdditionalInformationSection();

        $this->clickLink('Start any other information');
    }

    /**
     * @Given there is additional information to add
     */
    public function thereIsAdditionalInformationToAdd()
    {
        $this->setAdditionalInformationFormValues(true);

        $this->selectOption('more_info[actionMoreInfo]', 'yes');
        $this->fillField('more_info_actionMoreInfoDetails', $this->additionalInformation);

        $this->pressButton('Save and continue');
    }

    /**
     * @Given there is no additional information to add
     */
    public function thereIsNoAdditionalInformationToAdd()
    {
        $this->selectOption('more_info[actionMoreInfo]', 'no');
        $this->pressButton('Save and continue');
    }

    /**
     * @Then the additional information summary page should contain the details I entered
     */
    public function additionalInformationSummaryPageContainsExpectedText()
    {
        $descriptionList = $this->getSession()->getPage()->find('css', 'dl');

        if (!$descriptionList) {
            $this->throwContextualException('A dl element was not found on the page');
        }

        $missingText = [];
        $html = $descriptionList->getHtml();

        foreach ($this->formValuesEntered as $info) {
            $textVisible = str_contains($html, $info);

            if (!$textVisible) {
                $missingText[] = $info;
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
