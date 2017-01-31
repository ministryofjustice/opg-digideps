<?php

namespace DigidepsBehat;

trait SiteNavigationTrait
{
    /**
     * @Given I am on admin page :path
     * @Given I go to admin page :path
     */
    public function iAmOnAdminPage($path)
    {
        $adminUrl = $this->getAdminUrl();
        $this->visitPath($adminUrl . $path);
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
                . 'fields.each(function (index,item) {'
                . "  if (item.id === currentField.attr('id')) {"
                . '    $(fields[index+1]).focus();'
                . "    currentField.trigger('blur');"
                . '  }'
                . '});';

            $this->getSession()->executeScript($javascript);
        }
    }

    /**
     * @Given /^I scroll to "add\-account"$/
     */
    public function scrollTo($element)
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
}
