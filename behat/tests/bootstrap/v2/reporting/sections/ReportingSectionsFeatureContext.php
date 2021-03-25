<?php declare(strict_types=1);


namespace DigidepsBehat\v2\Reporting\Sections;

use DigidepsBehat\v2\Common\BaseFeatureContext;

class ReportingSectionsFeatureContext extends BaseFeatureContext
{
    use ContactsSectionTrait;
    use AdditionalInformationSectionTrait;

    const REPORT_SECTION_ENDPOINT = 'report/%s/%s';

    /**
     * @Then the previous section should be :sectionName
     */
    public function previousSectionShouldBe(string $sectionName)
    {
        $anchor = $this->getSession()->getPage()->find('named', ['link', "Navigate to previous part"]);

        if (!$anchor) {
            $this->throwContextualException(
                'Previous section link is not visible on the page (searched by title = "Navigate to previous part")'
            );
        }

        $linkTextContainsSectionName = str_contains($anchor->getText(), $sectionName);

        if (!$linkTextContainsSectionName) {
            $this->throwContextualException(
                sprintf('Link contained unexpected text. Wanted: %s. Got: %s ', $sectionName, $anchor->getText())
            );
        }
    }

    /**
     * @Then the next section should be :sectionName
     */
    public function nextSectionShouldBe(string $sectionName)
    {
        $anchor = $this->getSession()->getPage()->find('named', ['link', "Navigate to next part"]);

        if (!$anchor) {
            $this->throwContextualException(
                'Next section link is not visible on the page (searched by title = "Navigate to next part")'
            );
        }

        $linkTextContainsSectionName = str_contains(strtolower($anchor->getText()), strtolower($sectionName));

        if (!$linkTextContainsSectionName) {
            $this->throwContextualException(
                sprintf('Link contained unexpected text. Wanted: %s. Got: %s ', $sectionName, $anchor->getText())
            );
        }
    }
}
