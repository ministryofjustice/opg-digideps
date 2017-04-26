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

        //TODO: Investigate automatically running these scripts based on tag?
        $command = sprintf('sh scripts/dbScript.sh %s "%s%s"', self::$dbName, self::$sqlPath, "pa/pateam-setup.sql");
        exec($command);
    }

    /**
     * @AfterFeature @pa
     */
    public static function tearDownPaFeature()
    {
        echo "Clearing PA data\n";

        $command = sprintf('sh scripts/dbScript.sh %s "%s%s"', self::$dbName, self::$sqlPath, "pa/pa-teardown.sql");
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