<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

trait FormFillingTrait
{
    public array $submittedAnswersByFormSections = [];

    /**
     * @param string      $field           field id|name|label|value
     * @param mixed       $value           field value to enter
     * @param string|null $formSectionName define with any name you like - only include if you want to assert on the
     *                                     value entered on a summary page at the end of the form flow
     * @param int         $answerGroup     set to a value greater than 0 if form answers are grouped together to enable
     *                                     removing all associated answers when required
     */
    public function fillInField(string $field, $value, ?string $formSectionName = null, int $answerGroup = 0)
    {
        if ($formSectionName) {
            $this->submittedAnswersByFormSections[$formSectionName][$answerGroup][$field] = $value;
        }

        $this->fillField($field, $value);
    }

    /**
     * @param string      $select          select id|name|label|value
     * @param mixed       $option          option value to choose
     * @param string|null $formSectionName define with any name you like - only include if you want to assert on
     *                                     the value entered on a summary page at the end of the form flow
     * @param int         $answerGroup     set to a value greater than 0 if form answers are grouped together to enable
     *                                     removing all associated answers when required
     */
    public function chooseOption(string $select, $option, ?string $formSectionName = null, int $answerGroup = 0)
    {
        if ($formSectionName) {
            $this->submittedAnswersByFormSections[$formSectionName][$answerGroup][$select] = $option;
        }

        $this->selectOption($select, $option);
    }

    /**
     * @return mixed
     */
    public function getSectionAnswers(string $formSectionName)
    {
        return $this->submittedAnswersByFormSections[$formSectionName];
    }

    /**
     * Removes an answer from a section of the form being completed - via the interface and the
     * submitted field values in $this->submittedAnswersByFormSections. If multiple fields form one answer
     * and were grouped using a number (e.g.
     * [ 'sectionName' => [ 0 => ['field1' => 'abc, 'field2' => 123 ], 1 => ['field1' => 'def, 'field2' => 123 ] ] ] ),
     * provide the group number as the fourth argument to remove grouped answers under that group number.
     *
     * @param string $fieldInAnswerGroupToRemove The field/option id|name|label|value as set in fillInField() or
     *                                           chooseOption() to match on
     * @param string $formSectionName            The name given to the section of the form being completed as set in
     *                                           fillInField() or chooseOption()
     * @param string $removeButtonText           Text value of the remove button on confirmation page
     * @param int    $answerGroupNumber          The number used to associate field/option answers when filling the
     *                                           form in (see fillInField() and chooseOption())
     *
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    public function removeAnswerFromSection(
        string $fieldInAnswerGroupToRemove,
        string $formSectionName,
        string $removeButtonText,
        int $answerGroupNumber = 0
    ) {
        $answers = $this->getSectionAnswers($formSectionName);

        $rowSelector = sprintf('//tr[th[normalize-space() ="%s"]]', $answers[$answerGroupNumber][$fieldInAnswerGroupToRemove]);
        $descriptionTableRow = $this->getSession()->getPage()->find('xpath', $rowSelector);

        $descriptionTableRow->clickLink('Remove');
        $this->pressButton($removeButtonText);

        unset($this->submittedAnswersByFormSections[$formSectionName][$answerGroupNumber]);
    }

    public function removeAllAnswers()
    {
        unset($this->submittedAnswersByFormSections);
    }
}
