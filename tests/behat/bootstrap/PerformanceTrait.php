<?php

namespace DigidepsBehat;

use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\AfterFeatureScope;

trait PerformanceTrait
{
    private static $data = [];

    private static $filePath = '/app/tests/behat-results.csv';

    /**
     * @BeforeFeature
     */
    public static function performanceFeatureStart(BeforeFeatureScope $scope)
    {
        $feature = self::getFeatureScenarioLineFromScope($scope);

        self::$data[$feature] = microtime(1);
    }

    /**
     * @AfterFeature
     */
    public static function performanceFeatureStop(AfterFeatureScope $scope)
    {
        $feature = self::getFeatureScenarioLineFromScope($scope);

        $diff = round(microtime(1) - self::$data[$feature], 3);
        self::$data[$feature] = $diff;

        file_put_contents(self::$filePath, "{$feature},{$diff}\n", FILE_APPEND);
    }

    /**
     * @return array
     */
    private static function getFeatureScenarioLineFromScope($scope)
    {
        $file = $scope->getFeature()->getFile();
        $expectedPrefix = '/behat/features/';
        $feature = substr($file, strpos($file, $expectedPrefix) + strlen($expectedPrefix));

        return $feature;
    }
}
