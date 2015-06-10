<?php

namespace DigidepsBehat;

use Behat\Gherkin\Node\TableNode;


trait FormTrait
{    

    /**
     * @Then the form should contain an error
     */
    public function theFormShouldContainAnError()
    {
        $this->iShouldSeeTheBehatElement('form-errors', 'region');
    }
    
    /**
     * @Then the form should not contain an error
     * @Then the form should not contain any error
     */
    public function theFormShouldNotContainAnError()
    {
        $this->iShouldNotSeeTheBehatElement('form-errors', 'region');
    }
    
    /**
     * Check if the given elements (input/textarea inside each .behat-region-form-errors) 
     *  are the only ones with errors 
     * 
     * @Then the following fields should have an error:
     */
    public function theFollowingFieldsOnlyShouldHaveAnError(TableNode $table)
    {
        $fields = array_keys($table->getRowsHash());
        $errorRegionCss = self::behatElementToCssSelector('form-errors', 'region');
        $errorRegions = $this->getSession()->getPage()->findAll('css', $errorRegionCss);
        $foundIdsWithErrors = [];
        foreach ($errorRegions as $errorRegion) {
            $elementsWithErros = $errorRegion->findAll('xpath', "//*[name()='input' or name()='textarea' or name()='select']");
            foreach ($elementsWithErros as $elementWithError) { /* @var $found \Behat\Mink\Element\NodeElement */
                $foundIdsWithErrors[] = $elementWithError->getAttribute('id');
            }
        }
        $untriggeredField = array_diff($fields, $foundIdsWithErrors);
        $unexpectedFields = array_diff($foundIdsWithErrors, $fields);
        
        if ($untriggeredField || $unexpectedFields) {
            $message = "";
            if ($untriggeredField) {
                $message .= " - Form fields not throwing error as expected: \n      " . implode(', ', $untriggeredField) . "\n";
            }
            if ($unexpectedFields) {
                 $message .= " - Form fields unexpectedly throwing errors: \n      " . implode(', ', $unexpectedFields) . "\n";
            }
            
            throw new \RuntimeException($message);
        }
    }
    
     /**
     * @Then /^the following fields should have the corresponding values:$/
     */
    public function followingFieldsShouldHaveTheCorrespondingValues(TableNode $fields)
    {
        foreach ($fields->getRowsHash() as $field => $value) {
            $this->assertFieldContains($field, $value);
        }
    }


    
}