<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use App\Tests\Behat\BehatException;

trait ErrorsTrait
{
    public function assertOnErrorMessage(string $errorMessage)
    {
        $errorDiv = $this->getSession()->getPage()->find('css', 'div#error-summary');
        $flashDiv = $this->getSession()->getPage()->find('css', 'div.opg-alert--error');

        if (is_null($errorDiv) && is_null($flashDiv)) {
            $missingDivMessage = <<<MESSAGE
A div with the id error-summary or class opg-alert--error was not found.
This suggests one of the following:

- an error was not triggered
- the form error appears in a non-GDS error element
- the flash error element is not using macro.notification.
MESSAGE;

            throw new BehatException($missingDivMessage);
        }

        $errorHtml = $errorDiv ? $errorDiv->getHtml() : $flashDiv->getHtml();
        $errorMessageFound = str_contains($errorHtml, $errorMessage);

        if (!$errorMessageFound) {
            throw new BehatException(sprintf('The error summary did not contain the expected error message. Expected: "%s", got (full HTML): %s', $errorMessage, $errorHtml));
        }
    }
}
