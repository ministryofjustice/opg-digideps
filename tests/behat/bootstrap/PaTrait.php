<?php

namespace DigidepsBehat;

trait PaTrait
{
    /**
     * @BeforeFeature @pateam
     */
    public static function setUpPaTeamFeature()
    {
        tearDownPaTeamFeature();

        echo "Adding PA Team data\n";

        //TODO: Remove hard coded path
        //TODO: Investigate automatically running these scripts based on tag?
        $command = sprintf('psql %s -f "%s"', self::$dbName, "tests/behat/sql/pa/pateam-setup.sql");
        exec($command);
    }

    /**
     * @AfterFeature @pa @pateam
     */
    public static function tearDownPaTeamFeature()
    {
        echo "Clearing PA data";

        //TODO: Remove hard coded path
        $command = sprintf('psql %s -f "%s"', self::$dbName, "tests/behat/sql/pa/pa-teardown.sql");
        exec($command);
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