<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

trait AssertTrait
{
    public function assertStringContainsString(
        string $needle,
        string $haystack,
        string $reasonForFailedAssert,
    ) {
        $haystack = trim(strtolower($haystack));
        $needle = trim(strtolower($needle));
        assert(
            str_contains($haystack, $needle),
            $this->getAssertMessage($needle, $haystack, $reasonForFailedAssert)
        );
    }

    public function assertStringEqualsString(
        $expected,
        $found,
        string $comparisonSubject,
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
        string $comparisonSubject,
    ) {
        assert(
            $expected == $found,
            $this->getAssertMessage($expected, $found, $comparisonSubject)
        );
    }

    public function assertBoolIsTrue(
        $expected,
        string $comparisonSubject,
    ) {
        assert(
            true === $expected,
            $this->getAssertMessage($expected, 'false', $comparisonSubject)
        );
    }

    public function assertStringDoesNotContainString(
        $notExpected,
        $found,
        string $comparisonSubject,
    ) {
        $foundFormatted = strval(trim(strtolower($found)));
        $notExpectedFormatted = strval(trim(strtolower($notExpected)));
        assert(
            !str_contains($foundFormatted, $notExpectedFormatted),
            $this->getAssertMessage('\''.$notExpectedFormatted.'\' should not exist in searched element!', $foundFormatted, $comparisonSubject)
        );
    }

    public function assertStringDoesNotEqualString(
        $notExpected,
        $found,
        string $comparisonSubject,
    ) {
        $foundFormatted = strval(trim(strtolower($found)));
        $notExpectedFormatted = strval(trim(strtolower($notExpected)));
        assert(
            $foundFormatted != $notExpectedFormatted,
            $this->getAssertMessage('\''.$notExpectedFormatted.'\' should not exist in searched element!', $foundFormatted, $comparisonSubject)
        );
    }

    private function getAssertMessage(
        string $expected,
        string $found,
        string $comparisonSubject,
    ) {
        $message = <<<MESSAGE

============================
Expecting: %s
Found: %s

Subject of Comparison: %s
Page URL: %s
Logged in User: %s
============================

MESSAGE;

        $loggedInUser =
            is_null($this->loggedInUserDetails) ? 'User not logged in' : $this->loggedInUserDetails->getUserEmail();

        return sprintf(
            $message,
            $expected,
            $found,
            $comparisonSubject,
            $this->getCurrentUrl(),
            $loggedInUser
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
        string $comparisonSubject,
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
        string $comparisonSubject,
    ) {
        assert(
            is_null($actual),
            $this->getAssertMessage('null', gettype($actual), $comparisonSubject)
        );
    }

    public function assertLinkWithTextIsOnPage(string $linkText)
    {
        $linkElement = $this->getSession()->getPage()->find(
            'xpath',
            "//a[normalize-space() = '$linkText']"
        );

        if (is_null($linkElement)) {
            $expected = sprintf('Anchor element with text value \'%s\'', $linkText);

            $message = $this->getAssertMessage(
                $expected,
                'Could not find specified anchor element',
                $this->getSession()->getPage()->getHtml()
            );

            assert(false, $message);
        }
    }

    public function assertLinkWithTextIsNotOnPage(string $linkText)
    {
        $linkElement = $this->getSession()->getPage()->find(
            'xpath',
            "//a[text() = '$linkText']"
        );

        if (!is_null($linkElement)) {
            $expected = sprintf('Not to find anchor element with text value \'%s\'', $linkText);

            $message = $this->getAssertMessage(
                $expected,
                'The element appeared on the page',
                $this->getSession()->getPage()->getHtml()
            );

            assert(false, $message);
        }
    }

    public function assertEntitiesAreTheSame(
        $expectedEntity,
        $actualEntity,
        string $comparisonSubject,
    ) {
        if (is_null($actualEntity)) {
            assert(
                false,
                $this->getAssertMessage(
                    sprintf('id: %s', $expectedEntity->getId()),
                    'null',
                    $comparisonSubject
                )
            );
        }

        $objectsAreTheSame = $expectedEntity->getId() === $actualEntity->getId();

        assert(
            $objectsAreTheSame,
            $this->getAssertMessage(
                sprintf('Expected %s id: %s', get_class($expectedEntity), $expectedEntity->getId()),
                sprintf('Actual %s id: %s', get_class($actualEntity), $actualEntity->getId()),
                $comparisonSubject
            )
        );
    }

    public function assertEntitiesAreNotTheSame(
        $expectedEntity,
        $actualEntity,
        string $comparisonSubject,
    ) {
        if (is_null($actualEntity)) {
            assert(
                false,
                $this->getAssertMessage(
                    sprintf('id: %s', $expectedEntity->getId()),
                    'null',
                    $comparisonSubject
                )
            );
        }

        $objectsAreNotTheSame = $expectedEntity->getId() !== $actualEntity->getId();

        assert(
            $objectsAreNotTheSame,
            $this->getAssertMessage(
                sprintf('Expected %s id: %s', get_class($expectedEntity), $expectedEntity->getId()),
                sprintf('Actual %s id: %s', get_class($actualEntity), $actualEntity->getId()),
                $comparisonSubject
            )
        );
    }
}
