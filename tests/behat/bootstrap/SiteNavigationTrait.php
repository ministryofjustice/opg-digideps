<?php

namespace DigidepsBehat;

use Behat\Behat\Tester\Exception\PendingException;

trait SiteNavigationTrait
{
    /**
     * @Given I am on admin page :path
     * @Given I go to admin page :path
     */
    public function iAmOnAdminPage($path)
    {
        $adminUrl = $this->getAdminUrl();
        $this->visitPath($adminUrl.$path);
    }
    
    /**
     * @Given I am on the feedback page
     * @Given I goto the feedback page
     * @And I select the feedback link
     */
    public function feedbackPage()
    {
        $this->visit('/feedback');
    }
    
    /**
     * @Given I am on the :report report overview page
     * @Given I go to the :report report overview page
     */
    public function iAmOnTheReport1Page($report)
    {
        $this->clickOnBehatLink('client-home');
        $this->clickOnBehatLink('report-' . $report);
    }
    
    /**
     * @Given I am on the accounts page of the :report report
     * @Given I go to the accounts page of the :report report
     */
    public function iAmOnTheReportAccountsPage($report)
    {
        $this->iAmOnTheReport1Page($report);
        $this->clickLink('edit-accounts');
    }
    
    /**
     * @Given I am on the account :accountNumber page of the :report report
     * @Given I go to the account :accountNumber page of the :report report
     */
    public function iAmOnTheReport1AccountPageByAccNumber($accountNumber, $report)
    {
        $this->iAmOnTheReportAccountsPage($report);
        $this->clickOnBehatLink('account-' . $accountNumber);
    }
    
    /**
     * @Given I goto the terms page
     */
    public function iGotoTheTermsPage()
    {
        $this->visit('/terms');
    }

    /**
     * Presses button with specified id|name|title|alt|value.
     *
     * @When /^(?:|I )press "(?P<button>(?:[^"]|\\")*)"$/
     */
    public function pressButton($button)
    {
        $this->scrollTo($button);
        $this->getSession()->getPage()->pressButton($button);
    }

    /**
     * Clicks link with specified id|title|alt|text.
     *
     * @When /^(?:|I )follow "(?P<link>(?:[^"]|\\")*)"$/
     */
    public function clickLink($link)
    {
        $link = $this->fixStepArgument($link);
        $this->scrollTo($link);
        $this->getSession()->getPage()->clickLink($link);
    }

    /**
     * @Given /^I tab to the next field$/
     */
    public function iTabToTheNextField()
    {
        $driver = $this->getSession()->getDriver();
        if (get_class($driver) == 'Behat\Mink\Driver\Selenium2Driver') {

            $javascript =
                "var currentField = $(':focus');"
                . "var fields = currentField.closest('form').find('input:visible');"
                . "fields.each(function (index,item) {"
                . "  if (item.id === currentField.attr('id')) {"
                . "    $(fields[index+1]).focus();"
                . "    currentField.trigger('blur');"
                . "  }"
                . "});";

            $this->getSession()->executeScript($javascript);
        
        }
        
    }

    /**
     * @Given /^I scroll to "add\-account"$/
     */
    public function scrollTo($element) {

        if (substr($element,0,1) != '.' && substr($element,0,1) != '#') {
            $element = '#' . $element;
        }
        
        $driver = $this->getSession()->getDriver();
        if (get_class($driver) == 'Behat\Mink\Driver\Selenium2Driver') {
            $javascript =
                "var el = $('$element');"
                . "var elOffset = el.offset().top;"
                . "var elHeight = el.height();"
                . "var windowHeight = $(window).height();"
                . "var offset;"
                . "if (elHeight < windowHeight) {"
                . "  offset = elOffset - ((windowHeight / 2) - (elHeight / 2));"
                . "} else {"
                . "  offset = elOffset;"
                . "}"
                . "window.scrollTo(0, offset);";
            
            $this->getSession()->executeScript($javascript);

        }
    }

}
