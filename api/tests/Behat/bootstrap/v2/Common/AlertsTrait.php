<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use App\Tests\Behat\BehatException;

trait AlertsTrait
{
    public function assertOnAlertMessage(string $alertMessage)
    {
        $xpath = '//div[contains(@class, "opg-alert__message")]|//div[contains(@class, "opg-alert--info")]|//div[contains(@class, "govuk-error-summary")]';
        $alertDiv = $this->getSession()->getPage()->find('xpath', $xpath);

        if (is_null($alertDiv)) {
            throw new BehatException('Could not find a div with class "opg-alert__message", "opg-alert--info" or "govuk-error-summary"');
        }

        $alertHtml = $alertDiv->getHtml();
        $alertMessageFound = str_contains($alertHtml, $alertMessage);

        if (!$alertMessageFound) {
            throw new BehatException(sprintf('The alert element did not contain the expected message. Expected: "%s", got (full HTML): %s', $alertMessage, $alertHtml));
        }
    }
}
