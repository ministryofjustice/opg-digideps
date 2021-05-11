<?php

declare(strict_types=1);

namespace DigidepsBehat\v2\Common;

trait ExpectedResultsTrait
{
    private array $comparisonItems = [];

    public function expectedResultsDisplayed($sectionNumber, $expectedResultArrays, $context)
    {
        $xpath = '//dl|//tbody';
        $topLevelTables = $this->getSession()->getPage()->findAll('xpath', $xpath);

        $sections = [];
        foreach ($topLevelTables as $tableItem) {
            $this->comparisonItems = [];
            if ('dl' == $tableItem->getTagName()) {
                $xpath = "//div[contains(@class, 'govuk-summary-list__row')]";
                $listSummaryRowItems = $tableItem->findAll('xpath', $xpath);

                if (count($listSummaryRowItems) > 0) {
                    $xpath = '//li';
                    $listItems = $tableItem->findAll('xpath', $xpath);

                    if (count($listItems) > 0) {
                        $this->getTextItems($listItems);
                    } else {
                        $xpath = '//dt|//dd';
                        $descriptionDataItems = $tableItem->findAll('xpath', $xpath);
                        $this->getTextItems($descriptionDataItems);
                    }
                } else {
                    $xpath = '//li';
                    $listItems = $tableItem->findAll('xpath', $xpath);

                    if (count($listItems) > 0) {
                        $this->getTextItems($listItems);
                    } else {
                        $xpath = '//dt|//dd';
                        $descriptionDataItems = $tableItem->findAll('xpath', $xpath);
                        $this->getTextItems($descriptionDataItems);
                    }
                }
            } elseif ('tbody' == $tableItem->getTagName()) {
                $xpath = '//tr';
                $tableRowItems = $tableItem->findAll('xpath', $xpath);

                if (count($tableRowItems) > 0) {
                    foreach ($tableRowItems as $tableRowItem) {
                        $xpath = '//td|//th';
                        $tableDataItems = $tableRowItem->findAll('xpath', $xpath);
                        $this->getTextItems($tableDataItems);
                    }
                } else {
                    $xpath = '//td|//th';
                    $tableDataItems = $tableItem->findAll('xpath', $xpath);
                    $this->getTextItems($tableDataItems);
                }
            } else {
                $this->throwContextualException('Unrecognised option');
            }
            $sections[] = $this->comparisonItems;
        }

//        ** Uncomment below for debugging purposes... useful when creating your tests**
//        foreach ($sections as $sectionKey=>$section) {
//            var_dump("Section key: ".strval($sectionKey));
//            var_dump($section);
//        }
//        var_dump($expectedResultArrays);

        foreach ($sections[$sectionNumber] as $foundResultKey => $foundResults) {
            $this->checkContainsExpectedResults($expectedResultArrays[$foundResultKey], $foundResults, $sectionNumber, $context);
        }
    }

    private function getTextItems($items)
    {
        $tableValues = [];
        foreach ($items as $item) {
            $tableValues[] = trim(strval($item->getText()));
        }
        $this->comparisonItems[] = $tableValues;
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
}
