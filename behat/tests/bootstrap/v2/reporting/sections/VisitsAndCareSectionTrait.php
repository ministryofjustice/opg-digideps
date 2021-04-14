<?php declare(strict_types=1);

namespace DigidepsBehat\v2\Reporting\Sections;

trait VisitsAndCareSectionTrait
{
    /**
     * @Given I view visits and care section
     */
    public function iViewVisitsAndCareSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $activeReportId, 'visits-care');
        $this->visitPath($reportSectionUrl);
    }

    /**
     * @Given I view and start visits and care section
     */
    public function iViewAndStartVisitsAndCareSection()
    {
        $this->iViewVisitsAndCareSection();
        $this->clickLink('Start visits and care');
    }

    /**
     * @When I enter that I do not live with client
     */
    public function iDoNotLiveWithClientVisitsAndCareSection()
    {
//        $this->getSession()->getDriver()->manage()->window()->maximize();

//        $xpath = sprintf("//%s[@%s='%s']//input", 'div', 'data-module', 'govuk-radios');
//        $session = $this->getSession();
//        $value = $session->getPage()->find(
//            'xpath',
//            $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath)
//        );
//        $this->getSession()->wait(60, '(0 === jQuery.active)');
        $this->pressButton('Accept cookies');
        $value = '#how-often-contact-client-wrapper';
        $javascript =
            "var el = $('$value');"
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

        $this->iSelectRadioBasedOnName('div', 'data-module', 'govuk-radios', 'no');
//        var_dump($this->getCurrentUrl());
        //        var_dump($this->getSession()->getPage()->getHtml());
        assert($this->elementExistsOnPage('textarea', 'id', 'visits_care_howOftenDoYouContactClient'));
        $this->pressButton('Save and continue');
        var_dump($this->getCurrentUrl());
    }
}
