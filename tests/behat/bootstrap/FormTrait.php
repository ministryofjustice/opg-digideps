<?php

namespace DigidepsBehat;

use Behat\Gherkin\Node\TableNode;


trait FormTrait
{    

    /**
     * @Then the form should be invalid
     */
    public function theFormShouldBeInvalid()
    {
        $this->assertResponseStatus(200);
        if (!$this->getSession()->getPage()->has('css','.form-group.error')) {
            throw new \RuntimeException("No errors found");    
        }    
    }
    
    /**
     * @Then the form should be valid
     */
    public function theFormShouldBeValid()
    {
        $this->assertResponseStatus(200);
        if ($this->getSession()->getPage()->has('css','.form-group.error')) {
            throw new \RuntimeException("Errors found in elements: "
                . implode(',', $this->getElementsIdsWithValidationErrors()));    
        }
    }
    
    /**
     * @return array of IDs of input/select/textarea elements inside a  .form-group.error CSS class
     */
    private function getElementsIdsWithValidationErrors()
    {
        $ret = [];
        
        $errorRegions = $this->getSession()->getPage()->findAll('css', ".form-group.error");
        foreach ($errorRegions as $errorRegion) {
            $elementsWithErros = $errorRegion->findAll('xpath', "//*[name()='input' or name()='textarea' or name()='select']");
            foreach ($elementsWithErros as $elementWithError) { /* @var $found \Behat\Mink\Element\NodeElement */
                $ret[] = $elementWithError->getAttribute('id');
            }
        }
        
        return $ret;
    }
    
    /**
     * Check if the given elements (input/textarea inside each .behat-region-form-errors) 
     *  are the only ones with errors 
     * 
     * @Then the following fields should have an error:
     */
    public function theFollowingFieldsOnlyShouldHaveAnError(TableNode $table)
    {
        $foundIdsWithErrors = $this->getElementsIdsWithValidationErrors();
        
        $fields = array_keys($table->getRowsHash());
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
    
    /**
     * @Then the :arg1 field should be expandable
     * @Then the :arg1 field is expandable
     */
    public function expandableField($arg1)
    {
        $element = $this->getSession()->getPage()->find('css', 'textarea#' . $arg1 . '.expanding');
    
        if(!$element) {
            throw new \RuntimeException("Cannot find an expanding textarea with that id: " . $arg1);
        }    
    }


    
}