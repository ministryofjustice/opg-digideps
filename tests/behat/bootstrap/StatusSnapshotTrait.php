<?php

namespace DigidepsBehat;

use Behat\Testwork\Hook\Scope\BeforeSuiteScope;

/**
 * @method ApplicationBehatHelper getApplicationBehatHelper()
 */
trait StatusSnapshotTrait
{

    /**
     * @param string $param
     * 
     * @return string
     */
    private static function getDbConf($param)
    {
        throw new Exception('TO IMPLEMENT');
        return self::getApplicationBehatHelper()->getConfig()['doctrine']['connection']['orm_default']['params'][$param];
    }

    /**
     * @Then I save the application status into :status
     */
    public static function iSaveTheApplicationStatusInto($status)
    {
        throw new Exception('TO IMPLEMENT');
        $dbname = self::getDbConf('dbname');

        $sqlFile = self::getSnapshotPath($status);
        exec("sudo -u postgres pg_dump -U postgres {$dbname} --clean > {$sqlFile}");
    }

    /**
     * @Then I load the application status from :status
     */
    public static function iLoadtheApplicationStatusFrom($status)
    {
        throw new Exception('TO IMPLEMENT');
        $dbname = self::getDbConf('dbname');

        $sqlFile = self::getSnapshotPath($status);
        if (!file_exists($sqlFile)) {
            throw new \RuntimeException("File $sqlFile not found. Re-run the full behat suite to recreate the missing snapshots.");
        }
        //exec("sudo -u postgres psql -U postgres -d {$dbname} -c 'DROP SCHEMA IF EXISTS public cascade; CREATE SCHEMA IF NOT EXISTS public;'");
        exec("sudo -u postgres psql -U postgres {$dbname} < {$sqlFile}");
    }

    /**
     * @param string $name
     * 
     * @return string
     */
    private static function getSnapshotPath($name)
    {
        return getcwd()
                . '/misc/tmpbehat-snapshot-'
                . strtolower(preg_replace('/[^\w]+/', '-', $name))
                . '.sql';
    }

}