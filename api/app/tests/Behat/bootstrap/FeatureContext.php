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
use Behat\MinkExtension\Context\MinkContext;

/**
 * Behat context class
 */
class FeatureContext extends MinkContext
{
    use AuthenticationTrait;
    use DebugTrait;
    use DbTrait;
    use CookieTrait;
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
    use ReportTrait;
    use CourtOrderTrait;

    protected static string $dbName = 'api';
    protected static bool $autoDbSnapshot = false;
    private string $sessionName;

    public function __construct($options = [])
    {
        $maxNestingLevel = $options['maxNestingLevel'] ?? 200;

        ini_set('xdebug.max_nesting_level', $maxNestingLevel);
        ini_set('max_nesting_level', $maxNestingLevel);

        $this->sessionName = empty($options['sessionName']) ? 'digideps' : $options['sessionName'];
        self::$dbName = empty($options['dbName']) ? 'api' : $options['dbName'];
    }

    public function getAdminUrl(): string|false
    {
        return getenv('ADMIN_HOST');
    }
}
