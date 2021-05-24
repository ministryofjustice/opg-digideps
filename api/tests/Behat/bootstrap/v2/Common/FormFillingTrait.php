<?php

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
    public function fillInField(string $field, $value, ?string $formSectionName = null)
    {
        if (!is_null($formSectionName)) {
            $this->submittedAnswersByFormSections[$formSectionName][][$field] = $value;
        }

        $this->fillField($field, $value);
    }

    /**
     * @param string      $select          - field id|name|label|value
     * @param string      $option          - field option to select
     * @param string|null $formSectionName - define with any name you like - only include if you want to assert on
     *                                     the value entered on a summary page at the end of the form flow
     */
    public function chooseOption(string $select, string $option, ?string $formSectionName = null)
    {
        if (!is_null($formSectionName)) {
            $this->submittedAnswersByFormSections[$formSectionName][][$select] = $option;
        }

        $this->selectOption($select, $option);
    }

    /**
     * @return mixed
     */
    public function getSectionAnswers(string $sectionName)
    {
        return $this->submittedAnswersByFormSections[$sectionName];
    }
}
