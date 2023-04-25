<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\ReportSectionNavigation;

use App\Tests\Behat\BehatException;

trait ReportSectionNavigationTrait
{
    /**
     * @Then the previous section should be :sectionName
     */
    public function previousSectionShouldBe(string $sectionName)
    {
        $anchor = $this->getSession()->getPage()->find('named', ['link', 'Navigate to previous part']);

        if (!$anchor) {
            throw new BehatException('Previous section link is not visible on the page (searched by title = "Navigate to previous part")');
        }

        $linkTextContainsSectionName = str_contains($anchor->getText(), $sectionName);

        if (!$linkTextContainsSectionName) {
            throw new BehatException(sprintf('Link contained unexpected text. Wanted: %s. Got: %s ', $sectionName, $anchor->getText()));
        }
    }

    /**
     * @Then the next section should be :sectionName
     */
    public function nextSectionShouldBe(string $sectionName)
    {
        $anchor = $this->getSession()->getPage()->find('named', ['link', 'Navigate to next part']);

        if (!$anchor) {
            throw new BehatException('Next section link is not visible on the page (searched by title = "Navigate to next part")');
        }

        $linkTextContainsSectionName = str_contains(strtolower($anchor->getText()), strtolower($sectionName));

        if (!$linkTextContainsSectionName) {
            throw new BehatException(sprintf('Link contained unexpected text. Wanted: %s. Got: %s ', $sectionName, $anchor->getText()));
        }
    }

    /**
     * @Given /^the link to the report overview page should display the correct reporting years$/
     */
    public function theLinkToTheReportOverviewPageShouldDisplayTheCorrectReportingYears()
    {
        $startYear = $this->interactingWithUserDetails->getCurrentReportStartDate()->format('Y');
        $endYear = $this->interactingWithUserDetails->getCurrentReportDueDate()->format('Y');

        $this->assertLinkWithTextIsOnPage(sprintf('%s to %s report overview', $startYear, $endYear));
    }
}
