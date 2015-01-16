<?php

namespace DigidepsBehat;

trait DebugTrait
{

    /**
     * @Then /^wtf$/
     */
    public function wtf()
    {
        $this->printLastResponse();
    }

    /**
     * @Then /^debug$/
     */
    public function debug($feature = null, $line = null)
    {
        for ($i=1; $i<100; $i++) {
            $iPadded = str_pad($i, 2, '0', STR_PAD_LEFT);
            $filename = $feature
                       ? 'misc/tmp/behat-response-' . $feature . '-' . $iPadded . '.html' 
                       : 'misc/tmp/behat-response-' . $iPadded . '.html';
            if (!file_exists($filename)) {
                break;
            }
        }
        $data = $this->getSession()->getPage()->getContent();
        if (!file_put_contents($filename, $data)) {
            echo "Cannot write response into $filename \n";
        }
        #exec("firefox $filename 2>&1");
        echo "Open $filename to debug last response.\n";
    }
    
    /**
     * Call debug() when an exception is thrown after as tep
     * @AfterStep
     */
    public function debugOnException(\Behat\Behat\Hook\Scope\AfterStepScope $scope)
    {
        if (($result = $scope->getTestResult())
            && $result instanceof \Behat\Behat\Tester\Result\ExecutedStepResult   
            && $result->hasException()
        ) {
            $feature = basename($scope->getFeature()->getFile());
            $line = $scope->getFeature()->getLine();
            $this->debug($feature, $line);
        }
    }

    /**
     * @Then die
     */
    public function interrupt()
    {
        die(1);
    }
    
     /**
     * @Then fail
     */
    public function fail()
    {
        throw new \RuntimeException('manual fail');
    }

}