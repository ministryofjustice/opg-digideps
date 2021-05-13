<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

trait AssertTrait
{
    public function assertStringContainsString(
        $expected,
        $found,
        string $comparisonSubject
    ) {
        $foundFormatted = strval(trim(strtolower($found)));
        $expectedFormatted = strval(trim(strtolower($expected)));
        assert(
            str_contains($foundFormatted, $expectedFormatted),
            $this->getAssertMessage($expectedFormatted, $foundFormatted, $comparisonSubject)
        );
    }

    public function assertStringEqualsString(
        $expected,
        $found,
        string $comparisonSubject
    ) {
        $foundFormatted = strval(trim(strtolower((string) $found)));
        $expectedFormatted = strval(trim(strtolower((string) $expected)));
        assert(
            $foundFormatted == $expectedFormatted,
            $this->getAssertMessage($expectedFormatted, $foundFormatted, $comparisonSubject)
        );
    }

    public function assertIntEqualsInt(
        $expected,
        $found,
        string $comparisonSubject
    ) {
        assert(
            $expected == $found,
            $this->getAssertMessage($expected, $found, $comparisonSubject)
        );
    }

    private function getAssertMessage(
        $expected,
        $found,
        string $comparisonSubject
    ) {
        $message = <<<MESSAGE

============================
Expecting: %s
Found: %s

Subject of Comparison: %s
Page URL: %s
============================

MESSAGE;

        return sprintf(
            $message,
            $expected,
            $found,
            $comparisonSubject,
            $this->getCurrentUrl()
        );
    }

    public function assertValueIsInSelect(string $expectedValue, string $selectNameAttributeValue)
    {
        $values = $this->getValuesFromSelect($selectNameAttributeValue);
        $roleSelectable = in_array($expectedValue, $values);

        assert(
            $roleSelectable,
            $this->getAssertMessage(
                $expectedValue,
                implode(',', $values),
                sprintf('Select element with name attribute value \'%s\'', $selectNameAttributeValue)
            )
        );
    }

    public function assertValueIsNotInSelect(string $expectedMissingValue, string $selectNameAttributeValue)
    {
        $values = $this->getValuesFromSelect($selectNameAttributeValue);
        $roleSelectable = !in_array($expectedMissingValue, $values);

        assert(
            $roleSelectable,
            $this->getAssertMessage(
                sprintf('\'%s\' not to appear', $expectedMissingValue),
                implode(',', $values),
                sprintf('Select element with name attribute value \'%s\'', $selectNameAttributeValue)
            )
        );
    }

    private function getValuesFromSelect(string $selectNameValue): array
    {
        $selectElement = $this->getSession()->getPage()->find(
            'xpath',
            "//select[@name='$selectNameValue']"
        );

        $options = $selectElement->findAll('xpath', '//option');

        $values = [];
        foreach ($options as $option) {
            $values[] = $option->getValue();
        }

        return $values;
    }

    public function assertIsClass(
        $expectedClassName,
        $actual,
        string $comparisonSubject
    ) {
        if (is_null($actual)) {
            assert(
                false,
                $this->getAssertMessage($expectedClassName, 'null', $comparisonSubject)
            );
        }

        $actualClass = get_class($actual);
        $isExpectedClass = $actualClass === $expectedClassName;

        assert(
            $isExpectedClass,
            $this->getAssertMessage($expectedClassName, $actualClass, $comparisonSubject)
        );
    }

    public function assertIsNull(
        $actual,
        string $comparisonSubject
    ) {
        assert(
            is_null($actual),
            $this->getAssertMessage('null', $actual, $comparisonSubject)
        );
    }

    public function assertLinkWithTextIsOnPage(string $linkText)
    {
        $linkElement = $this->getSession()->getPage()->find(
            'xpath',
            "//a[text() = '$linkText']"
        );

        $this->assertStringContainsString(
            $linkText,
            $linkElement->getHtml(),
            sprintf('Anchor element with text value \'%s\'', $linkText)
        );
    }
}
