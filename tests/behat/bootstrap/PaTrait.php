<?php

namespace DigidepsBehat;

trait PaTrait
{
    /**
     * @BeforeFeature @pa
     */
    public static function setUpPaFeature()
    {
        self::tearDownPaFeature();
    }

    /**
     * @BeforeFeature @pateam
     */
    public static function setUpPaTeamFeature()
    {
        echo "Adding PA Team data\n";

        //TODO: Remove hard coded path
        //TODO: Investigate automatically running these scripts based on tag?
        $command = sprintf('psql %s -f "%s"', self::$dbName, "tests/behat/sql/pa/pateam-setup.sql");
        exec($command);
    }

    /**
     * @AfterFeature @pa
     */
    public static function tearDownPaFeature()
    {
        echo "Clearing PA data\n";

        //TODO: Remove hard coded path
        $command = sprintf('psql %s -f "%s"', self::$dbName, "tests/behat/sql/pa/pa-teardown.sql");
        exec($command);
    }

    /**
     * @BeforeScenario @pateam
     */
    public function setUpPaTeamScenario()
    {

    }

    /**
     * @AfterScenario @pateam
     */
    public function tearDownPaTeamScenario()
    {

    }
}