<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\UserManagement;

use App\Tests\Behat\v2\Common\AdminTrait;
use App\Tests\Behat\v2\Common\BaseFeatureContext;
use App\Tests\Behat\v2\DeputyManagement\DeputyManagementTrait;

class UserManagementFeatureContext extends BaseFeatureContext
{
    use UserManagementTrait;
    use DeputyManagementTrait;
    use AdminTrait;
}
