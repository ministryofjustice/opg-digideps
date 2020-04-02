<?php declare(strict_types=1);

namespace DigidepsBehat\CompletingReports;

trait CompletingReportsTrait
{

    /**
     * @When deputy :email submits their completed report
     */
    public function aDeputyWithEmailSubmitsTheirCompletedReport(string $email)
    {
        $this->iAmLoggedInAsWithPassword($email, 'Abcd1234');
        $this->clickLink('Continue');
        $this->clickLink('Preview and check report');
        $this->clickLink('Continue');
        $this->checkOption('report_declaration_agree');
        $this->selectOption('I am the only deputy', 'only_deputy');
        $this->pressButton('Submit report');
    }

    /**
     * @When I click :cssSelector
     */
    public function iClick(string $cssSelector)
    {
        $element = $this->getSession()->getPage()->find('css', $cssSelector);
        if (!$element) {
            throw new Exception($cssSelector . " could not be found");
        } else {
            $element->click();
        }
    }
}
