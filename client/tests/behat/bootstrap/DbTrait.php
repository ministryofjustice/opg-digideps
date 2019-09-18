<?php

namespace DigidepsBehat;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

trait DbTrait
{
    /**
     * @Then I save the application status into :status
     */
    public static function iSaveTheApplicationStatusInto($status)
    {
        $sqlFile = self::getSnapshotPath($status);
        // truncate cascade + insert. faster than drop + table recreate
        exec('echo "SET client_min_messages TO WARNING; truncate dd_user, dd_team, organisation, casrec, setting, user_team, client cascade;" > ' . $sqlFile);
        exec('pg_dump ' . self::$dbName . "  --data-only  --inserts --exclude-table='migrations' | sed '/EXTENSION/d' >> {$sqlFile}", $output, $return);
        if (!file_exists($sqlFile) || filesize($sqlFile) < 100) {
            throw new \RuntimeException("SQL snapshot $sqlFile not created or not valid");
        }
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
        exec('psql ' . self::$dbName . " --quiet < {$sqlFile}");
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
     * @Given I reset the behat SQL snapshots
     */
    public function deleteBehatSnapshots()
    {
        foreach (glob('/tmp/behat/behat-snapshot-*.sql') as $file) { // iterate files
          if (is_file($file)) {
              unlink($file);
          }
        }
    }

    /**
     * @BeforeScenario
     */
    public function dbSnapshotBeforeScenario(BeforeScenarioScope $scope)
    {
        if (!self::$autoDbSnapshot) {
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
        if (!self::$autoDbSnapshot) {
            return;
        }

        $snapshotName = preg_replace('/([^a-z0-9])/i', '-', $scope->getScenario()->getTitle())
                        . '-after-auto';

        self::iSaveTheApplicationStatusInto($snapshotName);
    }

    public function dbQueryRaw($table, array $fields)
    {
        if (!$fields) {
            throw new \InvalidArgumentException(__METHOD__ . ' array with at least one element expected');
        }
        $columns = join(',', array_keys($fields));
        $values = "'" . join("', '", array_values($fields)) . "'";
        $query = sprintf("INSERT INTO {$table} ({$columns}) VALUES({$values})");
        $command = sprintf('psql %s -c "%s"', self::$dbName, $query);
        exec($command);
    }

    /**
     * @Then I delete the :setting app setting
     */
    public function IDeleteTheAppSetting($setting)
    {
        $query = "DELETE FROM setting where id='{$setting}'";
        $command = sprintf('psql %s -c "%s"', self::$dbName, $query);
        exec($command);
    }

    /**
     * @Given I discharge the deputies from case :caseNumber
     */
    public function iDischargeTheDeputiesFromCase($caseNumber)
    {
        $query = "UPDATE client SET deleted_at = '2018-07-24 14:03:00' WHERE case_number = '{$caseNumber}'";
        $command = sprintf('psql %s -c "%s"', self::$dbName, $query);
        exec($command);

    }
}
