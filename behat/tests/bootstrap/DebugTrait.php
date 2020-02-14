<?php

namespace DigidepsBehat;

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
     * Clean the snapshot folder before running a suite
     *
     * @BeforeSuite
     */
    public static function cleanDebugSnapshots()
    {
        $handle = opendir(self::$DEBUG_SNAPSHOT_DIR);

        while (false !== ($file = readdir($handle))) {
            $path = self::$DEBUG_SNAPSHOT_DIR . '/' . $file;
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
            $this->debug($feature);
        }
    }
}
