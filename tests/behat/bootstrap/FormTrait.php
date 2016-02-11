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
    
    /**
     * Fills in form field with specified id|name|label|value.
     *
     * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with:$/
     * @When /^(?:|I )fill in "(?P<value>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)"$/
     */
    public function fillField($field, $value)
    {
        $field = $this->fixStepArgument($field);
        $value = $this->fixStepArgument($value);

        if (substr($field,0,1) != '.' && substr($field,0,1) != '#') {
            $field = '#' . $field;
        }
        
        $this->scrollTo($field);

        $javascript =  <<<EOT
            var field = $('$field');
            var value = '$value';
            
            $(':focus').trigger('blur').trigger('change');
            var tag = field.prop('tagName');
      
            if (field.prop('type') === 'checkbox' || 
                field.prop('type') === 'radio')
            {
            
                field.prop('checked', true);
            
            } else if (tag === 'SELECT') {
                
                field.focus().val(value).trigger('change');
            
            } else {
                var pos = 0,
                    length = value.length,
                    character, charCode;
                    
                for (;pos < length; pos += 1) {
                    
                    character = value[pos];
                    charCode = character.charCodeAt(0);
                    
                    var keyPressEvent = $.Event('keypress', {which: charCode}),
                        keyDownEvent = $.Event('keydown', {which: charCode}),
                        keyUpEvent = $.Event('keyup', {which: charCode});
                    
                    field
                        .focus()
                        .trigger(keyDownEvent)
                        .trigger(keyPressEvent)
                        .val(value.substr(0,pos+1))
                        .trigger(keyUpEvent);
    
                }
            }

EOT;
        
        $this->getSession()->executeScript($javascript);
        
    }
}
