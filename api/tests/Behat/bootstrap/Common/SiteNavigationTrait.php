<?php

namespace DigidepsBehat\Common;

trait SiteNavigationTrait
{
    /**
     * @Given I am on admin page :path
     * @Given I go to admin page :path
     */
    public function visitAdminPath($path)
    {
        $adminUrl = $this->getAdminUrl();
        $this->visitPath($adminUrl.$path);
    }

    /**
     * @Given /^I scroll to "add\-account"$/
     */
    public function scrollTo($element)
    {
        if ('.' != substr($element, 0, 1) && '#' != substr($element, 0, 1)) {
            $element = '#'.$element;
        }

        $driver = $this->getSession()->getDriver();
        if ('Behat\Mink\Driver\Selenium2Driver' == get_class($driver)) {
            $javascript =
                "var el = $('$element');"
                .'var elOffset = el.offset().top;'
                .'var elHeight = el.height();'
                .'var windowHeight = $(window).height();'
                .'var offset;'
                .'if (elHeight < windowHeight) {'
                .'  offset = elOffset - ((windowHeight / 2) - (elHeight / 2));'
                .'} else {'
                .'  offset = elOffset;'
                .'}'
                .'window.scrollTo(0, offset);';

            $this->getSession()->executeScript($javascript);
        }
    }

    /**
     * @When I open the :period checklist for client :caseNumber
     */
    public function iOpenChecklistForClient($period, $caseNumber)
    {
        $this->visitAdminPath("/admin/client/case-number/$caseNumber/details");
        $this->clickLinkInsideElement('checklist', 'report-'.$period);
    }

    /**
     * @When I visit the client page for :caseNumber
     */
    public function iVisitClientPageOnAdmin($caseNumber)
    {
        $this->visitAdminPath("/admin/client/case-number/$caseNumber/details");
    }
}
