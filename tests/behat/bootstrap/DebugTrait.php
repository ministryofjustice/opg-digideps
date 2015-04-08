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
        if (!is_writable($filename)) {
            echo "$filename not writeable\n";
        }
        file_put_contents($filename, $data);
        #exec("firefox $filename 2>&1");
        echo "Open $filename to debug last response.\n";
    }
    
    
    
    /**
     * @Then I save the page as :name
     */
    public function iSaveThePageAs($name)
    {
        $filename = 'misc/tmp/behat-screenshot-' . $name . '.html';
            
        $data = $this->getSession()->getPage()->getContent();
        if (!file_put_contents($filename, $data)) {
            echo "Cannot write screenshot into $filename \n";
        }
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
     * @Then die :code
     */
    public function interrupt($code)
    {
        die($code);
    }
    
     /**
     * @Then fail
     */
    public function fail()
    {
        throw new \RuntimeException('manual fail');
    }

}