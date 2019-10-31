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
     * @Then I save the page as :feature
     */
    public function debug($feature = null)
    {
        for ($i = 1; $i < 100; ++$i) {
            $iPadded = str_pad($i, 2, '0', STR_PAD_LEFT);
            $filename = $feature
                       ? '/tmp/html/behat-response-' . $feature . '-' . $iPadded . '.html'
                       : '/tmp/html/behat-response-' . $iPadded . '.html';
            if (!file_exists($filename)) {
                break;
            }
        }
        $session = $this->getSession();

        $pageContent = $session->getPage()->getContent();
        $data = str_replace('"/assets', '"https://digideps.local/assets', $pageContent);

        $bytes = file_put_contents($filename, $data);
        echo '- Url: ' . $session->getCurrentUrl() . "\n";
        //echo "- Status code: " . $session->getStatusCode() . "\n";
        $file = basename($filename);
        echo "- Response: saved into {$file} ({$bytes} bytes). View locally at https://digideps.local/behat-debugger.php?frame=page&f={$file} .\n";
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
            $this->debug($feature);
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
        $element = $this->getSession()->getPage()->find('css', '#' . $elementId);

        if ($element) {
            $javascipt = "return $('#" . $elementId . "').height()";
            $height = $this->getSession()->evaluateScript($javascipt);

            if ($height < $minSize || $height > $maxSize) {
                throw new \RuntimeException('Element height out of range: ' . $height);
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
        $element = $this->getSession()->getPage()->find('css', '#' . $elementId);

        if ($element) {
            $javascipt = "return $('#" . $elementId . "').height()";
            $height = $this->getSession()->evaluateScript($javascipt);

            if ($height < $minSize) {
                throw new \RuntimeException('Element height out of range: ' . $height);
            }
        } else {
            throw new \RuntimeException('Element not found');
        }
    }
}
