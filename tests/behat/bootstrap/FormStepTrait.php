<?php

namespace DigidepsBehat;

use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Console\Helper\Table;

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
        $this->stepSaveAndContinue();
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
        
        $this->stepSaveAndContinue();
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
     * @Then I fill in the step with the following, save and go back checking it's saved:
     */
    public function iFillInTheStepWithTheFollowingSaveAndGoBackCheckingItSSaved(TableNode $table)
    {
        $this->theStepWithTheFollowingValuesCanCannotBeSubmitted($table, 'can');  // from FormTrait
        $this->stepGoBack();
        $this->followingFieldsShouldHaveTheCorrespondingValues($table); //from FormTrait
    }


    /**
     * @Given I choose :what when asked for adding another record
     */
    public function iChooseWhenAskingToAddAnotherRecord($what)
    {
        // check that "add another" has validation (could be tested just once as it's the same form)
        $this->stepSaveAndContinue();
        $this->theFormShouldBeInvalid(); // from FormTrait
        switch (strtolower($what)) {
            case 'yes':
                $this->fillField('add_another_addAnother_0', 'yes');
                break;
            case 'no':
                $this->fillField('add_another_addAnother_1', 'no');
                break;
            default:
                throw new \RuntimeException("invalid value");
        }
        $this->clickOnBehatLink('save-and-continue');
    }

    private function stepSaveAndContinue()
    {
        $this->clickOnBehatLink('save-and-continue');
    }

    private function stepGoBack()
    {
        $this->clickOnBehatLink('step-back');
    }
}
