<?php

namespace App\Tests\Behat\v2\Common;

trait FormFillingTrait
{
    public array $submittedAnswersByFormSections = [];

    public function fillInField(string $fieldName, $fieldValue, ?string $formSectionName = null)
    {
        if (!is_null($formSectionName)) {
            $this->submittedAnswersByFormSections[$formSectionName][][$fieldName] = $fieldValue;
        }

        $this->fillField($fieldName, $fieldValue);
    }

    public function chooseOption(string $fieldName, string $fieldOption, ?string $formSectionName = null)
    {
        if (!is_null($formSectionName)) {
            $this->submittedAnswersByFormSections[$formSectionName][][$fieldName] = $fieldOption;
        }

        $this->selectOption($fieldName, $fieldOption);
    }
}
