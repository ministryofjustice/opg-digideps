<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\Common;

use Tests\OPG\Digideps\Backend\Behat\BehatException;

trait ElementSelectionTrait
{
    // Bring back list of items based on css selector with error handling
    public function findAllCssElements($elementType): array
    {
        $listOfElements = $this->getSession()->getPage()->findAll('css', $elementType);

        if (!$listOfElements) {
            throw new BehatException("A $elementType element was not found on the page");
        }

        return $listOfElements;
    }

    // Bring back list of items based on xpath selector with error handling
    public function findAllXpathElements($xpath): array
    {
        $listOfElements = $this->getSession()->getPage()->findAll('xpath', $xpath);

        if (!$listOfElements) {
            throw new BehatException("A '$xpath' element was not found on the page");
        }

        return $listOfElements;
    }

    // Click on a specified occurrence of an href based on a regex you specify
    public function iClickOnNthElementBasedOnRegex(string $regex, int $elementIndex): void
    {
        $linksArray = [];
        $links = $this->getSession()->getPage()->findAll('css', 'a');

        if (!$links) {
            throw new BehatException('A link element was not found on the page');
        }

        foreach ($links as $link) {
            if (preg_match($regex, $link->getAttribute('href'))) {
                $linksArray[] = $link->getAttribute('href');
            }
        }

        $xpath = sprintf("//a[@href='%s']", $linksArray[$elementIndex]);

        $session = $this->getSession();
        $element = $session->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath)
        );

        if ($element === null) {
            throw new \InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', $xpath));
        }

        $element->click();
    }

    public function clickBasedOnText($text): void
    {
        $xpath = sprintf('//a[text()[contains(., \'%s\')]]', $text);
        $link = $this->getSession()->getPage()->find('xpath', $xpath);
        $link->click();
    }

    // Click on a link (a or button css ref for example) based on the value of it's attribute type.
    public function iClickBasedOnAttributeTypeAndValue(string $elementType, string $attributeType, string $attributeValue): void
    {
        $xpath = sprintf("//%s[@%s='%s']", $elementType, $attributeType, $attributeValue);

        $element = $this->getSession()->getPage()->find(
            'xpath',
            $xpath
        );

        if ($element === null) {
            throw new \InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', $xpath));
        }

        $element->click();
    }

    // Returns True if a particular element exists on a page based on parameters we pass in
    public function elementExistsOnPage(string $elementType, string $attributeType, string $attributeValue): bool
    {
        $xpath = sprintf("//%s[@%s='%s']", $elementType, $attributeType, $attributeValue);
        $session = $this->getSession();
        $element = $session->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath)
        );

        if ($element === null) {
            return false;
        }

        return true;
    }
}
