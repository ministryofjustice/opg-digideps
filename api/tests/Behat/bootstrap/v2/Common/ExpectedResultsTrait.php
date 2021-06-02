<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use App\Tests\Behat\BehatException;
use Behat\Mink\Element\NodeElement;

trait ExpectedResultsTrait
{
    private string $tableHtml = '';
    private array $summarySectionItemsFound = [];

    public function expectedResultsDisplayedSimplified(string $sectionName, bool $partialMatch = false, bool $sectionsHaveTotals = false)
    {
        // Add assertion for checking on section totals and overall total
        $this->tableHtml = '';
        $this->summarySectionItemsFound = [];

        $xpath = '//dl|//tbody';
        $summarySectionElements = $this->getSession()->getPage()->findAll('xpath', $xpath);

        foreach ($summarySectionElements as $summarySectionElement) {
            $this->tableHtml .= $summarySectionElement->getHtml();

            $this->extractDescriptionListContents($summarySectionElement);
            $this->extractTableBodyContents($summarySectionElement);
        }

        $this->extractMonetaryTotals();
        $this->removeEmptyElements();
        var_dump($this->summarySectionItemsFound);
        var_dump($this->submittedAnswersByFormSections['totals']);

        $this->assertSectionContainsExpectedResultsSimplified($sectionName, $partialMatch);

        if ($sectionsHaveTotals) {
            $this->assertSectionTotal($sectionName);
        }

        $this->assertGrandTotal();
    }

    private function assertGrandTotal()
    {
        if (!is_null($grandTotal = $this->getGrandTotal())) {
            $normalizedTotal = $this->normalizeIntToCurrencyString($grandTotal);
            $sectionAnswerFound = in_array($normalizedTotal, $this->summarySectionItemsFound);

            if (!$sectionAnswerFound) {
                $failureMessage = sprintf('Grand total value of %s was not found on the page', $normalizedTotal);
                throw new BehatException($failureMessage);
            }
        }
    }

    private function assertSectionTotal(string $sectionName)
    {
        if (!is_null($sectionTotal = $this->getSectionTotal($sectionName))) {
            $normalizedTotal = $this->normalizeIntToCurrencyString($sectionTotal);
            $sectionAnswerFound = in_array($normalizedTotal, $this->summarySectionItemsFound);

            if (!$sectionAnswerFound) {
                $failureMessage = sprintf(
                    'Section "%s" total value of %s was not found on the page',
                    $sectionName,
                    $normalizedTotal
                );

                throw new BehatException($failureMessage);
            }

            // Remove the found total so sections with multiple questions/totals won't get a false positive
            $key = array_search($normalizedTotal, $this->summarySectionItemsFound);
            unset($this->summarySectionItemsFound[$key]);
        }
    }

    private function removeEmptyElements()
    {
        $this->summarySectionItemsFound = array_filter(
            $this->summarySectionItemsFound,
            fn ($value) => !is_null($value) && '' !== $value
        );
    }

    private function extractDescriptionListContents(NodeElement $element)
    {
        if ('dl' == $element->getTagName()) {
            $xpath = '//dd';
            $descriptionDetailsElements = $element->findAll('xpath', $xpath);

            foreach ($descriptionDetailsElements as $dd) {
                $this->summarySectionItemsFound[] = strtolower($dd->getText());
            }

            $xpath = '//dt';
            $descriptionTermElements = $element->findAll('xpath', $xpath);

            foreach ($descriptionTermElements as $dd) {
                $this->summarySectionItemsFound[] = strtolower($dd->getText());
            }
        }
    }

    private function extractTableBodyContents(NodeElement $element)
    {
        if ('tbody' == $element->getTagName()) {
            $xpath = '//th';
            $tableHeadElements = $element->findAll('xpath', $xpath);

            foreach ($tableHeadElements as $th) {
                $this->summarySectionItemsFound[] = strtolower($th->getText());
            }

            $xpath = '//td';
            $tableDataElements = $element->findAll('xpath', $xpath);

            foreach ($tableDataElements as $td) {
                $this->summarySectionItemsFound[] = strtolower($td->getText());
            }

            $xpath = '//p';
            $paragraphElements = $element->findAll('xpath', $xpath);

            foreach ($paragraphElements as $td) {
                $this->summarySectionItemsFound[] = strtolower($td->getText());
            }
        }
    }

    private function extractMonetaryTotals()
    {
        $totalElements = [];

        $divXpath = '//div[text()[contains(.,"Total")] and text()[contains(.,"£")]]';
        $totalElements = array_merge($totalElements, $this->getSession()->getPage()->findAll('xpath', $divXpath));

        $tableRowXpath = '//tr/th[text()[contains(.,"Total")]]/parent::*';
        $totalElements = array_merge($totalElements, $this->getSession()->getPage()->findAll('xpath', $tableRowXpath));

        if (!empty($totalElements)) {
            foreach ($totalElements as $element) {
                $text = strtolower($element->getText());

                preg_match('/£([0-9]+[\.,0-9]*)/', $text, $match);
                $totalValue = $match[0];

                $this->summarySectionItemsFound[] = $totalValue;
            }
        }
    }

    private function assertSectionContainsExpectedResultsSimplified(string $sectionName, bool $partialMatch = false)
    {
        if (empty($this->getSectionAnswers($sectionName))) {
            throw new BehatException(sprintf('The section specified (%s) is not in $this->submittedAnswersByFormSections', $sectionName));
        }

        $foundAnswers = [];
        $missingAnswers = [];

        foreach ($this->getSectionAnswers($sectionName) as $index => $sectionAnswers) {
            foreach ($sectionAnswers as $fieldName => $fieldValue) {
                $fieldValue = $this->normalizeValue($fieldValue);

                if ($partialMatch) {
                    $matches = array_filter($this->summarySectionItemsFound, function ($item) use ($fieldValue) {
                        return false !== strpos($item, $fieldValue);
                    });

                    $sectionAnswerFound = !empty($matches);
                } else {
                    $sectionAnswerFound = in_array($fieldValue, $this->summarySectionItemsFound);
                }

                if ($sectionAnswerFound) {
                    $foundAnswers[$fieldName] = $fieldValue;

                    $key = array_search($fieldValue, $this->summarySectionItemsFound);

                    if ($key) {
                        // Remove the found answer so sections with multiple questions won't get a false positive
                        unset($this->summarySectionItemsFound[$key]);
                    }
                } else {
                    $missingAnswers[$fieldName] = $fieldValue;
                }
            }
        }

        if (!empty($missingAnswers)) {
            $this->throwMissingAnswersException($missingAnswers, $foundAnswers);
        }
    }

    private function throwMissingAnswersException(array $missingAnswers, array $foundAnswers)
    {
        $foundText = !empty($foundAnswers) ? json_encode($foundAnswers) : 'No form values found';
        $missingText = json_encode($missingAnswers);

        $failureMessage = <<<MSG
The following form answers were found on the page:
$foundText

But these were missing:
$missingText

(shown with the field name the value was entered in)

Table HTML:
$this->tableHtml
MSG;

        throw new BehatException($failureMessage);
    }

    private function normalizeValue($value)
    {
        if (is_numeric($value)) {
            $value = $this->normalizeIntToCurrencyString($value);
        } else {
            $value = strtolower(strval($value));
        }

        return $value;
    }

    private function normalizeIntToCurrencyString($fieldValue)
    {
        if (is_int($fieldValue)) {
            return sprintf('£%s.00', number_format($fieldValue));
        }

        if (is_float($fieldValue)) {
            return sprintf('£%s', number_format($fieldValue));
        }

        return $fieldValue;
    }

    /*
     * Adds the contents of each summary section (identified by dl or tbody) to an array
     * then compares the results of the specified section contents to an array of expected contents.
     * $summarySectionNumber - which occurrence of tbody or dl to search in in the order they appear on summary page.
     * $expectedResults - must be an array of arrays of strings. The outer array specifies the 'row' and the
     * inner array specifies the 'fields'. The 'rows' and 'fields' in this case are dependent on what type of
     * elements you are searching through and are found automatically by the logic in the function.
     * $context - a description of what the section is or does.
     * $debug - set to true to output all the sections to screen for development purposes.
     * Very useful when creating the tests! It will debug on the expected results you are checking.
     */
    public function expectedResultsDisplayed(int $summarySectionNumber, array $expectedResults, string $context, bool $debug = false)
    {
        $this->checkExpectedResultsCorrectFormat($expectedResults);

        $xpath = '//dl|//tbody';
        $summarySectionElements = $this->getSession()->getPage()->findAll('xpath', $xpath);

        $sections = [];
        foreach ($summarySectionElements as $summarySectionElement) {
            $this->summarySectionItemsFound = [];
            if ('dl' == $summarySectionElement->getTagName()) {
                $this->addSummarySectionItemsFoundFromDescriptionList($summarySectionElement);
            } elseif ('tbody' == $summarySectionElement->getTagName()) {
                $this->addSummarySectionItemsFoundFromTableBody($summarySectionElement);
            } else {
                $this->throwContextualException('Element must be either dl or tbody');
            }
            $sections[] = $this->summarySectionItemsFound;
        }

        if ($debug) {
            $this->debugExpectedResultsDisplayed($sections, $summarySectionNumber, $expectedResults);
        }

        foreach ($sections[$summarySectionNumber] as $foundResultKey => $foundResults) {
            $this->checkContainsExpectedResults($expectedResults[$foundResultKey], $foundResults, $summarySectionNumber, $context);
        }
    }

    private function addSummarySectionItemsFoundFromDescriptionList($descriptionList)
    {
        $xpath = "//div[contains(@class, 'govuk-summary-list__row')]";
        $listSummaryRowItems = $descriptionList->findAll('xpath', $xpath);

        if (count($listSummaryRowItems) > 0) {
            foreach ($listSummaryRowItems as $listSummaryRowItemKey => $listSummaryRowItem) {
                // first row always seems to be title row so ignore it.
                if ($listSummaryRowItemKey > 0) {
                    $this->addSummarySectionItemsFoundFromDescription($listSummaryRowItem);
                }
            }
        } else {
            $this->addSummarySectionItemsFoundFromDescription($descriptionList);
        }
    }

    private function addSummarySectionItemsFoundFromDescription($descriptionList)
    {
        $xpath = '//li';
        $listItems = $descriptionList->findAll('xpath', $xpath);

        if (count($listItems) > 0) {
            $this->addSummarySectionItemsFound($listItems);
        } else {
            $xpath = '//dt|//dd';
            $descriptionDataItems = $descriptionList->findAll('xpath', $xpath);
            $this->addSummarySectionItemsFound($descriptionDataItems);
        }
    }

    private function addSummarySectionItemsFoundFromTableBody($table)
    {
        $xpath = '//tr';
        $tableRowItems = $table->findAll('xpath', $xpath);

        if (count($tableRowItems) > 0) {
            foreach ($tableRowItems as $tableRowItem) {
                $xpath = '//td|//th';
                $tableDataItems = $tableRowItem->findAll('xpath', $xpath);
                $this->addSummarySectionItemsFound($tableDataItems);
            }
        } else {
            $xpath = '//td|//th';
            $tableDataItems = $table->findAll('xpath', $xpath);
            $this->addSummarySectionItemsFound($tableDataItems);
        }
    }

    private function addSummarySectionItemsFound($items)
    {
        $tableValues = [];
        foreach ($items as $item) {
            $tableValues[] = trim(strval($item->getText()));
        }
        $this->summarySectionItemsFound[] = $tableValues;
    }

    private function checkContainsExpectedResults($expectedItems, $foundItems, $sectionNumber, $context)
    {
        $foundInElemPrevious = 0;
        $raiseException = false;
        foreach ($expectedItems as $expectedItem) {
            $found = false;
            foreach (array_slice($foundItems, $foundInElemPrevious) as $foundItemKey => $foundItem) {
                if (str_contains(strval(trim(strtolower($foundItem))), strval(trim(strtolower($expectedItem))))) {
                    $found = true;
                    $foundInElem = $foundItemKey + $foundInElemPrevious;
                    break;
                }
            }
            if ($found and $foundInElem >= $foundInElemPrevious) {
                $foundInElemPrevious = $foundInElem;
            } else {
                $raiseException = true;
            }
        }

        if ($raiseException) {
            $this->throwContextualException(
                $this->raiseExpectedResultsException(
                    $expectedItems,
                    $foundItems,
                    $sectionNumber,
                    $context
                    )
            );
        }
    }

    private function raiseExpectedResultsException($expectedItems, $foundItems, $sectionNumber, $context)
    {
        $expected = implode(PHP_EOL, $expectedItems);
        $found = implode(PHP_EOL, $foundItems);

        $message = <<<MESSAGE

============================
-- VALIDATION ERROR --
Please check that the values you are expecting are:
    - In the found section below (not case sensitive)
    - In the same order
Note: There may be extra values in found section. This is fine as long as order is the same.

-- Expecting:

%s

-- Found:

%s

Section number: %s
Context of test: %s
Page URL: %s
============================

MESSAGE;

        return sprintf(
            $message,
            $expected,
            $found,
            strval($sectionNumber),
            $context,
            $this->getCurrentUrl()
        );
    }

    private function debugExpectedResultsDisplayed($sections, $summarySectionNumber, $expectedResults)
    {
        $summarySectionsText = '';
        foreach ($sections as $sectionKey => $section) {
            $summarySectionsText = $summarySectionsText."\n\nSection Number: ".strval($sectionKey)."\n";
            foreach ($section as $rowNumber => $row) {
                $summarySectionsText = $summarySectionsText."\tRow Number: ".strval($rowNumber)."\n";
                foreach ($row as $fieldNumber => $field) {
                    $summarySectionsText = $summarySectionsText."\t\t".strtolower(strval($field))."\n";
                }
            }
        }

        $expectedText = "\n\nThe input to this function is specifically looking at section: ".strval($summarySectionNumber)."\n";
        $expectedText = $expectedText."\n\nSection Number: ".strval($summarySectionNumber)."\n";
        foreach ($expectedResults as $rowNumber => $row) {
            $expectedText = $expectedText."\tRow Number: ".strval($rowNumber)."\n";
            foreach ($row as $fieldNumber => $field) {
                $expectedText = $expectedText."\t\t".strtolower(strval($field))."\n";
            }
        }

        $message = <<<MESSAGE

============================
-- DEBUG REPORT --

How to use: Firstly, check that the elements under 'found' correspond with what you see on
the summary screen.

If there are missing items or sections on the screen that don't appear in the found section
then you may have found an edge case that won't work with this function.
Either amend the function or use some bespoke code.

Secondly, check that the items you are expecting appear in the equivalent 'Section Number'
and 'Row Number' and that the 'fields' appear in the right order.

It is completely fine to have more rows in the 'found' section. As long as they are in the
correct section, row and field order.

-- FOUND --
%s

-- EXPECTED --
%s

============================

MESSAGE;

        $this->throwContextualException(sprintf(
            $message,
            $summarySectionsText,
            $expectedText
        ));
    }

    private function checkExpectedResultsCorrectFormat($expectedResults)
    {
        foreach ($expectedResults as $expectedResult) {
            if (!is_array($expectedResult)) {
                $this->throwContextualException(
                    "\nIncorrect data types - \nexpectedResults must be an array of array of strings ([][]string) to represent rows and fields on summary page"
                );
            } else {
                foreach ($expectedResult as $expectedField) {
                    if (!is_string($expectedField)) {
                        $this->throwContextualException(
                            "\nIncorrect data type - \n$expectedField must be a string"
                        );
                    }
                }
            }
        }
    }
}
