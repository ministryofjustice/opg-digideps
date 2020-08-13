<?php

namespace DigidepsBehat;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use DigidepsBehat\Common\AuthenticationTrait;
use DigidepsBehat\Common\DebugTrait;
use DigidepsBehat\Common\FormTrait;
use DigidepsBehat\Common\LinksTrait;
use DigidepsBehat\Common\RegionTrait;
use DigidepsBehat\Common\ReportTrait;
use DigidepsBehat\Common\SiteNavigationTrait;

/**
 * Behat context class.
 *
 * when the alpha models are refactored and simplified, this class can be refactored and splitter around.
 * until then, better to keep things in the sample place for simplicity
 */
class FeatureContext extends MinkContext implements SnippetAcceptingContext
{
    use AuthenticationTrait,
        DebugTrait,
        DbTrait,
        CookieTrait,
        FileTrait,
        FormStepTrait,
        FormTrait,
        LinksTrait,
        LinksPreviouslySavedTrait,
        RegionTrait,
        ReportTrait,
        SiteNavigationTrait,
        UserTrait,
        SearchTrait;

    protected static $dbName = 'api';
    protected static $sqlPath = 'tests/behat/sql/';

    protected static $autoDbSnapshot = false;

    public function __construct($options = [])
    {
        //$options['session']; // not used
        $maxNestingLevel = isset($options['maxNestingLevel']) ? $options['maxNestingLevel'] : 200;
        ini_set('xdebug.max_nesting_level', $maxNestingLevel);
        ini_set('max_nesting_level', $maxNestingLevel);
        $this->sessionName = empty($options['sessionName']) ? 'digideps' : $options['sessionName'];
        self::$dbName = empty($options['dbName']) ? 'api' : $options['dbName'];
        // set this to true for temporary local debugging
    }

    /**
     * @return string
     */
    public function getAdminUrl()
    {
        return getenv('ADMIN_HOST');
    }

    /**
     * @return string
     */
    public function getSiteUrl()
    {
        return getenv('NONADMIN_HOST');
    }

    /**
     * @BeforeSuite
     */
    public static function prepare(\Behat\Testwork\Hook\Scope\BeforeSuiteScope $scope)
    {
        $suiteName = $scope->getSuite()->getName();
        echo "\n\n"
            . strtoupper($suiteName) . "\n"
            . str_repeat('=', strlen($suiteName)) . "\n"
            . $scope->getSuite()->getSetting('description') . "\n"
            . "\n";
    }

    /**
     * @Then the response should have the :arg1 header containing :arg2
     */
    public function theResponseShouldHaveTheHeaderContaining($header, $value)
    {
        $headers = array_change_key_case($this->getSession()->getDriver()->getResponseHeaders(), CASE_LOWER);
        if (empty($headers[strtolower($header)][0])) {
            throw new \Exception("Header '{$header}' not found.");
        }
        if (strpos($headers[strtolower($header)][0], $value) === false) {
            throw new \Exception("Header '{$header}' has value '{$headers[$header][0]}' that does not contains '{$value}'");
        }
    }

    /**
     * @Given the :area area works properly
     */
    public function iChecktheAppParameterFile($area)
    {
        $baseUrl = $this->getAreaUrl($area);

        $this->visitPath($baseUrl . '/manage/availability');
        $this->assertResponseStatus(200);
    }

    /**
     * @Given I should be in the :area area
     */
    public function iShouldBeInTheArea($area)
    {
        $baseUrl = $this->getAreaUrl($area);

        $currentUrl = $this->getSession()->getCurrentUrl();
        if (substr($currentUrl, 0, strlen($baseUrl)) !== $baseUrl) {
            throw new \RuntimeException("$currentUrl does not start with $baseUrl");
        }
    }

    private function getAreaUrl($area)
    {
        if ($area === 'deputy') {
            return $this->getSiteUrl();
        } elseif ($area === 'admin') {
            return $this->getAdminUrl();
        } else {
            throw new \RuntimeException(__METHOD__ . ': area not valid');
        }
    }
}
