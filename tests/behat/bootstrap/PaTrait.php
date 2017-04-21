<?php

namespace DigidepsBehat;

trait PaTrait
{
    /**
     * @BeforeFeature @pateam
     */
    public static function setUpPaTeamFeature()
    {
        echo "Run before PA Team feature\n";
        echo getcwd() . "\n";
        echo exec("ls");

        $command = sprintf('psql %s -f "%s"', self::$dbName, "tests/behat/sql/pa/pateam.sql");
        echo $command;
        #echo exec(sprintf('psql %s -f "%s"', self::$dbName, "tests/behat/sql/pa/pateam.sql"));
        echo exec($command);
        #echo exec($command);

        /*$dbconn = pg_connect("host=localhost dbname=api user=api password=api") or die("Could not connect: " . pg_last_error());

        $existingUser = pg_query($dbconn, "SELECT id FROM dd_user") or die('Query failed: ' . pg_last_error());
        if(pg_num_rows($existingUser) === 0)
        {
            //Delete everything
            echo "User exists. Deleting";
        }
        else
        {
            echo "Creating user";
        }*/
    }

    /**
     * @AfterFeature @pateam
     */
    public static function tearDownPaTeamFeature()
    {
        echo "Run after PA Team feature";
    }

    /**
     * @BeforeScenario @pateam
     */
    public function setUpPaTeamScenario()
    {
        echo "Run before PA Team scenario";
    }

    /**
     * @AfterScenario @pateam
     */
    public function tearDownPaTeamScenario()
    {
        echo "Run after PA Team scenario";
    }
}