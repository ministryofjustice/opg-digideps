<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

trait FormFillingTrait
{
    public array $submittedAnswersByFormSections = [];

    /**
     * @param string $field - field id|name|label|value
     * @param $value - field value to enter
     * @param string|null $formSectionName - define with any name you like - only include if you want to assert on
     *                                     the value entered on a summary page at the end of the form flow
     */
    public function fillInField(string $field, $value, ?string $formSectionName = null, int $answerGroup = 0)
    {
        if ($formSectionName) {
            $this->submittedAnswersByFormSections[$formSectionName][$answerGroup][$field] = $value;
        }

        $this->fillField($field, $value);
    }

    /**
     * @param string      $select          - field id|name|label|value
     * @param string      $option          - field option to select
     * @param string|null $formSectionName - define with any name you like - only include if you want to assert on
     *                                     the value entered on a summary page at the end of the form flow
     * @param int         $answerGroup     - set to a value greater than 0 if form answers are grouped together to enable removing all associated answers
     */
    public function chooseOption(string $select, string $option, ?string $formSectionName = null, int $answerGroup = 0)
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
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    public function removeAnswerFromSection(
        int $answerGroupNumber,
        string $fieldInAnswerGroupToRemove,
        string $formSectionName,
        string $removeButtonText
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
