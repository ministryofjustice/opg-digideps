<?php

namespace App\Tests\Behat;

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
        exec('echo "SET client_min_messages TO WARNING; truncate dd_user, satisfaction, deputy, organisation, pre_registration, setting, client cascade;" > '.$sqlFile);
        exec('pg_dump '.self::$dbName."  --data-only  --inserts --exclude-table='migrations' | sed '/EXTENSION/d' >> {$sqlFile}", $output, $return);
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

        exec(sprintf('psql %s --quiet < %s', self::$dbName, $sqlFile));
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private static function getSnapshotPath($name)
    {
        return '/tmp/sql/behat-snapshot-'
                .strtolower(preg_replace('/[^\w]+/', '-', $name))
                .'.sql';
    }

    /**
     * @Given I reset the behat SQL snapshots
     */
    public function deleteBehatSnapshots()
    {
        foreach (glob('/tmp/sql/behat-snapshot-*.sql') as $file) { // iterate files
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
                        .'-before-auto';

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
                        .'-after-auto';

        self::iSaveTheApplicationStatusInto($snapshotName);
    }

    public function dbQueryRaw($table, array $fields)
    {
        if (!$fields) {
            throw new \InvalidArgumentException(__METHOD__.' array with at least one element expected');
        }
        $columns = join(',', array_keys($fields));
        $values = "'".join("', '", array_values($fields))."'";
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

    /**
     * @Given I add the client with case number :caseNumber to be deputised by email :deputyEmail
     */
    public function iAddTheClientWithCaseNumberToBeDeputisedByEmail($caseNumber, $deputyEmail)
    {
        $query = "INSERT INTO deputy_case (client_id, user_id) VALUES (
                    (SELECT id from client where case_number = '".$caseNumber."'),
                    (SELECT id from dd_user where email = '".$deputyEmail."')
                  )";
        $command = sprintf('psql %s -c "%s"', self::$dbName, $query);
        exec($command);
    }

    /**
     * @Given the organisation :organisationEmailIdentifier is active
     */
    public function theOrganisationIsActive($organisationEmailIdentifier)
    {
        $query = "UPDATE organisation SET is_activated = true WHERE email_identifier = '{$organisationEmailIdentifier}'";
        $command = sprintf('psql %s -c "%s"', self::$dbName, $query);
        exec($command);
    }

    /**
     * @Given the organisation :organisationEmailIdentifier is inactive
     */
    public function theOrganisationIsInactive($organisationEmailIdentifier)
    {
        $query = "UPDATE organisation SET is_activated = false WHERE email_identifier = '{$organisationEmailIdentifier}'";
        $command = sprintf('psql %s -c "%s"', self::$dbName, $query);
        exec($command);
    }

    /**
     * @Given :userEmail has been added to the :organisationEmailIdentifier organisation
     */
    public function hasBeenAddedToTheOrganisation($userEmail, $organisationEmailIdentifier)
    {
        $query = "INSERT INTO organisation_user (user_id, organisation_id) VALUES
          (
            (SELECT id FROM dd_user WHERE email = '{$userEmail}'),
            (SELECT id FROM organisation WHERE email_identifier = '{$organisationEmailIdentifier}')
          ) ON CONFLICT DO NOTHING;";
        $command = sprintf('psql %s -c "%s"', self::$dbName, $query);
        exec($command);
    }

    /**
     * @Given :userEmail has been removed from the :organisationEmailIdentifier organisation
     */
    public function hasBeenRemovedFromTheOrganisation($userEmail, $organisationEmailIdentifier)
    {
        $query = "DELETE FROM organisation_user WHERE organisation_id =
                    (SELECT id FROM organisation WHERE email_identifier = '{$organisationEmailIdentifier}')
                    AND user_id = (SELECT id FROM dd_user WHERE email = '{$userEmail}')";
        $command = sprintf('psql %s -c "%s"', self::$dbName, $query);
        exec($command);
    }

    /**
     * @Given :userEmail has been removed from their organisation
     */
    public function hasBeenRemovedFromTheirOrganisation($userEmail)
    {
        $query = "DELETE FROM organisation_user WHERE user_id =
          (
            SELECT id FROM dd_user WHERE email = '{$userEmail}'
          )";
        $command = sprintf('psql %s -c "%s"', self::$dbName, $query);
        exec($command);
    }
}
