<?php

namespace App\Tests\Behat\OrganisationManagement;

use App\Tests\Behat\Common\BaseFeatureContext;
use App\Tests\Behat\Common\LinksTrait;
use App\Tests\Behat\Common\RegionTrait;
use App\Tests\Behat\UserTrait;

class OrganisationManagementFeatureContext extends BaseFeatureContext
{
    use LinksTrait;
    use OrganisationManagementTrait;
    use RegionTrait;
    use UserTrait;
}
