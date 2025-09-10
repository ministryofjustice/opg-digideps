<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use App\Tests\Behat\BehatException;
use Behat\Mink\Element\NodeElement;

trait ExpectedResultsTrait
{
    private string $tableHtml = '';
    private array $summarySectionItemsFound = [];
    private array $foundAnswers = [];
    private array $missingAnswers = [];

    /**
     * @param string|null $sectionName        The name given to the portion of the form being tested as defined in
     *                                        FormFillingTrait. Set to null to check all sections completed using
     *                                        keys of $summarySectionItemsFound
     * @param bool        $partialMatch       If assertions should match on a full or partial string (defaults to false)
     * @param bool        $sectionsHaveTotals If assertions should match on a section subtotal (defaults to false)
     * @param bool        $hasGrandTotal      If assertions should match on a grand total (defaults to true)
     * @param bool        $debug              Set to true to output a list of user inputs and data extracted from
     *                                        the summary page
     *
     * @throws BehatException
     */
    public function expectedResultsDisplayedSimplified(
        ?string $sectionName = null,
        bool $partialMatch = false,
        bool $sectionsHaveTotals = false,
        bool $hasGrandTotal = true,
        bool $debug = false
    ) {
        $this->tableHtml = '';
        $this->summarySectionItemsFound = [];

        $xpath = '//dl|//tbody';
        $summarySectionElements = $this->getSession()->getPage()->findAll('xpath', $xpath);

        foreach ($summarySectionElements as $summarySectionElement) {
            $this->tableHtml .= $summarySectionElement->getHtml();

            $this->extractDescriptionListContents($summarySectionElement);
            $this->extractTableBodyContents($summarySectionElement);
        }

        $this->extractH3Contents();
        $this->extractMonetaryTotals();
        $this->removeEmptyElements();

        if ($debug) {
            $this->throwDebugException(is_null($sectionName) ? 'Section not set' : $sectionName);
        }

        // Assert on all sections completed by user
        if (is_null($sectionName)) {
            $completedSections = array_keys($this->submittedAnswersByFormSections);
            $key = array_search('totals', $completedSections);

            if ($key) {
                unset($completedSections[$key]);
            }

            foreach ($completedSections as $section) {
                $this->assertSectionContainsExpectedResultsSimplified($section, $partialMatch);
            }

            // Assert on specific section
        } else {
            $this->assertSectionContainsExpectedResultsSimplified($sectionName, $partialMatch);
        }

        if ($sectionsHaveTotals && $sectionName) {
            $this->assertSectionTotal($sectionName);
        }

        if ($hasGrandTotal) {
            $this->assertGrandTotal();
        }
    }

    private function extractH3Contents()
    {
        $h3s = $this->getSession()->getPage()->findAll('xpath', '//h3');

        foreach ($h3s as $h3) {
            $this->summarySectionItemsFound[] = strtolower($h3->getText());
        }
    }

    /**
     * Asserts on there being a grand total visible on the page that matches
     * submittedAnswersByFormSections['totals']['grandTotal'].
     *
     * @throws BehatException
     */
    private function assertGrandTotal()
    {
        if (!empty($grandTotal = $this->getGrandTotal())) {
            $normalizedTotal = $this->normalizeIntToCurrencyString($grandTotal);
            $sectionAnswerFound = in_array($normalizedTotal, $this->summarySectionItemsFound);

            if (!$sectionAnswerFound) {
                $failureMessage = sprintf('Grand total value of %s was not found on the page', $normalizedTotal);
                throw new BehatException($failureMessage);
            }
        }
    }

    /**
     * Asserts on there being a section total visible on the page that matches
     * submittedAnswersByFormSections['totals'][<$sectionName>].
     *
     * @param string $sectionName The name given to the portion of the form being tested as defined in FormFillingTrait
     *
     * @throws BehatException
     */
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

    /**
     * Removes blank elements from the summary section items array to help with debugging and skip empty loops.
     */
    private function removeEmptyElements()
    {
        $this->summarySectionItemsFound = array_filter(
            $this->summarySectionItemsFound,
            fn ($value) => !is_null($value) && '' !== $value
        );
    }

    /**
     * Extracts text from elements contained under a description list (dl) element on the page.
     */
    private function extractDescriptionListContents(NodeElement $element)
    {
        if ('dl' == $element->getTagName()) {
            $xpath = '//dd';
            $descriptionDetailsElements = $element->findAll('xpath', $xpath);

            foreach ($descriptionDetailsElements as $dd) {
                if ($listItemElements = $dd->findAll('xpath', '//li')) {
                    foreach ($listItemElements as $li) {
                        $this->summarySectionItemsFound[] = strtolower($li->getText());
                    }
                } elseif ($paragraphElements = $dd->findAll('xpath', '//p')) {
                    foreach ($paragraphElements as $p) {
                        $this->summarySectionItemsFound[] = strtolower($p->getText());
                    }
                } else {
                    $this->summarySectionItemsFound[] = strtolower($dd->getText());
                }
            }

            $xpath = '//dt';
            $descriptionTermElements = $element->findAll('xpath', $xpath);

            foreach ($descriptionTermElements as $dt) {
                if ($listItemElements = $dt->findAll('xpath', '//li')) {
                    foreach ($listItemElements as $li) {
                        $this->summarySectionItemsFound[] = strtolower($li->getText());
                    }
                } elseif ($paragraphElements = $dt->findAll('xpath', '//p')) {
                    foreach ($paragraphElements as $p) {
                        $this->summarySectionItemsFound[] = strtolower($p->getText());
                    }
                } else {
                    $this->summarySectionItemsFound[] = strtolower($dt->getText());
                }
            }
        }
    }

    /**
     * Extracts text from elements contained under a table body (tbody) element on the page.
     */
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

    /**
     * Extracts monetary values from div or tr > th elements that contain the strings 'Total' and '£' (for divs)
     * and 'Total' (for tr > th). This covers section totals and grand totals on summary pages.
     */
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

    /**
     * @param string $sectionName  The name given to the portion of the form being tested as defined in FormFillingTrait
     * @param bool   $partialMatch If assertions should match on a full or partial string (defaults to false)
     *
     * @throws BehatException
     */
    private function assertSectionContainsExpectedResultsSimplified(string $sectionName, bool $partialMatch = false)
    {
        $sectionExists = array_key_exists($sectionName, $this->submittedAnswersByFormSections);

        if (!$sectionExists) {
            throw new BehatException(sprintf('The section specified (%s) is not in $this->submittedAnswersByFormSections', $sectionName));
        }

        if (empty($this->getSectionAnswers($sectionName))) {
            return;
        }

        // Loop over the collection of values inputted to forms via FormFillingTrait functions for a specific section
        foreach ($this->getSectionAnswers($sectionName) as $sectionAnswers) {
            // We assert on totals separately - skip asserting
            if ('totals' === $sectionName) {
                continue;
            }

            // Loop over each field value to assert against summary page values
            foreach ($sectionAnswers as $fieldName => $fieldValue) {
                if (is_string($fieldValue) || is_int($fieldValue) || is_float($fieldValue)) {
                    $fieldValue = $this->normalizeValue($fieldValue);
                }

                if (is_array($fieldValue)) {
                    foreach ($fieldValue as $value) {
                        $value = $this->normalizeValue($value);
                        $this->matchOnValue($partialMatch, $value, $fieldName);
                    }
                } else {
                    $this->matchOnValue($partialMatch, $fieldValue, $fieldName);
                }
            }
        }

        if (!empty($this->missingAnswers)) {
            $this->throwMissingAnswersException();
        }
    }

    private function matchOnValue(bool $partialMatch, $fieldValue, $fieldName)
    {
        if ($partialMatch) {
            $matches = array_filter($this->summarySectionItemsFound, function ($item) use ($fieldValue) {
                return empty($fieldValue) || false !== strpos($item, $fieldValue);
            });

            $sectionAnswerFound = !empty($matches);
        } else {
            $sectionAnswerFound = in_array($fieldValue, $this->summarySectionItemsFound);
        }

        if ($sectionAnswerFound) {
            $this->foundAnswers[$fieldName][] = $fieldValue;

            $key = array_search($fieldValue, $this->summarySectionItemsFound);

            if ($key) {
                // Remove the found answer so sections with multiple questions won't get a false positive
                unset($this->summarySectionItemsFound[$key]);
            }
        } else {
            $this->missingAnswers[$fieldName][] = $fieldValue;
        }
    }

    private function throwMissingAnswersException()
    {
        [$foundText, $missingText] = $this->formatFoundAndMissingAnswers();

        $failureMessage = <<<MSG
The following form answers were found on the page:

$foundText

But these were missing:

$missingText

(shown with the field name the value was entered in)

Summary page table HTML:

$this->tableHtml
MSG;

        throw new BehatException($failureMessage);
    }

    private function formatFoundAndMissingAnswers()
    {
        $foundText = !empty($this->foundAnswers) ? json_encode($this->foundAnswers, JSON_PRETTY_PRINT) : 'No form values found';
        $missingText = json_encode($this->missingAnswers, JSON_PRETTY_PRINT);

        $foundText = str_replace('\u00a3', '£', $foundText);
        $missingText = str_replace('\u00a3', '£', $missingText);

        return [$foundText, $missingText];
    }

    private function throwDebugException(string $sectionName)
    {
        $userInput = json_encode($this->submittedAnswersByFormSections, JSON_PRETTY_PRINT);
        $summaryExtract = json_encode($this->summarySectionItemsFound, JSON_PRETTY_PRINT);

        $userInput = str_replace('\u00a3', '£', $userInput);
        $summaryExtract = str_replace('\u00a3', '£', $summaryExtract);

        $debugMessage = <<<MSG
====================== DEBUG ======================

Tracked user input for section '$sectionName' was:

$userInput

Data extracted from the summary page was:

$summaryExtract

Summary page table HTML:

$this->tableHtml
MSG;

        throw new BehatException($debugMessage);
    }

    /**
     * When entering form values in FormFillingTrait there is no formatting applied (e.g. 12345 rather than £12,345.00).
     * This adds the apps standard currency formatting of £, commas and decimal points.
     *
     * @return mixed|string
     */
    private function normalizeValue($value)
    {
        if (is_numeric($value)) {
            $value = $this->normalizeIntToCurrencyString($value);
        } else {
            $value = strtolower(strval($value));
        }

        return $value;
    }

    /**
     * Adds the apps standard currency formatting of £, commas and decimal points to ints. Returns $fieldValue
     * unchanged if its not an int or float.
     *
     * @return mixed|string
     */
    public function normalizeIntToCurrencyString($fieldValue)
    {
        if (is_int($fieldValue)) {
            return sprintf('£%s.00', number_format($fieldValue));
        }

        if (is_float($fieldValue)) {
            return sprintf('£%s', number_format($fieldValue, 2));
        }

        return $fieldValue;
    }
}
