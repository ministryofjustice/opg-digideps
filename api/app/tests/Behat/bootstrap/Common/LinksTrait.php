<?php

namespace Tests\OPG\Digideps\Backend\Behat\Common;

use Behat\Mink\Element\NodeElement;

trait LinksTrait
{
    /**
     * @Then the :text link url should contain ":expectedLink"
     */
    public function linkWithTextContains($text, $expectedLink): void
    {
        $linksElementsFound = $this->getSession()->getPage()->findAll('xpath', '//a[text()="' . $text . '"]');
        $count = count($linksElementsFound);

        if (count($linksElementsFound) === 0) {
            throw new \RuntimeException('Element not found');
        }

        if (count($linksElementsFound) > 1) {
            throw new \RuntimeException('Returned multiple elements');
        }

        $href = $linksElementsFound[0]->getAttribute('href');

        if (strpos($href, $expectedLink) === false) {
            throw new \Exception("Link: $href does not contain $expectedLink");
        }
    }

    /**
     * Click on element(s) with class "behat-link-$link"; if $link is comma-separated values, each matching
     * link is clicked.
     *
     * @When I click on ":link"
     */
    public function clickOnBehatLink($link): void
    {
        // if multiple links are specified (comma-separated), click on all of them
        if (strpos($link, ',') !== false) {
            foreach (explode(',', $link) as $singleLink) {
                $this->clickOnBehatLink(trim($singleLink));
            }

            return;
        }

        // find link inside the region
        $linkSelector = self::behatElementToCssSelector($link, 'link');
        $linksElementsFound = $this->getSession()->getPage()->findAll('css', $linkSelector);
        $count = count($linksElementsFound);

        if (count($linksElementsFound) > 1) {
            throw new \RuntimeException("Found more than one $linkSelector element in the page ($count). Interrupted");
        }
        if (count($linksElementsFound) === 0) {
            $this->clickOnHashLink($link);

            return;
        }

        // click on the found link
        $this->scrollTo($linkSelector);
        $linksElementsFound[0]->click();
    }

    private function clickOnHashLink($link): void
    {
        $linksElementsFound = $this->getSession()->getPage()->findAll('css', '#' . $link);
        if (count($linksElementsFound) > 1) {
            throw new \RuntimeException("Found more than a #$link element in the page. Interrupted");
        }
        if (count($linksElementsFound) === 0) {
            throw new \RuntimeException("Element #$link not found. Interrupted");
        }

        // click on the found link
        $this->scrollTo('#' . $link);
        $linksElementsFound[0]->click();
    }

    /**
     * Click on a link with specified text inside the given region.
     *
     * @When I press :text in the :region region
     */
    public function clickOnLinkWithTextInRegion($text, $region): void
    {
        $region = $this->findRegion($region);

        $linksElementsFound = $region->findAll('xpath', '//a[normalize-space(text())="' . $text . '"]');
        $count = count($linksElementsFound);
        if ($count === 0) {
            throw new \RuntimeException('Element not found');
        }

        if ($count > 1) {
            throw new \RuntimeException('Returned multiple elements');
        }

        // click on the found link
        $linksElementsFound[0]->click();
    }

    private function findRegion($region): NodeElement
    {
        // find region
        $regionSelector = '#' . $region . ', ' . self::behatElementToCssSelector($region, 'region');
        $regionsFound = $this->getSession()->getPage()->findAll('css', $regionSelector);
        if (count($regionsFound) > 1) {
            throw new \RuntimeException("Found more than one $regionSelector");
        }
        if (count($regionsFound) === 0) {
            throw new \RuntimeException("Region $regionSelector not found.");
        }

        return $regionsFound[0];
    }

    /**
     * Click on element with attribute [behat-link=:link] inside the element with attribute [behat-region=:region].
     *
     * @When I click on :link in the :region region
     */
    public function clickLinkInsideElement($link, $region, $theFirst = false): void
    {
        $linkSelector = self::behatElementToCssSelector($link, 'link');

        $regionSelector = $this->findRegion($region);
        $linksElementsFound = $regionSelector->findAll('css', $linkSelector);
        if (count($linksElementsFound) > 1 && !$theFirst) {
            throw new \RuntimeException("Found more than 1 $link element inside $region . Interrupted");
        }
        if (count($linksElementsFound) === 0) {
            throw new \RuntimeException("Element $link not found inside $region . Interrupted");
        }

        // click on the found link
        $linksElementsFound[0]->click();
    }

    /**
     * @When I click on the first :link in the :region region
     */
    public function clickFirstLinkInsideElement($link, $region): void
    {
        $this->clickLinkInsideElement($link, $region, true);
    }

    /**
     * @Given /^I follow meta refresh$/
     */
    public function iFollowMetaRefresh(): void
    {
        while ($refresh = $this->getSession()->getPage()->find('css', 'meta[http-equiv="refresh"]')) {
            $content = $refresh->getAttribute('content');
            $url = preg_replace('/^\d+;\s*URL=/i', '', $content);

            $this->getSession()->visit($url);
        }
    }

    private function findRowByText($rowText)
    {
        $row = $this->getSession()->getPage()->find('css', sprintf('table tr:contains("%s")', $rowText));

        if ($row === null) {
            throw new \Exception('Cannot find a table row with text: ' . $rowText);
        }

        return $row;
    }

    /**
     * @Then the :text link, in the :region region, url should contain ":expectedLink"
     * @Then the :text link, in the :region, url should contain ":expectedLink"
     */
    public function linkWithTextInRegionContains($text, string $expectedLink, $region): void
    {
        $region = $this->findRegion($region);

        $linksElementsFound = $region->findAll('xpath', '//a[normalize-space(text())="' . $text . '"]');
        $count = count($linksElementsFound);

        if ($count === 0) {
            throw new \RuntimeException('Element not found');
        }

        if ($count > 1) {
            throw new \RuntimeException('Returned multiple elements');
        }

        $href = $linksElementsFound[0]->getAttribute('href');

        if (!str_contains($href ?? '', $expectedLink)) {
            throw new \Exception("Link: $href does not contain $expectedLink");
        }
    }
}
