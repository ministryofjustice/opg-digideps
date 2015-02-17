<?php

namespace DigidepsBehat;

/**
 * @method ApplicationBehatHelper getApplicationBehatHelper()
 */
trait LogTrait
{

    /**
     * @Given I clear the log
     */
    public function clearLog()
    {
        self::getApplicationBehatHelper()->clearAppLog();
    }

    /**
     * @Then the log should contain :str
     */
    public function theLogShouldContain($str)
    {
        throw new Exception('TO IMPLEMENT');
        $logContent = self::getApplicationBehatHelper()->getLogContent();
        if (strpos($logContent, $str) === false) {
            throw new \Exception("Log does not contain '$str'");
        }
    }

}