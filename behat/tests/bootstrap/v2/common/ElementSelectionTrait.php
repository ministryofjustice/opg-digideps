<?php declare(strict_types=1);

namespace DigidepsBehat\v2\Common;

trait ElementSelectionTrait
{
    // Click on a specified occurrence of an href based on a regex you specify
    public function iClickOnNthElementBasedOnRegex(string $regex, int $elementIndex)
    {
        $linksArray = [];
        $links = $this->getSession()->getPage()->findAll('css', 'a');

        if (!$links) {
            $this->throwContextualException('A link element was not found on the page');
        }

        foreach ($links as $link) {
            if (preg_match($regex, $link->getAttribute('href'))) {
                array_push($linksArray, $link->getAttribute('href'));
            }
        }

        $xpath = sprintf("//a[@href='%s']", $linksArray[$elementIndex]);
        $session = $this->getSession();
        $element = $session->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath)
        );

        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', $xpath));
        }

        $element->click();
    }

    // Click on a link (a or button css ref for example) based on the value of it's id.
    public function iClickBasedOnElementId(string $elementType, string $attributeValue)
    {
        $xpath = sprintf("//%s[@id='%s']", $elementType, $attributeValue);
        $session = $this->getSession();
        $element = $session->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath)
        );

        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', $xpath));
        }

        $element->click();
    }

    // Returns True if a particular element exists on a page based on parameters we pass in
    public function elementExistsOnPage(string $elementType, string $attributeType, string $attributeValue)
    {
        $xpath = sprintf("//%s[@%s='%s']", $elementType, $attributeType, $attributeValue);
        $session = $this->getSession();
        $element = $session->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath)
        );

        if (null === $element) {
            return false;
        }

        return true;
    }

    // Find a particular radio dialogue on a page and select the Nth option from it based on parameters we pass
    public function iSelectBasedOnChoiceNumber(string $elementType, string $attributeType, string $attributeValue, int $choiceNumber)
    {
        $xpath = sprintf("//%s[@%s='%s']", $elementType, $attributeType, $attributeValue);
        $session = $this->getSession();
        $element = $session->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath)
        );

        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', $xpath));
        }

        $values = $element->findAll('css', 'option');

        $choices = [];

        foreach ($values as $value) {
            array_push($choices, $value->getHtml());
        }

        $element->selectOption(trim($choices[$choiceNumber]));

        return $choices[$choiceNumber];
    }

    // Select radio dialogue based on name
    public function iSelectRadioBasedOnName(string $elementType, string $attributeType, string $attributeValue, string $name)
    {
        $xpath = sprintf("//%s[@%s='%s']//input", $elementType, $attributeType, $attributeValue);
        $session = $this->getSession();
        $values = $session->getPage()->findAll(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath)
        );

        if (null === $values) {
            throw new \InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', $xpath));
        }

        foreach ($values as $value) {
            if ($value->getAttribute('value') == $name) {
                $select = trim($value->getAttribute('name'));
                $option = trim($value->getAttribute('value'));
            }
        }

        $this->getSession()->getPage()->selectFieldOption($select, $option);
    }
}
