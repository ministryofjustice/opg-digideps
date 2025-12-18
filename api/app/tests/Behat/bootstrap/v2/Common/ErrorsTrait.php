<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use App\Tests\Behat\BehatException;

trait ErrorsTrait
{
    private function errorLocations()
    {
        $errorDiv = $this->getSession()->getPage()->find('css', 'div#error-summary');
        $flashDiv = $this->getSession()->getPage()->find('css', 'div.opg-alert--error');

        return ['errorDiv' => $errorDiv, 'flashDiv' => $flashDiv];
    }

    public function assertOnErrorMessage(string $errorMessage)
    {
        $errors = $this->errorLocations();

        if (is_null($errors['errorDiv']) || is_null($errors['flashDiv'])) {
            $missingDivMessage = <<<MESSAGE
            A div with the id error-summary or class opg-alert--error was not found.
            This suggests one of the following:

            - an error was not triggered
            - the form error appears in a non-GDS error element
            - the flash error element is not using macro.notification.
            MESSAGE;

            throw new BehatException($missingDivMessage);
        }

        $errorHtml = $errors['errorDiv'] ? $errors['errorDiv']->getHtml() : $errors['flashDiv']->getHtml();
        $errorMessageFound = str_contains($errorHtml, $errorMessage);

        if (!$errorMessageFound) {
            throw new BehatException(sprintf('The error summary did not contain the expected error message. Expected: "%s", got (full HTML): %s', $errorMessage, $errorHtml));
        }
    }

    /**
     * @Then I should see no errors
     */
    public function assertNoErrorMessages()
    {
        $errors = $this->errorLocations();

        if (!is_null($errors['errorDiv']) || !is_null($errors['flashDiv'])) {
            $errorHtml = $errors['errorDiv'] ? $errors['errorDiv']->getHtml() : $errors['flashDiv']->getHtml();
            throw new BehatException(sprintf('Error message displayed: %s', $errorHtml));
        }
    }
}
