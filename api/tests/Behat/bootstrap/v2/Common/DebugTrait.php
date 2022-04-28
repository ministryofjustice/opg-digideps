<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Tester\Result\ExecutedStepResult;

trait DebugTrait
{
    private static $DEBUG_SNAPSHOT_DIR = '/tmp/html';

    /**
     * @Then /^wtf$/
     */
    public function wtf()
    {
        $this->printLastResponse();
    }

    /**
     * Call debug() when an exception is thrown after a step.
     *
     * @AfterStep
     */
    public function debugOnException(AfterStepScope $scope)
    {
        if (($result = $scope->getTestResult())
            && $result instanceof ExecutedStepResult
            && $result->hasException()
        ) {
            $feature = basename($scope->getFeature()->getFile());
            $this->debug($feature);
        }
    }

    /**
     * @Then I save the page as :name
     */
    public function debug($name)
    {
        for ($i = 1; $i < 100; ++$i) {
            $iPadded = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $filename = self::$DEBUG_SNAPSHOT_DIR.'/behat-response-'.$name.'-'.$iPadded.'.html';
            if (!file_exists($filename)) {
                break;
            }
        }

        $session = $this->getSession();

        $pageContent = $session->getPage()->getContent();
        $data = str_replace('"/assets', '"https://digideps.local/assets', $pageContent);

        $bytes = file_put_contents($filename, $data);
        $file = basename($filename);

        $loggedInEmail = !isset($this->loggedInUserDetails) ? 'Not logged in' : $this->loggedInUserDetails->getUserEmail();
        $interactingWithEmail = !isset($this->interactingWithUserDetails) ? 'Not interacting with a user' : $this->interactingWithUserDetails->getUserEmail();
        $currentUrl = $session->getCurrentUrl();

        $message = <<<CONTEXT
Logged in user: $loggedInEmail
Interacting with user: $interactingWithEmail
Test run ID: $this->testRunId
Current URL: $currentUrl

Response saved ($bytes bytes):
$file
CONTEXT;

        echo $message;
    }
}
