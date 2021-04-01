<?php declare(strict_types=1);

namespace DigidepsBehat\v2\Common;

trait ElementSelectionTrait
{
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
}
