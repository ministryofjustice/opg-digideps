<?php declare(strict_types=1);

namespace DigidepsBehat\Common;


use Exception;

trait ButtonTrait
{
    /**
     * @Then /^(?:|I )click (?:on |)(?:|the )"([^"]*)"(?:|.*) button$/
     * @param string $buttonText
     * @throws Exception
     */
    public function iClickOn(string $buttonText)
    {
        $element = $this->getSession()->getPage()->findButton($buttonText);
        if (!$element) {
            throw new Exception($buttonText . " could not be found");
        } else {
            $element->click();
        }
    }

    /**
     * @Then the button :cssSelector should be :status
     */
    public function theButtonShouldBeDisabled(string $cssSelector, string $status)
    {
        if (!in_array($status, ['enabled', 'disabled'])) {
            throw new Exception(sprintf("$status is not a valid option for \$status - valid options are 'enabled' or 'disabled"));
        }

        $element = $this->getSession()->getPage()->find("css", $cssSelector);
        if (!$element) {
            throw new Exception($cssSelector . " could not be found");
        } else {
            $status === 'disabled' ? $element->hasAttribute('disabled') : !$element->hasAttribute('disabled');
        }
    }
}
