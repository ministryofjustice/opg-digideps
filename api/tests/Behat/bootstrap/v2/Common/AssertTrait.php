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

    public function assertValueAppearsInSelect(string $expectedValue, string $selectNameAttributeValue)
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
}
