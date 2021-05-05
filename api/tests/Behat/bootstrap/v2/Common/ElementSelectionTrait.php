<?php

declare(strict_types=1);

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

    // Click on a link (a or button css ref for example) based on the value of it's attribute type.
    public function iClickBasedOnAttributeTypeAndValue(string $elementType, string $attributeType, string $attributeValue)
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

    // Sets fields in a way that we can use in our cross browser tests
    public function iFillFieldForCrossBrowser($field, $value)
    {
        $driver = $this->getSession()->getDriver();
        $field = str_replace('\\"', '"', $field);
        $value = str_replace('\\"', '"', $value);

        if ('.' != substr($field, 0, 1) && '#' != substr($field, 0, 1)) {
            $field = '#'.$field;
        }

        if ('Behat\Mink\Driver\Selenium2Driver' == get_class($driver)) {
            $this->scrollToElement($field);

            $javascript = <<<EOT
            var field = $('$field');
            var value = '$value';

            $(':focus').trigger('blur').trigger('change');
            var tag = field.prop('tagName');

            if (field.prop('type') === 'checkbox' ||
                field.prop('type') === 'radio')
            {

                field.prop('checked', true);

            } else if (tag === 'SELECT') {

                field.focus().val(value).trigger('change');

            } else {
                var pos = 0,
                    length = value.length,
                    character, charCode;

                for (;pos < length; pos += 1) {

                    character = value[pos];
                    charCode = character.charCodeAt(0);

                    var keyPressEvent = $.Event('keypress', {which: charCode}),
                        keyDownEvent = $.Event('keydown', {which: charCode}),
                        keyUpEvent = $.Event('keyup', {which: charCode});

                    field
                        .focus()
                        .trigger(keyDownEvent)
                        .trigger(keyPressEvent)
                        .val(value.substr(0,pos+1))
                        .trigger(keyUpEvent);
                }
            }

EOT;

            $this->getSession()->executeScript($javascript);
        } else {
            $elementsFound = $this->getSession()->getPage()->findAll('css', $field);

            if (empty($elementsFound)) {
                throw new \RuntimeException("Element $field not found");
            }

            $elementsFound[0]->setValue($value);
        }
    }

    // Can be used for cross browser tests to scroll so element is in viewport
    public function scrollToElement($element)
    {
        if ('.' != substr($element, 0, 1) && '#' != substr($element, 0, 1)) {
            $element = '#'.$element;
        }

        $driver = $this->getSession()->getDriver();
        if ('Behat\Mink\Driver\Selenium2Driver' == get_class($driver)) {
            $javascript =
                "var el = $('$element');"
                .'var elOffset = el.offset().top;'
                .'var elHeight = el.height();'
                .'var windowHeight = $(window).height();'
                .'var offset;'
                .'if (elHeight < windowHeight) {'
                .'  offset = elOffset - ((windowHeight / 2) - (elHeight / 2));'
                .'} else {'
                .'  offset = elOffset;'
                .'}'
                .'window.scrollTo(0, offset);';

            $this->getSession()->executeScript($javascript);
        }
    }
}
