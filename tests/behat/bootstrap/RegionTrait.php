<?php

namespace DigidepsBehat;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Exception\ExpectationException;

/**
 * @method Behat\Mink\WebAssert assertSession
 * @method Behat\Mink\Session getSession
 */
trait RegionTrait
{
    
    /**
     * @Then I should not see the :region :type
     */
    public function iShouldNotSeeTheBehatElement($element, $type)
    {
        $regionCss = self::behatElementToCssSelector($element, $type);
        $linksElementsFound = $this->getSession()->getPage()->findAll('css', $regionCss);
        $count = count($linksElementsFound);
        if ($count > 0) {
            throw new \RuntimeException("$count  $regionCss element(s) found. None expected");
        }
    }
    
    /**
     * @Then I should see the :region :type
     */
    public function iShouldSeeTheBehatElement($element, $type)
    {
        $regionCss = self::behatElementToCssSelector($element, $type);
        $linksElementsFound = $this->getSession()->getPage()->findAll('css', $regionCss);
        if (count($linksElementsFound) === 0) {
            throw new \RuntimeException("Element $regionCss not found");
        }
    }
    
    /**
     * @Then I should see :text in the :region region
     */
    public function iShouldSeeInTheRegion($text, $region)
    {
        $this->assertSession()->elementTextContains('css', self::behatElementToCssSelector($region, 'region'), $text);
    }
    
    /**
     * @Then I should not see :text in the :region region
     */
    public function iShouldNotSeeInTheRegion($text, $region)
    {
        $this->assertSession()->elementTextNotContains('css', self::behatElementToCssSelector($region, 'region'), $text);
    }

    /**
     * @Then I should see each of the following in the :region region:
     */
    public function iShouldSeeTheFollowingInTheRegion($region, PyStringNode $pieces)
    {
        foreach ($pieces->getStrings() as $text) {
            $this->iShouldSeeInTheRegion($text, $region);
        }
    }
    
    public static function behatElementToCssSelector($element, $type)
    {
        return '#' . $element . ', .behat-'.$type.'-' . preg_replace('/\s+/', '-', $element);
    }
    
    /**
     * @Then I should see the cookie warning banner
     */
    public function seeCookieBanner()
    {
        $driver = $this->getSession()->getDriver();
        
        if (get_class($driver) != 'Behat\Mink\Driver\GoutteDriver') {
        
            $elementsFound = $this->getSession()->getPage()->findAll('css', '#global-cookie-message');
            if (count($elementsFound) === 0) {
                throw new \RuntimeException("Cookie banner not found");
            }
            
            foreach ($elementsFound as $node) {
                // Note: getText() will return an empty string when using Selenium2D. This
                // is ok since it will cause a failed step.
                if ($node->getText() != '' && $node->isVisible()) {
                    return;
                }
            }
        }
    }
    
    /**
     * @Then I should not see the cookie warning banner
     */
    public function dontSeeCookieBanner()
    {
        $driver = $this->getSession()->getDriver();
        
        if (get_class($driver) != 'Behat\Mink\Driver\GoutteDriver') {
            $elementsFound = $this->getSession()->getPage()->findAll('css', '#global-cookie-message');
        
            if (count($elementsFound) === 0) {
                return;
            }

            foreach ($elementsFound as $node) {
                // Note: getText() will return an empty string when using Selenium2D. This
                // is ok since it will cause a failed step.
                if ($node->getText() != '' && $node->isVisible()) {
                    throw new \RuntimeException("Cookie banner Visible");
                }
            }
        }
    }

    
}