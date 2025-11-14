<?php

declare(strict_types=1);

namespace App\Tests\Behat\UserManagement;

use App\Tests\Behat\Common\BaseFeatureContext;
use App\Tests\Behat\Common\LinksTrait;
use App\Tests\Behat\Common\RegionTrait;
use App\Tests\Behat\UserTrait;
use App\Tests\Behat\v2\AdminManagement\AdminManagementTrait;
use App\Tests\Behat\v2\Common\AuthTrait;

class UserManagementFeatureContext extends BaseFeatureContext
{
    use AdminManagementTrait;
    use AuthTrait;
    use LinksTrait;
    use RegionTrait;
    use UserManagementTrait;
    use UserTrait;
}
