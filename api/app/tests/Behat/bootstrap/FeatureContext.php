<?php

namespace App\Tests\Behat;

use App\Tests\Behat\Common\AuthenticationTrait;
use App\Tests\Behat\Common\CourtOrderTrait;
use App\Tests\Behat\Common\DebugTrait;
use App\Tests\Behat\Common\FormTrait;
use App\Tests\Behat\Common\LinksTrait;
use App\Tests\Behat\Common\RegionTrait;
use App\Tests\Behat\Common\ReportTrait;
use App\Tests\Behat\Common\SiteNavigationTrait;
use App\Tests\Behat\OrganisationManagement\OrganisationManagementTrait;
use App\Tests\Behat\UserManagement\UserManagementTrait;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;

/**
 * Behat context class.
 *
 * when the alpha models are refactored and simplified, this class can be refactored and splitter around.
 * until then, better to keep things in the sample place for simplicity
 */
class FeatureContext extends MinkContext implements SnippetAcceptingContext
{
    use AuthenticationTrait;
    use DebugTrait;
    use DbTrait;
    use CookieTrait;
    use FileTrait;
    use FormStepTrait;
    use FormTrait;
    use LinksTrait;
    use LinksPreviouslySavedTrait;
    use RegionTrait;
    use ReportTrait;
    use SiteNavigationTrait;
    use UserTrait;
    use SearchTrait;
    use OrganisationManagementTrait;
    use UserManagementTrait;
    use ReportTrait;
    use CourtOrderTrait;

    protected static $dbName = 'api';
    protected static $sqlPath = 'tests/behat/sql/';

    protected static $autoDbSnapshot = false;

    public function __construct($options = [])
    {
        // $options['session']; // not used
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
     * @Then the response should have the :arg1 header containing :arg2
     */
    public function theResponseShouldHaveTheHeaderContaining($header, $value)
    {
        $headers = array_change_key_case($this->getSession()->getDriver()->getResponseHeaders(), CASE_LOWER);
        if (empty($headers[strtolower($header)][0])) {
            throw new \Exception("Header '{$header}' not found.");
        }
        if (false === strpos($headers[strtolower($header)][0], $value)) {
            throw new \Exception("Header '{$header}' has value '{$headers[$header][0]}' that does not contains '{$value}'");
        }
    }

    /**
     * @Given the :area area works properly
     */
    public function iChecktheAppParameterFile($area)
    {
        $baseUrl = $this->getAreaUrl($area);

        $this->visitPath($baseUrl.'/health-check/service');
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
        if ('deputy' === $area) {
            return $this->getSiteUrl();
        } elseif ('admin' === $area) {
            return $this->getAdminUrl();
        } else {
            throw new \RuntimeException(__METHOD__.': area not valid');
        }
    }
}
