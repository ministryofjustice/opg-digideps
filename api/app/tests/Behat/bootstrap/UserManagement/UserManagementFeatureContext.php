<?php

declare(strict_types=1);

namespace App\Tests\Behat\UserManagement;

use App\Tests\Behat\Common\BaseFeatureContext;
use App\Tests\Behat\Common\LinksTrait;
use App\Tests\Behat\Common\RegionTrait;
use App\Tests\Behat\UserTrait;
use App\Tests\Behat\v2\Common\AuthTrait;
use App\Tests\Behat\v2\Common\INavigateToAdminTrait;

class UserManagementFeatureContext extends BaseFeatureContext
{
    use AuthTrait;
    use INavigateToAdminTrait;
    use LinksTrait;
    use RegionTrait;
    use UserManagementTrait;
    use UserTrait;
}
