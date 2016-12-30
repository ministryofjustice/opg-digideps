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
        $this->stepSaveAndContinue();
        $this->theFormShouldBeInvalid(); // from FormTrait
    }


    /**
     * @Then the step with the following values :what be submitted:
     */
    public function theStepWithTheFollowingValuesCanCannotBeSubmitted(TableNode $table, $what)
    {
        $this->fillFields($table); // from MinkContext
        $this->stepSaveAndContinue();
        switch (strtolower($what)) {
            case 'can':
                $this->theFormShouldBeValid();  // from FormTrait
                break;
            case 'cannot':
                $this->theFormShouldBeInvalid();  // from FormTrait
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


    private function stepSaveAndContinue()
    {
        $this->clickOnBehatLink('save-and-continue');
    }


    private function stepGoBack()
    {
        $this->clickOnBehatLink('step-back');
    }
}
