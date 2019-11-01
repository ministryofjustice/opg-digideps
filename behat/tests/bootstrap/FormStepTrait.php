<?php

namespace DigidepsBehat;

use Behat\Gherkin\Node\TableNode;

/**
 * Behat steps to test OTPP forms
 */
trait FormStepTrait
{
    /**
     * @Then the step cannot be submitted without making a selection
     */
    public function theStepCannotBeSubmittedWithoutMakingASelection()
    {
        $this->iSubmitTheStep();
        $this->theFormShouldBeInvalid(); // from FormTrait
    }

    /**
     * @Then the step with the following values :what be submitted:
     */
    public function theStepWithTheFollowingValuesCanCannotBeSubmitted(TableNode $table, $what)
    {
        // similar to "fillFields" but takes note of fields wih "ERR"
        $expectedErrors = [];
        foreach ($table->getRowsHash() as $field => $value) {
            if (is_array($value)) {
                if ($value[1]=='[ERR]') {
                    $expectedErrors[] = $field;
                }
                $value = $value[0];
            }
            $this->fillField($field, $value);
        }

        $this->iSubmitTheStep();
        switch (strtolower($what)) {
            case 'can':
                $this->theFormShouldBeValid();  // from FormTrait
                break;
            case 'cannot':
                $this->theFormShouldBeInvalid();  // from FormTrait
                // check fields one by one
//                $this->theFollowingFieldsOnlyShouldHaveAnError(new TableNode($expectedErrors));
                break;
            default:
                throw new \RuntimeException("invalid value: only 'can|cannot' are acceoted");
        }
    }

    /**
     * @Given I choose :what when asked for adding another record
     */
    public function iChooseWhenAskingToAddAnotherRecord($what)
    {
        // check that "add another" has validation (could be tested just once as it's the same form)
        $this->iSubmitTheStep();
        $this->theFormShouldBeInvalid(); // from FormTrait
        switch (strtolower($what)) {
            case 'yes':
                $this->fillField('add_another_addAnother_0', 'yes');
                break;
            case 'no':
                $this->fillField('add_another_addAnother_1', 'no');
                break;
            default:
                throw new \RuntimeException('invalid value');
        }
        $this->clickOnBehatLink('save-and-continue');
    }

    /**
     * @Then I submit the step
     */
    public function iSubmitTheStep()
    {
        $this->clickOnBehatLink('save-and-continue');
    }

    /**
     * @Then I go back from the step
     */
    public function iGoBackFromTheStep()
    {
        $this->clickOnBehatLink('step-back');
    }
}
