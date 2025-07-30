<?php

namespace App\Tests\Behat\Common;

trait LinksTrait
{
    /**
     * @Then the :text link url should contain ":expectedLink"
     */
    public function linkWithTextContains($text, $expectedLink)
    {
        $linksElementsFound = $this->getSession()->getPage()->findAll('xpath', '//a[text()="'.$text.'"]');
        $count = count($linksElementsFound);

        if (0 === count($linksElementsFound)) {
            throw new \RuntimeException('Element not found');
        }

        if (count($linksElementsFound) > 1) {
            throw new \RuntimeException('Returned multiple elements');
        }

        $href = $linksElementsFound[0]->getAttribute('href');

        if (false === strpos($href, $expectedLink)) {
            throw new \Exception("Link: $href does not contain $expectedLink");
        }
    }

    /**
     * Click on element(s) with class "behat-link-$link"; if $link is comma-separated values, each matching
     * link is clicked.
     *
     * @When I click on ":link"
     */
    public function clickOnBehatLink($link)
    {
        // if multiple links are specified (comma-separated), click on all of them
        if (false !== strpos($link, ',')) {
            foreach (explode(',', $link) as $singleLink) {
                $this->clickOnBehatLink(trim($singleLink));
            }

            return;
        }

        // find link inside the region
        $linkSelector = self::behatElementToCssSelector($link, 'link');
        error_log('==================== '.$linkSelector);
        $linksElementsFound = $this->getSession()->getPage()->findAll('css', $linkSelector);
        $count = count($linksElementsFound);

        if (count($linksElementsFound) > 1) {
            throw new \RuntimeException("Found more than one $linkSelector element in the page ($count). Interrupted");
        }
        if (0 === count($linksElementsFound)) {
            $this->clickOnHashLink($link);

            return;
        }

        // click on the found link
        $this->scrollTo($linkSelector);
        $linksElementsFound[0]->click();
    }

    private function clickOnHashLink($link)
    {
        $linksElementsFound = $this->getSession()->getPage()->findAll('css', '#'.$link);
        if (count($linksElementsFound) > 1) {
            throw new \RuntimeException("Found more than a #$link element in the page. Interrupted");
        }
        if (0 === count($linksElementsFound)) {
            throw new \RuntimeException("Element #$link not found. Interrupted");
        }

        // click on the found link
        $this->scrollTo('#'.$link);
        $linksElementsFound[0]->click();
    }

    /**
     * Click on a link with specified text inside the given region.
     *
     * @When I press :text in the :region region
     */
    public function clickOnLinkWithTextInRegion($text, $region)
    {
        $region = $this->findRegion($region);

        $linksElementsFound = $region->findAll('xpath', '//a[normalize-space(text())="'.$text.'"]');
        $count = count($linksElementsFound);
        if (0 === $count) {
            throw new \RuntimeException('Element not found');
        }

        if ($count > 1) {
            throw new \RuntimeException('Returned multiple elements');
        }

        // click on the found link
        $linksElementsFound[0]->click();
    }

    private function findRegion($region)
    {
        // find region
        $regionSelector = '#'.$region.', '.self::behatElementToCssSelector($region, 'region');
        $regionsFound = $this->getSession()->getPage()->findAll('css', $regionSelector);
        if (count($regionsFound) > 1) {
            throw new \RuntimeException("Found more than one $regionSelector");
        }
        if (0 === count($regionsFound)) {
            throw new \RuntimeException("Region $regionSelector not found.");
        }

        return $regionsFound[0];
    }

    /**
     * Click on element with attribute [behat-link=:link] inside the element with attribute [behat-region=:region].
     *
     * @When I click on :link in the :region region
     */
    public function clickLinkInsideElement($link, $region, $theFirst = false)
    {
        $linkSelector = self::behatElementToCssSelector($link, 'link');

        $regionSelector = $this->findRegion($region);
        $linksElementsFound = $regionSelector->findAll('css', $linkSelector);
        if (count($linksElementsFound) > 1 && !$theFirst) {
            throw new \RuntimeException("Found more than 1 $link element inside $region . Interrupted");
        }
        if (0 === count($linksElementsFound)) {
            throw new \RuntimeException("Element $link not found inside $region . Interrupted");
        }

        // click on the found link
        $linksElementsFound[0]->click();
    }

    /**
     * @When I click on the first :link in the :region region
     */
    public function clickFirstLinkInsideElement($link, $region)
    {
        $this->clickLinkInsideElement($link, $region, true);
    }

    /**
     * @Given /^I follow meta refresh$/
     */
    public function iFollowMetaRefresh()
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

        if (null === $row) {
            throw new \Exception('Cannot find a table row with text: '.$rowText);
        }

        return $row;
    }

    /**
     * @Then the :text link, in the :region region, url should contain ":expectedLink"
     * @Then the :text link, in the :region, url should contain ":expectedLink"
     */
    public function linkWithTextInRegionContains($text, $expectedLink, $region)
    {
        $region = $this->findRegion($region);

        $linksElementsFound = $region->findAll('xpath', '//a[normalize-space(text())="'.$text.'"]');
        $count = count($linksElementsFound);

        if (0 === $count) {
            throw new \RuntimeException('Element not found');
        }

        if ($count > 1) {
            throw new \RuntimeException('Returned multiple elements');
        }

        $href = $linksElementsFound[0]->getAttribute('href');

        if (false === strpos($href, $expectedLink)) {
            throw new \Exception("Link: $href does not contain $expectedLink");
        }
    }
}
