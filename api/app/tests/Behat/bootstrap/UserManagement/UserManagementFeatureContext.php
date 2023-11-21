<?php

declare(strict_types=1);

namespace App\Tests\Behat\UserManagement;

use App\Tests\Behat\Common\BaseFeatureContext;
use App\Tests\Behat\Common\LinksTrait;
use App\Tests\Behat\Common\RegionTrait;
use App\Tests\Behat\UserTrait;

class UserManagementFeatureContext extends BaseFeatureContext
{
    use LinksTrait;
    use RegionTrait;
    use UserManagementTrait;
    use UserTrait;
}
