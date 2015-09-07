<?php

namespace DigidepsBehat;

use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

trait StatusSnapshotTrait
{

    /**
     * @Then I save the application status into :status
     */
    public static function iSaveTheApplicationStatusInto($status)
    {
        $sqlFile = self::getSnapshotPath($status);
        exec("pg_dump " . self::$dbName . " --clean > {$sqlFile}");
    }

    /**
     * @Then I load the application status from :status
     */
    public static function iLoadtheApplicationStatusFrom($status)
    {
        $sqlFile = self::getSnapshotPath($status);
        if (!file_exists($sqlFile)) {
            $error = "File $sqlFile not found. Re-run the full behat suite to recreate the missing snapshots.";
            throw new \RuntimeException($error);
        }
        exec("psql " . self::$dbName . " < {$sqlFile}");
    }

    /**
     * @param string $name
     * 
     * @return string
     */
    private static function getSnapshotPath($name)
    {
        return '/tmp/behat/behat-snapshot-'
                . strtolower(preg_replace('/[^\w]+/', '-', $name))
                . '.sql';
    }
    
    /**
     * @BeforeScenario
     */
    public function dbSnapshotBeforeScenario(BeforeScenarioScope $scope)
    {
        if (!self::$saveSnaphotBeforeEachScenario) {
            return;
        }
        
        $snapshotName = preg_replace('/([^a-z0-9])/i', '-', $scope->getScenario()->getTitle()) 
                        . '-before-auto';
        
        self::iSaveTheApplicationStatusInto($snapshotName);
    }
    
    /**
     * @AfterScenario
     */
    public function dbSnapshotAFterScenario(AfterScenarioScope $scope)
    {
        if (!self::$saveSnaphotAfterEachScenario) {
            return;
        }
        
        $snapshotName = preg_replace('/([^a-z0-9])/i', '-', $scope->getScenario()->getTitle()) 
                        . '-after-auto';
        
        self::iSaveTheApplicationStatusInto($snapshotName);
    }

}