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
        for ($i = 1; $i < 100; ++$i) {
            $iPadded = str_pad($i, 2, '0', STR_PAD_LEFT);
            $filename = $feature
                       ? '/tmp/behat/behat-response-'.$feature.'-'.$iPadded.'.html'
                       : '/tmp/behat/behat-response-'.$iPadded.'.html';
            if (!file_exists($filename)) {
                break;
            }
        }
        $session = $this->getSession();
        $data = $session->getPage()->getContent();
        $bytes = file_put_contents($filename, $data);
        echo '- Url: '.$session->getCurrentUrl()."\n";
        //echo "- Status code: " . $session->getStatusCode() . "\n";
        echo "- Response: saved into $filename ($bytes bytes).\n";
        //echo "- Page content: [".$data . ']';
    }

    /**
     * @Then I save the page as :name
     */
    public function iSaveThePageAs($name)
    {
        $filename = '/tmp/behat/behat-screenshot-'.$name.'.html';

        $data = $this->getSession()->getPage()->getContent();
        if (!file_put_contents($filename, $data)) {
            echo "Cannot write screenshot into $filename \n";
        }

        $driver = $this->getSession()->getDriver();
        $filename = '/tmp/behat/'.$name.'.png';
        if (get_class($driver) == 'Behat\Mink\Driver\Selenium2Driver') {
            $image_data = $this->getSession()->getDriver()->getScreenshot();
            if (!file_put_contents($filename, $image_data)) {
                echo "Cannot write screenshot into $filename \n";
            }
        }
    }

    /**
     * Call debug() when an exception is thrown after as step.
     *
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
     * @Then exit :code
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

    /**
     * @Given I clear my cookies
     */
    public function clearCookies()
    {
        $this->getSession()->restart();
    }

    /**
     * @Then the :elementId element has a height between :minSize px and :maxSize px
     */
    public function theElementHasAHeightBetweenPxAndPx($elementId, $minSize, $maxSize)
    {
        $element = $this->getSession()->getPage()->find('css', '#'.$elementId);

        if ($element) {
            $javascipt = "return $('#".$elementId."').height()";
            $height = $this->getSession()->evaluateScript($javascipt);

            if ($height < $minSize || $height > $maxSize) {
                throw new \RuntimeException('Element height out of range: '.$height);
            }
        } else {
            throw new \RuntimeException('Element not found');
        }
    }

    /**
     * @Then the :elementPath element has a height greater than :minSize px
     */
    public function theElementHasAHeightGreaterThanPx($elementId, $minSize)
    {
        $element = $this->getSession()->getPage()->find('css', '#'.$elementId);

        if ($element) {
            $javascipt = "return $('#".$elementId."').height()";
            $height = $this->getSession()->evaluateScript($javascipt);

            if ($height < $minSize) {
                throw new \RuntimeException('Element height out of range: '.$height);
            }
        } else {
            throw new \RuntimeException('Element not found');
        }
    }
}
