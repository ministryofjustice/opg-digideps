<?php

namespace App\Tests\Behat\CourtOrderManagement;

use App\Tests\Behat\Common\BaseFeatureContext;
use App\Tests\Behat\Common\CourtOrderTrait;
use App\Tests\Behat\Common\LinksTrait;
use App\Tests\Behat\Common\RegionTrait;
use App\Tests\Behat\UserManagement\UserManagementTrait;
use App\Tests\Behat\UserTrait;

class CourtOrderManagementFeatureContext extends BaseFeatureContext
{
    use CourtOrderTrait;
    use CourtOrderManagementTrait;
    use LinksTrait;
    use RegionTrait;
    use UserManagementTrait;
    use UserTrait;
}
