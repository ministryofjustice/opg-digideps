<?php

namespace DigidepsBehat;

use Behat\Gherkin\Node\PyStringNode;

/**
 * @method Behat\Mink\WebAssert assertSession
 * @method Behat\Mink\Session getSession
 */
trait RegionTrait
{
    /**
     * Assert that the HTML elemtn with class behat-<type>-<element> does not exist.
     *
     * @Then I should not see the :element :type
     */
    public function iShouldNotSeeTheBehatElement($element, $type)
    {
        $this->assertResponseStatus(200);

        //$this->assertResponseStatus(200);
        $regionCss = self::behatElementToCssSelector($element, $type);
        $linksElementsFound = $this->getSession()->getPage()->findAll('css', $regionCss);
        $count = count($linksElementsFound);
        if ($count > 0) {
            throw new \RuntimeException("$count  $regionCss element(s) found. None expected");
        }
    }

    /**
     * @Then I should not see :text text
     */
    public function iShouldNotSee($text)
    {
        $this->assertResponseStatus(200);

        $this->assertSession()->elementTextNotContains('css', 'body', $text);
    }

    /**
     * Assert that the HTML elemtn with class behat-<type>-<element> does not exist.
     *
     * @Then I should see the :element :type
     */
    public function iShouldSeeTheBehatElement($element, $type)
    {
        //$this->assertResponseStatus(200);
        $regionCss = self::behatElementToCssSelector($element, $type);
        $linksElementsFound = $this->getSession()->getPage()->findAll('css', $regionCss);
        if (count($linksElementsFound) === 0) {
            throw new \RuntimeException("Element $regionCss not found");
        }
    }

    /**
     * @Then I should see a subsection called :subsection
     */
    public function iShouldSeeTheSubsection($subsection)
    {
        $elementsFound = $this->getSession()->getPage()->findAll('css', '#'.$subsection.'-subsection');
        if (count($elementsFound) === 0) {
            throw new \RuntimeException("Subsection $subsection not found");
        }
    }

    /**
     * @Then I should see :text in the :region region
     */
    public function iShouldSeeInTheRegion($text, $region)
    {
        //$this->assertResponseStatus(200);
        $this->assertSession()->elementTextContains('css', self::behatElementToCssSelector($region, 'region'), $text);
    }

    /**
     * @Then I should see :text in :section section
     */
    public function iShouldSeeInSection($text, $section)
    {
        $this->assertSession()->elementTextContains('css', '#'.$section.'-section', $text);
    }

    /**
     * @Then I should not see :text in the :section section
     */
    public function iShouldNotSeeInTheSection($text, $section)
    {
        $this->assertResponseStatus(200);

        //$this->assertResponseStatus(200);
        $this->assertSession()->elementTextNotContains('css', '#'.$section.'-section', $text);
    }

    /**
     * @Then I should see :text in :container
     */
    public function iShouldSeeInTheContainer($text, $container)
    {
        $this->assertSession()->elementTextContains('css', '#'.$container.', .'.$container, $text);
    }

    /**
     * @Then the :selector element should be empty
     */
    public function theElementShouldBeEmpty($selector)
    {
        $this->assertSession()->elementExists('css', '#'.$selector);
        if (!empty($this->getSession()->getPage()->find('css', '#'.$selector)->getText())) {
            throw new \RuntimeException('Element Not Empty');
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
        return '.behat-'.$type.'-'.preg_replace('/\s+/', '-', $element);
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
                throw new \RuntimeException('Cookie banner not found');
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
                    throw new \RuntimeException('Cookie banner Visible');
                }
            }
        }
    }

    /**
     * @Then I should see :text in the page header
     */
    public function iShouldSeeInThePageHeader($text)
    {
        $this->assertSession()->elementTextContains('css', '.page-header', $text);
    }

    /**
     * @Then /^I should see a confirmation$/
     */
    public function iShouldSeeAConfirmation()
    {
        $elementsFound = $this->getSession()->getPage()->findAll('css', '.confirm-bar');
        $count = count($elementsFound);
        if ($count < 1) {
            throw new \RuntimeException('No confirmation dialog found');
        }

        if ($elementsFound[0]->isVisible() == false) {
            throw new \RuntimeException('Confirmation dialog not visible');
        }
    }

    /**
     * @Then /^I should see "([^"]*)" in the section title info panel$/
     */
    public function iShouldSeeInSectionTitleInfoPanel($text)
    {
        $css = '#page-section-title-container .info';
        $this->assertSession()->elementTextContains('css', $css, $text);
    }
}
