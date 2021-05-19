<?php

namespace App\Tests\Behat\v2\Common;

trait FormFillingTrait
{
    public array $formSectionsAndAnswers = [];

    public function fillInField(string $fieldName, $fieldValue, ?string $formSectionName = null)
    {
        if (!is_null($formSectionName)) {
            $this->formSectionsAndAnswers[$formSectionName][][$fieldName] = $fieldValue;
        }

        $this->fillField($fieldName, $fieldValue);
    }

    public function chooseOption(string $fieldName, string $fieldOption, ?string $formSectionName = null)
    {
        if (!is_null($formSectionName)) {
            $this->formSectionsAndAnswers[$formSectionName][][$fieldName] = $fieldOption;
        }

        $this->selectOption($fieldName, $fieldOption);
    }
}
