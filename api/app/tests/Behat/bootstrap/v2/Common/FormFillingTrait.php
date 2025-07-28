<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use App\Tests\Behat\BehatException;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;

/**
 * Trait FormFillingTrait.
 */
trait FormFillingTrait
{
    public array $submittedAnswersByFormSections = ['totals' => ['grandTotal' => 0]];

    /**
     * @param string      $field           field id|name|label|value
     * @param mixed       $value           field value to enter
     * @param string|null $formSectionName define with any name you like - only include if you want to assert on the
     *                                     value entered on a summary page at the end of the form flow
     * @param string|null $formattedValue  int values are automatically formatted into currency strings (e.g 21
     *                                     becomes £21.00). To override this, or add any other formatting, pass this
     *                                     argument tot he function
     */
    public function fillInField(string $field, $value, ?string $formSectionName = null, ?string $formattedValue = null)
    {
        if ($formSectionName) {
            $this->addToSubmittedAnswersByFormSections($formSectionName, $field, $value, $formattedValue);
        }

        $this->fillField($field, $value);
    }

    /**
     * @param string      $fieldName       The field name minus the date portion e.g. made instead of made['month']
     * @param int|null    $day             The day of the date in number format (leave null if not required) e.g. 25
     * @param int|null    $month           The month of the date in number format (leave null if not required) e.g. 11
     * @param int|null    $year            The year of the date in 4 digit format (leave null if not required) e.g. 2021
     * @param string|null $formSectionName define with any name you like - only include if you want to assert on the
     *                                     value entered on a summary page at the end of the form flow
     */
    public function fillInDateFields(string $fieldName, ?int $day, ?int $month, ?int $year, ?string $formSectionName = null)
    {
        $fullDate = '';

        if ($day) {
            $dayField = sprintf('%s[day]', $fieldName);
            $this->fillField($dayField, $day);
            $fullDate = $day;
        }

        if ($month) {
            $monthField = sprintf('%s[month]', $fieldName);
            $this->fillField($monthField, $month);
            $monthName = \DateTime::createFromFormat('!m', strval($month))->format('F');

            $fullDate .= " $monthName";
        }

        if ($year) {
            $yearField = sprintf('%s[year]', $fieldName);
            $this->fillField($yearField, $year);
            $fullDate .= " $year";
        }

        if ($formSectionName) {
            $answerGroup = $this->determineAnswerGroup($formSectionName, $fieldName);
            $this->submittedAnswersByFormSections[$formSectionName][$answerGroup][$fieldName] = trim($fullDate);
        }
    }

    /**
     * @param string      $fieldName       The field name minus the date portion e.g. account[sortCode] account[sortCode][sort_code_part_1]
     * @param string      $fullSortCode    The full sort code in the format 01-02-03
     * @param string|null $formSectionName Define with any name you like - only include if you want to assert on the
     *                                     value entered on a summary page at the end of the form flow
     */
    public function fillInSortCodeFields(
        string $fieldName,
        string $fullSortCode,
        string $formSectionName = null
    ) {
        $sortCodeParts = explode('-', $fullSortCode);

        $firstDigitsField = sprintf('%s[sort_code_part_1]', $fieldName);
        $this->fillField($firstDigitsField, $sortCodeParts[0] ?? null);

        $secondDigitsField = sprintf('%s[sort_code_part_2]', $fieldName);
        $this->fillField($secondDigitsField, $sortCodeParts[1] ?? null);

        $thirdDigitsField = sprintf('%s[sort_code_part_3]', $fieldName);
        $this->fillField($thirdDigitsField, $sortCodeParts[2] ?? null);

        if ($formSectionName && is_numeric(str_replace('-', '', $fullSortCode))) {
            $answerGroup = $this->determineAnswerGroup($formSectionName, $fieldName);
            $this->submittedAnswersByFormSections[$formSectionName][$answerGroup][$fieldName] = str_replace('-', '', $fullSortCode);
        }
    }

    /**
     * Keeps a running total of the int values entered in the field stored against each formSectionName in
     * $submittedAnswersByFormSections and a grand total to assert on.
     *
     * @param string      $field           field id|name|label|value
     * @param int         $value           field value to enter (to be added to a running total)
     * @param string|null $formSectionName define with any name you like - only include if you want to assert on the
     *                                     value entered on a summary page at the end of the form flow
     */
    public function fillInFieldTrackTotal(string $field, int $value, string $formSectionName = null)
    {
        $this->fillInField($field, $value, $formSectionName);

        if (isset($this->submittedAnswersByFormSections['totals'][$formSectionName])) {
            $this->submittedAnswersByFormSections['totals'][$formSectionName] += $value;
        } else {
            $this->submittedAnswersByFormSections['totals'][$formSectionName] = $value;
        }

        $this->submittedAnswersByFormSections['totals']['grandTotal'] += $value;
    }

    /**
     * @param string      $select           select id|name|label|value
     * @param mixed       $option           option value to choose
     * @param string|null $formSectionName  define with any name you like - only include if you want to assert on
     *                                      the value entered on a summary page at the end of the form flow
     * @param string|null $translatedOption The full or partial string the option is translated to on the front end
     *                                      e.g. all_care_is_paid_by_someone_else could be:
     *
     *                                      'All John's care is paid for by someone else (for example, by the local authority, council or NHS)'
     *                                      or
     *                                      'paid for by someone else'
     *
     *                                      Only provide this if you are asserting on the translation on the summary page.
     */
    public function chooseOption(string $select, $option, string $formSectionName = null, string $translatedOption = null)
    {
        $this->selectOption($select, $option);

        if ($formSectionName) {
            $answerGroup = $this->determineAnswerGroup($formSectionName, $select);
            $option = $translatedOption ?: $option;
            $this->submittedAnswersByFormSections[$formSectionName][$answerGroup][$select] = $option;
        }
    }

    /**
     * @param string      $checkboxGroupName define with a name that describes the checkbox group - keep consistent with
     *                                       all checkboxes in the group that are ticked
     * @param mixed       $option            option value to check
     * @param string|null $formSectionName   define with any name you like - only include if you want to assert on
     *                                       the value entered on a summary page at the end of the form flow
     * @param string|null $translatedOption  The full or partial string the option is translated to on the front end
     *                                       e.g. all_care_is_paid_by_someone_else could be:
     *
     *                                       'All John's care is paid for by someone else (for example, by the local authority, council or NHS)'
     *                                       or
     *                                       'paid for by someone else'
     *
     *                                       Only provide this if you are asserting on the translation on the summary page.
     */
    public function tickCheckbox(string $checkboxGroupName, $option, string $formSectionName = null, string $translatedOption = null)
    {
        $this->checkOption($option);

        if ($formSectionName) {
            $option = $translatedOption ?: $option;
            $this->submittedAnswersByFormSections[$formSectionName][0][$checkboxGroupName][] = $option;
        }
    }

    private function determineAnswerGroup(string $formSectionName, string $inputLabel): int
    {
        $lastMatchingIndex = 0;

        if (!empty($this->submittedAnswersByFormSections[$formSectionName])) {
            foreach ($this->submittedAnswersByFormSections[$formSectionName] as $answerGroup) {
                if (is_array($answerGroup) && array_key_exists($inputLabel, $answerGroup)) {
                    ++$lastMatchingIndex;
                }
            }
        }

        return $lastMatchingIndex;
    }

    /**
     * @return mixed
     */
    public function getSectionAnswers(string $formSectionName)
    {
        return $this->submittedAnswersByFormSections[$formSectionName] ?? null;
    }

    public function getSectionTotal(string $formSectionName): ?int
    {
        return $this->submittedAnswersByFormSections['totals'][$formSectionName] ?? null;
    }

    /**
     * @return int|float|null
     */
    public function getGrandTotal()
    {
        return $this->submittedAnswersByFormSections['totals']['grandTotal'] ?? null;
    }

    public function removeSection(string $formSectionName)
    {
        unset($this->submittedAnswersByFormSections[$formSectionName]);
    }

    public function removeSectionAnswerGroup(string $formSectionName, int $answerGroupToRemove, string $fieldToRemove, bool $fullGroup = true)
    {
        if ($fullGroup) {
            unset($this->submittedAnswersByFormSections[$formSectionName][$answerGroupToRemove]);
        } else {
            unset($this->submittedAnswersByFormSections[$formSectionName][$answerGroupToRemove][$fieldToRemove]);
        }
    }

    public function removeSectionTotal(string $formSectionName)
    {
        if ($this->submittedAnswersByFormSections['totals'][$formSectionName] ?? null) {
            unset($this->submittedAnswersByFormSections['totals'][$formSectionName]);
        }
    }

    public function addToSectionTotal(string $formSectionName, $amountToAdd)
    {
        if (isset($this->submittedAnswersByFormSections['totals'][$formSectionName])) {
            $this->submittedAnswersByFormSections['totals'][$formSectionName] += $amountToAdd;
        } else {
            $this->submittedAnswersByFormSections['totals'][$formSectionName] = $amountToAdd;
        }
    }

    public function addToGrandTotal($amountToAdd)
    {
        $this->submittedAnswersByFormSections['totals']['grandTotal'] += $amountToAdd;
    }

    public function subtractFromSectionTotal(string $formSectionName, $amountToSubtract)
    {
        if (isset($this->submittedAnswersByFormSections['totals'][$formSectionName])) {
            $this->submittedAnswersByFormSections['totals'][$formSectionName] -= $amountToSubtract;
        } else {
            $this->submittedAnswersByFormSections['totals'][$formSectionName] = $amountToSubtract;
        }
    }

    public function addToSubmittedAnswersByFormSections($formSectionName, $field, $value, string $formattedValue = null)
    {
        $answerGroup = $this->determineAnswerGroup($formSectionName, $field);

        $this->submittedAnswersByFormSections[$formSectionName][$answerGroup][$field] = $formattedValue ?: $value;
    }

    public function subtractFromGrandTotal($amountToSubtract)
    {
        $this->submittedAnswersByFormSections['totals']['grandTotal'] -= $amountToSubtract;
    }

    /**
     * Removes an answer from a section of the form being completed - via the interface and the
     * submitted field values in $this->submittedAnswersByFormSections. If multiple fields form one answer
     * and were grouped using a number (e.g.
     * [ 'sectionName' => [ 0 => ['field1' => 'abc, 'field2' => 123 ], 1 => ['field1' => 'def, 'field2' => 123 ] ] ] ).
     *
     * @param string      $fieldInAnswerGroupToRemove The field/option id|name|label|value as set in fillInField() or
     *                                                chooseOption() to match on
     * @param string      $formSectionName            The name given to the section of the form being completed as set in
     *                                                fillInField() or chooseOption()
     * @param bool        $fullGroup                  whether to remove the entire group or specific row denoted by
     *                                                $fieldInAnswerGroupToRemove
     * @param string|null $removeButtonText           Text value of the remove button on confirmation page. If null,
     *                                                the value will be removed from $submittedAnswersByFormSections
     *                                                but no attempt is made to click a button
     *
     * @throws BehatException
     */
    public function removeAnswerFromSection(
        string $fieldInAnswerGroupToRemove,
        string $formSectionName,
        bool $fullGroup = true,
        string $removeButtonText = null
    ) {
        $answers = $this->getSectionAnswers($formSectionName);

        $answerGroupToRemove = null;

        if (!is_array($answers)) {
            throw new BehatException(sprintf('Section answers for "%s" could not be found', $formSectionName));
        }

        foreach ($answers as $index => $answerGroup) {
            if (is_array($answerGroup) && in_array($fieldInAnswerGroupToRemove, array_keys($answerGroup))) {
                $answerGroupToRemove = $index;
                break;
            }
        }

        if (is_null($answerGroupToRemove)) {
            $formattedAnswers = json_encode($this->submittedAnswersByFormSections, JSON_PRETTY_PRINT);

            throw new BehatException(sprintf('Tried to remove an answer but could not find submitted answers that contained the requested field name \'%s\'. Completed fields are: %s', $fieldInAnswerGroupToRemove, $formattedAnswers));
        }

        if (!is_null($removeButtonText)) {
            $normalizedAnswer = $this->normalizeIntToCurrencyString($answers[$answerGroupToRemove][$fieldInAnswerGroupToRemove]);

            $rowSelector = sprintf(
                '//tr[th[contains(.,"%s")]] | //td[contains(.,"%s")]/.. | //dd[contains(.,"%s")]/.. | //dt[contains(.,"%s")]/..',
                $normalizedAnswer,
                $normalizedAnswer,
                $normalizedAnswer,
                $normalizedAnswer,
            );

            $descriptionTableRow = $this->getSession()->getPage()->find('xpath', $rowSelector);

            $descriptionTableRow->clickLink('Remove');
            $this->pressButton($removeButtonText);
        }

        if (!is_null($this->getSectionTotal($formSectionName))) {
            foreach ($answers[$answerGroupToRemove] as $fieldName => $value) {
                if ((is_int($value) || is_float($value)) && $fieldName === $fieldInAnswerGroupToRemove) {
                    $this->subtractFromSectionTotal($formSectionName, $value);
                    $this->subtractFromGrandTotal($value);
                }
            }
        }

        $this->removeSectionAnswerGroup($formSectionName, $answerGroupToRemove, $fieldInAnswerGroupToRemove, $fullGroup);
    }

    /**
     * @param NodeElement $summaryRowToEdit The NodeElement of the item row on a summary page to edit
     * @param string      $fieldName        The name of the form field to add a new value to
     * @param string      $formSectionName  Which section name in $submittedAnswersByFormSections the item to
     *                                      edit belongs to
     * @param bool        $fullGroup        Whether to remove the entire group or specific row denoted by
     *                                      $fieldInAnswerGroupToRemove
     * @param int|null    $value            Value to edit the field to
     *
     * @return array Returns a list, not array, of the old and new value so the variables
     *               can be accessed directly rather than accessing via an array
     *
     * @throws BehatException
     * @throws ElementNotFoundException
     */
    public function editFieldAnswerInSectionTrackTotal(NodeElement $summaryRowToEdit, string $fieldName, string $formSectionName, bool $fullGroup = true, int $value = null): array
    {
        $currentValueString = $summaryRowToEdit->find('xpath', '//td[contains(.,"£")] | //dd[contains(.,"£")]')->getText();
        $currentValueInt = intval(str_replace([',', '£'], '', $currentValueString));

        $this->removeAnswerFromSection($fieldName, $formSectionName, $fullGroup);

        $summaryRowToEdit->clickLink('Edit');

        $newValue = $value ?: $this->faker->numberBetween(1, 10000);

        $this->fillInFieldTrackTotal(
            $fieldName,
            $newValue,
            $formSectionName
        );

        $this->pressButton('Save and continue');

        return list($currentValueInt, $newValue) = [$currentValueInt, $newValue];
    }

    /**
     * @param NodeElement $summaryRowToEdit The NodeElement of the item row on a summary page to edit
     * @param string      $fieldName        The name of the form field to add a new value to
     * @param mixed       $newValue         The new value
     * @param string      $formSectionName  Which section name in $submittedAnswersByFormSections the item to
     *                                      edit belongs to
     * @param bool        $fullGroup        Whether to use full group or the individual row
     *
     * @throws BehatException
     */
    public function editFieldAnswerInSection(NodeElement $summaryRowToEdit, string $fieldName, $newValue, string $formSectionName, bool $fullGroup = true)
    {
        $this->removeAnswerFromSection($fieldName, $formSectionName, $fullGroup);

        $summaryRowToEdit->clickLink('Edit');

        $this->fillInField(
            $fieldName,
            $newValue,
            $formSectionName
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @param NodeElement $summaryRowToEdit The NodeElement of the edit link in the item row on a summary page to edit
     * @param string      $selectName       The name of the form select to add a new value to
     * @param string      $newSelectOption  The new option
     * @param string      $formSectionName  Which section name in $submittedAnswersByFormSections the item to
     *                                      edit belongs to
     * @param string|null $translatedValue  The translated string of the option selected (optional)
     * @param bool        $fullGroup        Whether to use full group or the individual row
     *
     * @throws ElementNotFoundException
     */
    public function editSelectAnswerInSection(NodeElement $summaryRowToEdit, string $selectName, string $newSelectOption, string $formSectionName, string $translatedValue = null, bool $fullGroup = true)
    {
        $this->removeAnswerFromSection($selectName, $formSectionName, $fullGroup);

        $summaryRowToEdit->clickLink('Edit');

        $this->chooseOption(
            $selectName,
            $newSelectOption,
            $formSectionName,
            $translatedValue
        );

        $this->pressButton('Save and continue');
    }

    public function removeAllAnswers()
    {
        unset($this->submittedAnswersByFormSections);
    }

    /**
     * Call this function after executing a step that triggers an ajax request to refresh the page.
     */
    public function waitForAjaxAndRefresh()
    {
        while ($refresh = $this->getSession()->getPage()->find('css', 'meta[http-equiv="refresh"]')) {
            $content = $refresh->getAttribute('content');
            $url = preg_replace('/^\d+;\s*URL=/i', '', $content);

            $this->getSession()->visit($url);
        }
    }

    public function updateExpectedAnswerInSection($select, $formSectionName, $newAnswer)
    {
        $answerGroup = $this->determineAnswerGroup($formSectionName, $select);
        $this->submittedAnswersByFormSections[$formSectionName][$answerGroup][$select] = $newAnswer;
    }
}
