<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Mockery\Exception;

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
     * Clean the snapshot folder before running a suite.
     *
     * @BeforeSuite
     */
    public static function cleanDebugSnapshots()
    {
        $handle = opendir(self::$DEBUG_SNAPSHOT_DIR);

        while (false !== ($file = readdir($handle))) {
            $path = self::$DEBUG_SNAPSHOT_DIR.'/'.$file;
            if (is_file($path)) {
                unlink($path);
            }
        }
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
            $this->debug($feature, $result->getException());
        }
    }

    /**
     * @Then I save the page as :name
     */
    public function debug(string $name, \Exception $ex)
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
EXCEPTION: {$ex->getMessage()}
@ FILE: {$ex->getFile()}; LINE: {$ex->getLine()}
TRACE: {$ex->getTraceAsString()}

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
