<?php

namespace DigidepsBehat\Common;

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
     * @Then I save the page as :name
     */
    public function debug($name)
    {
        for ($i = 1; $i < 100; ++$i) {
            $iPadded = str_pad($i, 2, '0', STR_PAD_LEFT);
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

        echo "** Test failed **\n";
        echo 'Url: '.$session->getCurrentUrl()."\n";
        echo "Response saved ({$bytes} bytes):\n";
        echo "$file";
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
