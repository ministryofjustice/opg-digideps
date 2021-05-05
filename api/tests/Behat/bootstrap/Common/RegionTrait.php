<?php

namespace DigidepsBehat\Common;

use Behat\Gherkin\Node\TableNode;

/**
 * @method Behat\Mink\WebAssert assertSession
 */
trait RegionTrait
{
    /**
     * Assert that the HTML element with class behat-<type>-<element> does not exist.
     *
     * @Then I should not see the :element :type
     */
    public function iShouldNotSeeTheBehatElement($element, $type)
    {
        $this->assertResponseStatus(200);

        $regionCss = self::behatElementToCssSelector($element, $type);
        $linksElementsFound = $this->getSession()->getPage()->findAll('css', $regionCss);
        $count = count($linksElementsFound);
        if ($count > 0) {
            throw new \RuntimeException("$count  $regionCss element(s) found. None expected");
        }
    }

    /**
     * Assert that the HTML element with class behat-<type>-<element> exists.
     *
     * @Then I should see the :element :type
     */
    public function iShouldSeeTheBehatElement($element, $type)
    {
        $regionCss = self::behatElementToCssSelector($element, $type);
        $found = count($this->getSession()->getPage()->findAll('css', $regionCss));
        if (1 !== $found) {
            throw new \RuntimeException("One $regionCss class expected, $found found");
        }
    }

    /**
     * Assert that the HTML element with class behat-<type>-<element> exist N times.
     *
     * @Then I should see the :element :type exactly :n times
     */
    public function iShouldSeeTheBehatElementNTimes($element, $type, $n)
    {
        $regionCss = self::behatElementToCssSelector($element, $type);
        $linksElementsFound = $this->getSession()->getPage()->findAll('css', $regionCss);
        if (($c = count($linksElementsFound)) != $n) {
            throw new \RuntimeException("Found $c instances of $regionCss, $n expected");
        }
    }

    /**
     * @Then I should see :text in the :region region
     */
    public function iShouldSeeInTheRegion($text, $region)
    {
        // assert only one region is present
        $regionCss = self::behatElementToCssSelector($region, 'region');
        $found = count($this->getSession()->getPage()->findAll('css', $regionCss));
        if (1 !== $found) {
            throw new \RuntimeException("Can't assert text existing in region $region, $found found");
        }

        $this->assertSession()->elementTextContains('css', $regionCss, $text);
    }

    /**
     * @Then I should see the :region region in the :parentRegion region
     */
    public function iShouldSeeTheRegionInTheRegion($regionId, $parentRegionId)
    {
        $parentRegionCss = self::behatElementToCssSelector($parentRegionId, 'region');
        $parentRegion = $this->getSession()->getPage()->find('css', $parentRegionCss);

        if (null === $parentRegion) {
            throw new \RuntimeException("Can't find region $parentRegionId");
        }

        $regionCss = self::behatElementToCssSelector($regionId, 'region');
        $region = $parentRegion->find('css', $regionCss);

        if (null === $region) {
            throw new \RuntimeException("Can't find region $regionId in $parentRegionId");
        }
    }

    /**
     * @Then each text should be present in the corresponding region:
     */
    public function eachTextShouldBePresentCorrespondingRegion(TableNode $fields)
    {
        $errorMessages = [];
        foreach ($fields->getRowsHash() as $text => $region) {
            try {
                $this->iShouldSeeInTheRegion($text, $region);
            } catch (\Throwable $e) {
                $errorMessages[] = $e->getMessage();
            }
        }

        if ($errorMessages) {
            throw new \RuntimeException(implode("\n", $errorMessages));
        }
    }

    /**
     * @Then I should not see :text in the :region region
     */
    public function iShouldNotSeeInTheRegion($text, $region)
    {
        $this->assertResponseStatus(200);

        $this->assertSession()->elementTextNotContains('css', self::behatElementToCssSelector($region, 'region'), $text);
    }

    public static function behatElementToCssSelector($element, $type)
    {
        return '.behat-'.$type.'-'.preg_replace('/\s+/', '-', $element);
    }
}
