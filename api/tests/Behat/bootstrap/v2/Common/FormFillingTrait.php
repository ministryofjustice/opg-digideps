<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use App\Tests\Behat\BehatException;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use DateTime;

trait FormFillingTrait
{
    public array $submittedAnswersByFormSections = [];

    /**
     * @param string      $field           field id|name|label|value
     * @param mixed       $value           field value to enter
     * @param string|null $formSectionName define with any name you like - only include if you want to assert on the
     *                                     value entered on a summary page at the end of the form flow
     */
    public function fillInField(string $field, $value, ?string $formSectionName = null)
    {
        if ($formSectionName) {
            $answerGroup = $this->determineAnswerGroup($formSectionName, $field);
            $this->submittedAnswersByFormSections[$formSectionName][$answerGroup][$field] = $value;
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
            $monthName = DateTime::createFromFormat('!m', strval($month))->format('F');

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
     * Keeps a running total of the int values entered in the field stored against each formSectionName in
     * $submittedAnswersByFormSections and a grand total to assert on.
     *
     * @param string      $field           field id|name|label|value
     * @param int         $value           field value to enter (to be added to a running total)
     * @param string|null $formSectionName define with any name you like - only include if you want to assert on the
     *                                     value entered on a summary page at the end of the form flow
     */
    public function fillInFieldTrackTotal(string $field, int $value, ?string $formSectionName = null)
    {
        $this->fillInField($field, $value, $formSectionName);
        $this->submittedAnswersByFormSections['totals'][$formSectionName] += $value;
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
    public function chooseOption(string $select, $option, ?string $formSectionName = null, ?string $translatedOption = null)
    {
        $this->selectOption($select, $option);

        if ($formSectionName) {
            $answerGroup = $this->determineAnswerGroup($formSectionName, $select);
            $option = $translatedOption ?: $option;
            $this->submittedAnswersByFormSections[$formSectionName][$answerGroup][$select] = $option;
        }
    }

    private function determineAnswerGroup(string $formSectionName, string $inputLabel)
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
        return $this->submittedAnswersByFormSections[$formSectionName];
    }

    public function getSectionTotal(string $formSectionName): ?int
    {
        return $this->submittedAnswersByFormSections['totals'][$formSectionName];
    }

    public function getGrandTotal(): ?int
    {
        return $this->submittedAnswersByFormSections['totals']['grandTotal'];
    }

    public function removeSection(string $formSectionName)
    {
        unset($this->submittedAnswersByFormSections[$formSectionName]);
    }

    public function removeSectionAnswerGroup(string $formSectionName, int $answerGroupToRemove)
    {
        unset($this->submittedAnswersByFormSections[$formSectionName][$answerGroupToRemove]);
    }

    /**
     * @param string $answerGroupToRemove
     */
    public function removeSectionTotal(string $formSectionName)
    {
        unset($this->submittedAnswersByFormSections['totals'][$formSectionName]);
    }

    public function addToSectionTotal(string $formSectionName, int $amountToAdd)
    {
        $this->submittedAnswersByFormSections['totals'][$formSectionName] += $amountToAdd;
    }

    /**
     * @param string $formSectionName
     */
    public function addToGrandTotal(int $amountToAdd)
    {
        $this->submittedAnswersByFormSections['totals']['grandTotal'] += $amountToAdd;
    }

    public function subtractFromSectionTotal(string $formSectionName, int $amountToSubtract)
    {
        $this->submittedAnswersByFormSections['totals'][$formSectionName] -= $amountToSubtract;
    }

    /**
     * @param string $formSectionName
     */
    public function subtractFromGrandTotal(int $amountToSubtract)
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
     * @param string|null $removeButtonText           Text value of the remove button on confirmation page. If null,
     *                                                the value will be removed from $submittedAnswersByFormSections
     *                                                but no attempt is made to click a button
     *
     * @throws ElementNotFoundException
     */
    public function removeAnswerFromSection(
        string $fieldInAnswerGroupToRemove,
        string $formSectionName,
        ?string $removeButtonText = null
    ) {
        $answers = $this->getSectionAnswers($formSectionName);

        $answerGroupToRemove = null;

        foreach ($answers as $index => $answerGroup) {
            if (is_array($answerGroup) && in_array($fieldInAnswerGroupToRemove, array_keys($answerGroup))) {
                $answerGroupToRemove = $index;
                break;
            }
        }

        if (is_null($answerGroupToRemove)) {
            throw new BehatException(sprintf('Tried to remove an answer but could not find submitted answers that contained the requested field name \'%s\'', $fieldInAnswerGroupToRemove));
        }

        if (!is_null($removeButtonText)) {
            $rowSelector = sprintf('//tr[th[normalize-space() ="%s"]]', $answers[$answerGroupToRemove][$fieldInAnswerGroupToRemove]);
            $descriptionTableRow = $this->getSession()->getPage()->find('xpath', $rowSelector);
            $descriptionTableRow->clickLink('Remove');
            $this->pressButton($removeButtonText);
        }

        if (!is_null($this->getSectionTotal($formSectionName))) {
            foreach ($this->submittedAnswersByFormSections[$formSectionName][$answerGroupToRemove] as $value) {
                if (is_int($value)) {
                    $this->subtractFromSectionTotal($formSectionName, $value);
                    $this->subtractFromGrandTotal($value);
                }
            }
        }

        $this->removeSectionAnswerGroup($formSectionName, $answerGroupToRemove);
    }

    /**
     * @param NodeElement $summaryRowToEdit The NodeElement of the item row on a summary page to edit
     * @param string      $fieldName        The name of the form field to add a new value to
     * @param string      $formSectionName  Which section name in $submittedAnswersByFormSections the item to
     *                                      edit belongs to
     *
     * @return array Returns a list, not array, of the old and new value so the variables
     *               can be accessed directly rather than accessing via an array
     *
     * @throws ElementNotFoundException
     */
    public function editAnswerInSectionTrackTotal(NodeElement $summaryRowToEdit, string $fieldName, string $formSectionName): array
    {
        $currentValueString = $summaryRowToEdit->find('xpath', '//td[text()[contains(.,"£")]]')->getText();
        $currentValueInt = intval(str_replace([',', '£'], '', $currentValueString));

        $this->removeAnswerFromSection($fieldName, $formSectionName);

        $summaryRowToEdit->clickLink('Edit');

        $newValue = $this->faker->numberBetween(1, 10000);

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
     * @param string      $newValue         The new value
     * @param string      $formSectionName  Which section name in $submittedAnswersByFormSections the item to
     *                                      edit belongs to
     *
     * @throws ElementNotFoundException
     */
    public function editAnswerInSection(NodeElement $summaryRowToEdit, string $fieldName, string $newValue, string $formSectionName): array
    {
        $this->removeAnswerFromSection($fieldName, $formSectionName);

        $summaryRowToEdit->clickLink('Edit');

        $this->fillInField(
            $fieldName,
            $newValue,
            $formSectionName
        );

        $this->pressButton('Save and continue');
    }

    public function removeAllAnswers()
    {
        unset($this->submittedAnswersByFormSections);
    }
}
