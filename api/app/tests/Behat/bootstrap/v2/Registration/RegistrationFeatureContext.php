<?php

namespace Tests\OPG\Digideps\Backend\Behat\v2\Registration;

use Tests\OPG\Digideps\Backend\Behat\Common\AuthenticationTrait;
use Tests\OPG\Digideps\Backend\Behat\Common\SiteNavigationTrait;
use Tests\OPG\Digideps\Backend\Behat\CourtOrderManagement\CourtOrderManagementTrait;
use Tests\OPG\Digideps\Backend\Behat\v2\AdminManagement\AdminManagementTrait;
use Tests\OPG\Digideps\Backend\Behat\v2\Common\BaseFeatureContext;
use Tests\OPG\Digideps\Backend\Behat\v2\CourtOrder\CourtOrderTrait;
use Tests\OPG\Digideps\Backend\Behat\v2\Reporting\Sections\ReportingSectionsTrait;

class RegistrationFeatureContext extends BaseFeatureContext
{
    use ActivateTrait;
    use AdminManagementTrait;
    use AuthenticationTrait;
    use CourtOrderManagementTrait;
    use CourtOrderTrait;
    use IngestTrait;
    use ReportingSectionsTrait;
    use SelfRegistrationTrait;
    use SiteNavigationTrait;
}
