<?php

namespace App\Tests\Behat\Common;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;

trait FormTrait
{
    /**
     * Assert the page returs HTTP 200
     * and contains the ".form-group.form-group-error", ".govuk-form-group--error" or the "#error-summary-heading" elements.
     *
     * @Then the form should be invalid
     */
    public function theFormShouldBeInvalid()
    {
        $this->assertResponseStatus(200);

        if (!$this->getSession()->getPage()->has('css', '.form-group.form-group-error')
            && !$this->getSession()->getPage()->has('css', '.govuk-form-group--error')
            && !$this->getSession()->getPage()->has('css', '#error-summary-heading')) {
            throw new \RuntimeException('No errors found');
        }
    }

    /**
     * Assert the page returs HTTP 200
     * and does NOT contain the ".form-group.form-group-error", ".govuk-form-group--error" nor the "#error-summary-heading" elements.
     *
     * @Then the form should be valid
     */
    public function theFormShouldBeValid()
    {
        $driver = $this->getSession()->getDriver();
        if ('Behat\Mink\Driver\Selenium2Driver' != get_class($driver)) {
            $this->assertResponseStatus(200);
        }

        $page = $this->getSession()->getPage();

        if ($page->has('css', '.form-group.form-group-error')
            || $page->has('css', '.govuk-form-group--error')
            || $page->has('css', '#error-summary-heading')) {
            throw new \RuntimeException('Errors found in elements: '.implode(',', $this->getElementsIdsWithValidationErrors()));
        }
    }

    /**
     * @return array of IDs of input/select/textarea elements inside a  .form-group.form-group-error CSS class
     */
    private function getElementsIdsWithValidationErrors()
    {
        $ret = [];

        $errorRegions = array_merge(
            $this->getSession()->getPage()->findAll('css', '.form-group.form-group-error'),
            $this->getSession()->getPage()->findAll('css', '.govuk-form-group--error')
        );
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
     *  are the only ones with errors.
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
            $message = '';
            if ($untriggeredField) {
                $message .= " - Form fields not throwing error as expected: \n      ".implode(', ', $untriggeredField)."\n";
            }
            if ($unexpectedFields) {
                $message .= " - Form fields unexpectedly throwing errors: \n      ".implode(', ', $unexpectedFields)."\n";
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
     * @Then the following hidden fields should have the corresponding values:
     */
    public function theFollowingHiddenFieldsShouldHaveTheCorrespondingValues(TableNode $fields)
    {
        foreach ($fields->getRowsHash() as $field => $value) {
            /** @var NodeElement $elementsFound */
            $elementsFound = $this->getSession()->getPage()->find('css', '#'.$field);

            if (empty($elementsFound)) {
                throw new \RuntimeException("Element $field not found");
            }
            if ($elementsFound->getAttribute('value') != $value) {
                throw new \RuntimeException("Element $field value not equal to $value");
            }
        }
    }

    /**
     * @Then the :test field should contain the :yearType year
     */
    public function fieldShouldContainYear($field, $yearType)
    {
        $currentYear = intval(date('Y'));

        if ('current' === $yearType) {
            $year = $currentYear;
        } elseif ('previous' === $yearType) {
            $year = $currentYear - 1;
        } elseif ('next' === $yearType) {
            $year = $currentYear + 1;
        } else {
            throw new \RuntimeException("Invalid year type \"$yearType\"");
        }

        $this->assertFieldContains($field, $year);
    }
}
