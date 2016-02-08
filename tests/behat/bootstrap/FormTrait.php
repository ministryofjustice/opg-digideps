<?php

namespace DigidepsBehat;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;


trait FormTrait
{    

    /**
     * @Then the form should be invalid
     */
    public function theFormShouldBeInvalid()
    {
        //$this->assertResponseStatus(200);
        if (!$this->getSession()->getPage()->has('css','.form-group.error')) {
            throw new \RuntimeException("No errors found");    
        }    
    }
    
    /**
     * @Then the form should be valid
     */
    public function theFormShouldBeValid()
    {
        //$this->assertResponseStatus(200);
        if ($this->getSession()->getPage()->has('css','.form-group.error')) {
            throw new \RuntimeException("Errors found");    
        }
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

        $errorRegions = $this->getSession()->getPage()->findAll('css', ".form-group.error");
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

    /**
     * @Then /^I click on the first decision$/
     * @Then /^I click on the first contact$/
     */
    public function iClickOnTheFirstDecision()
    {
        $this->getSession()->getPage()->clickLink("edit-1-link");
    }

    public function enterIntoField($field, $value) {
        $driver = $this->getSession()->getDriver();

        if (get_class($driver) == 'Behat\Mink\Driver\Selenium2Driver') {
            $this->getSession()->executeScript('document.getElementById("' . $field .'").scrollIntoView(true);');
            $this->getSession()->executeScript('window.scrollBy(0, 40);');
        }
        $this->fillField($field, $value);
    }

    /**
     * Fills in form fields with provided table.
     *
     * @When /^(?:|I )fill in the following:$/
     */
    public function fillFields(TableNode $fields)
    {
        foreach ($fields->getRowsHash() as $field => $value) {
            $this->enterIntoField($field, $value);
        }
    }
}
