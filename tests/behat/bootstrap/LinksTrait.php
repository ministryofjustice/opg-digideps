<?php

namespace DigidepsBehat;

trait LinksTrait
{

    /**
     * @Then the :text link url should contain ":expectedLink"
     */
    public function linkWithTextContains($text, $expectedLink)
    {

        $linksElementsFound = $this->getSession()->getPage()->find('xpath', '//a[text()="' . $text . '"]');
        $count = count($linksElementsFound);

        if (count($linksElementsFound) === 0) {
            throw new \RuntimeException("Element not found");
        }

        if (count($linksElementsFound) > 1) {
            throw new \RuntimeException("Returned multiple elements");
        }

        $href = $linksElementsFound->getAttribute('href');

        if (strpos($href, $expectedLink) === FALSE) {
            throw new \Exception("Link: $href does not contain $expectedLink");
        }
    }

    /**
     * @Given I visit the behat link :link
     */
    public function visitBehatLink($link)
    {
        $secret = md5('behat-dd-' . $this->getSymfonyParam('secret'));

        $this->visit("/behat/{$secret}/{$link}");
        // non-200 response -> debug content
        //if (200 != $this->getSession()->getStatusCode()) {
        //   $this->printLastResponse();
        //}
    }

    /**
     * Click on element with attribute [behat-link=:link]
     * 
     * @When I click on ":link"
     */
    public function clickOnBehatLink($link)
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

    private function clickOnHashLink($link)
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
     * Click on element with attribute [behat-link=:link]
     * 
     * @When I click on link with text :text
     */
    public function clickOnLinkWithText($text)
    {
        $linksElementsFound = $this->getSession()->getPage()->find('xpath', '//*[text()="' . $text . '"]');
        $count = count($linksElementsFound);

        if (count($linksElementsFound) === 0) {
            throw new \RuntimeException("Element not found");
        }

        if (count($linksElementsFound) > 1) {
            throw new \RuntimeException("Returned multiple elements");
        }

        // click on the found link
        $linksElementsFound[0]->click();
    }

    /**
     * Click on element with attribute [behat-link=:link]
     * 
     * @When I click on link with text :text in region :region
     * @When I press :text in region :region
     * @When I press :text in the :region
     */
    public function clickOnLinkWithTextInRegion($text, $region)
    {
        $region = $this->findRegion($region);

        $linksElementsFound = $region->find('xpath', '//a[text()="' . $text . '"]');
        $count = count($linksElementsFound);

        if (count($linksElementsFound) === 0) {
            throw new \RuntimeException("Element not found");
        }

        if (count($linksElementsFound) > 1) {
            throw new \RuntimeException("Returned multiple elements");
        }

        // click on the found link
        $linksElementsFound->click();
    }

    private function findRegion($region)
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
     * Click on element with attribute [behat-link=:link] inside the element with attribute [behat-region=:region]
     * 
     * @When I click on :link in the :region region
     */
    public function clickLinkInsideElement($link, $region)
    {
        $this->findRegion($region);

        // find link inside the region
        $linkSelector = self::behatElementToCssSelector($link, 'link');
        $linksElementsFound = $regionsFound[0]->findAll('css', $linkSelector);
        if (count($linksElementsFound) > 1) {
            throw new \RuntimeException("Found more than a $linkSelector element inside $regionSelector . Interrupted");
        }
        if (count($linksElementsFound) === 0) {
            throw new \RuntimeException("Element $linkSelector not found inside $regionSelector . Interrupted");
        }


        // click on the found link
        $linksElementsFound[0]->click();
    }

    /**
     * @Then the :text link, in the :region region, url should contain ":expectedLink"
     * @Then the :text link, in the :region, url should contain ":expectedLink"
     */
    public function linkWithTextInRegionContains($text, $expectedLink, $region)
    {

        $region = $this->findRegion($region);

        $linksElementsFound = $region->find('xpath', '//a[text()="' . $text . '"]');
        $count = count($linksElementsFound);

        if (count($linksElementsFound) === 0) {
            throw new \RuntimeException("Element not found");
        }

        if (count($linksElementsFound) > 1) {
            throw new \RuntimeException("Returned multiple elements");
        }

        $href = $linksElementsFound->getAttribute('href');

        if (strpos($href, $expectedLink) === FALSE) {
            throw new \Exception("Link: $href does not contain $expectedLink");
        }
    }

}
