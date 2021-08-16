<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\ContactDetails;

use App\Tests\Behat\BehatException;

trait ContactDetailsTrait
{
    /**
     * @Then the support footer should show :text
     */
    public function supportFooterShouldShowEmail(string $text)
    {
        $supportFooter = $this->getSession()->getPage()->find('xpath', '//main/../details');

        if (is_null($supportFooter)) {
            throw new BehatException('A details element was not visible on the page');
        }

        $textVisible = str_contains($supportFooter->getHtml(), $text);

        if (!$textVisible) {
            throw new BehatException(sprintf('Details element did not contain the expecting text "%s"', $text));
        }
    }

    /**
     * @Then the support footer should not be visible
     */
    public function supportFooterShouldNotBeVisible()
    {
        $supportFooter = $this->getSession()->getPage()->find('xpath', '//div/details');

        if (!is_null($supportFooter)) {
            throw new BehatException('A details element was found on the page when it should not be visible');
        }
    }
}
