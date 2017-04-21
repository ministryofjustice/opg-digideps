<?php

namespace DigidepsBehat;

trait PaTrait
{
    /**
     * @BeforeFeature @pateam
     */
    public static function setUpPaTeamFeature()
    {
        echo "Run before PA Team feature";
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