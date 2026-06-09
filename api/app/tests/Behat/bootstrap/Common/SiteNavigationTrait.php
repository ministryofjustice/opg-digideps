<?php

namespace Tests\OPG\Digideps\Backend\Behat\Common;

trait SiteNavigationTrait
{
    /**
     * @Given I am on admin page :path
     * @Given I go to admin page :path
     */
    public function visitAdminPath($path): void
    {
        $adminUrl = $this->getAdminUrl();
        $this->visitPath($adminUrl . $path);
    }

    /**
     * @Given /^I scroll to "add\-account"$/
     */
    public function scrollTo($element): void
    {
        if (substr($element, 0, 1) != '.' && substr($element, 0, 1) != '#') {
            $element = '#' . $element;
        }

        $driver = $this->getSession()->getDriver();
        if (get_class($driver) == 'Behat\Mink\Driver\Selenium2Driver') {
            $javascript =
                "var el = $('$element');"
                . 'var elOffset = el.offset().top;'
                . 'var elHeight = el.height();'
                . 'var windowHeight = $(window).height();'
                . 'var offset;'
                . 'if (elHeight < windowHeight) {'
                . '  offset = elOffset - ((windowHeight / 2) - (elHeight / 2));'
                . '} else {'
                . '  offset = elOffset;'
                . '}'
                . 'window.scrollTo(0, offset);';

            $this->getSession()->executeScript($javascript);
        }
    }

    /**
     * @When I open the :period checklist for client :caseNumber
     */
    public function iOpenChecklistForClient($period, $caseNumber): void
    {
        $this->visitAdminPath("/admin/client/case-number/$caseNumber/details");
        $this->clickLinkInsideElement('checklist', 'report-' . $period);
    }

    /**
     * @When I visit the client page for :caseNumber
     */
    public function iVisitClientPageOnAdmin($caseNumber): void
    {
        $this->visitAdminPath("/admin/client/case-number/$caseNumber/details");
    }
}
