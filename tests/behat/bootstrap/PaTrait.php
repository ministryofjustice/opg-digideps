<?php

namespace DigidepsBehat;

trait PaTrait
{
    /**
     * @BeforeFeature @pa
     */
    public static function setUpPaFeature()
    {
        echo "Clearing PA data\n";

        $command = sprintf('sh scripts/dbScript.sh %s "%s%s"', self::$dbName, self::$sqlPath, "pa/pa-teardown.sql");
        exec($command);
    }

    /**
     * @BeforeFeature @pateam
     */
    public static function setUpPaTeamFeature()
    {

    }

    /**
     * @AfterFeature @pa
     */
    public static function tearDownPaFeature()
    {

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