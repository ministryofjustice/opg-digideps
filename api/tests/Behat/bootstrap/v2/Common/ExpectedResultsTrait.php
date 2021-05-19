<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

trait ExpectedResultsTrait
{
    private array $summarySectionItemsFound = [];

    /*
     * Adds the contents of each summary section (identified by dl or tbody) to an array
     * then compares the results of the specified section contents to an array of expected contents.
     *
     * $summarySectionName - which occurrence of tbody or dl to search in in the order they appear on summary page.
     *
     * $context - a description of what the section is or does.
     *
     * $debug - set to true to output all the sections to screen for development purposes.
     * Very useful when creating the tests! It will debug on the expected results you are checking.
     */
    public function expectedResultsDisplayed(string $summarySectionName, string $context, bool $debug = false)
    {
//        $this->formSectionsAndAnswers = array_reverse($this->formSectionsAndAnswers);
//        var_dump(array_reverse($this->formSectionsAndAnswers));
//        var_dump($this->formSectionsAndAnswers);

        $expectedResults = $this->formSectionsAndAnswers;
        $this->checkExpectedResultsCorrectFormat($summarySectionName);

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
            $sections[$summarySectionName] = $this->summarySectionItemsFound;
        }

        if ($debug) {
            $this->debugExpectedResultsDisplayed($sections, $summarySectionName, $expectedResults);
        }

        foreach ($sections[$summarySectionName] as $foundResultKey => $foundResults) {
            var_dump($summarySectionName.':');
            var_dump($sections);

            var_dump($foundResultKey.':');
            var_dump($expectedResults[$summarySectionName][$foundResultKey]);

            $this->checkContainsExpectedResults($expectedResults[$summarySectionName][$foundResultKey], $foundResults, $summarySectionName, $context);
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

    private function checkContainsExpectedResults($expectedItems, $foundItems, $sectionName, $context)
    {
        $foundInElemPrevious = 0;
        $raiseException = false;

        var_dump('Expected:');
        var_dump($expectedItems);

        var_dump('Found:');
        var_dump($foundItems);
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
                    $sectionName,
                    $context
                    )
            );
        }
    }

    private function raiseExpectedResultsException($expectedItems, $foundItems, $sectionName, $context)
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

Section name: %s
Context of test: %s
Page URL: %s
============================

MESSAGE;

        return sprintf(
            $message,
            $expected,
            $found,
            strval($sectionName),
            $context,
            $this->getCurrentUrl()
        );
    }

    private function debugExpectedResultsDisplayed($sections, $summarySectionName, $expectedResults)
    {
        $summarySectionsText = '';
        foreach ($sections as $sectionKey => $section) {
            $summarySectionsText = $summarySectionsText."\n\nSection Name: ".strval($sectionKey)."\n";
            foreach ($section as $rowNumber => $row) {
                $summarySectionsText = $summarySectionsText."\tRow Number: ".strval($rowNumber)."\n";
                foreach ($row as $fieldNumber => $field) {
                    $summarySectionsText = $summarySectionsText."\t\t".strtolower(strval($field))."\n";
                }
            }
        }

        $expectedText = "\n\nThe input to this function is specifically looking at section: ".strval($summarySectionName)."\n";
        $expectedText = $expectedText."\n\nSection Name: ".strval($summarySectionName)."\n";
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

    private function checkExpectedResultsCorrectFormat(string $summarySectionName)
    {
        if (empty($this->formSectionsAndAnswers[$summarySectionName])) {
            $this->throwContextualException(
                "\nForm values not set - \n\$formSectionsAndAnswers[$summarySectionName] is empty. Make sure the form is filled in using functions in FormTrait"
            );
        }

        foreach ($this->formSectionsAndAnswers[$summarySectionName] as $expectedResult) {
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
