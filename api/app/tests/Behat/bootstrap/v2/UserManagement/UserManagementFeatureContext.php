<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\UserManagement;

use App\Tests\Behat\Common\AuthenticationTrait;
use App\Tests\Behat\Common\FormTrait;
use App\Tests\Behat\UserTrait;
use App\Tests\Behat\v2\AdminManagement\AdminManagementTrait;
use App\Tests\Behat\v2\Common\AdminTrait;
use App\Tests\Behat\v2\Common\BaseFeatureContext;
use App\Tests\Behat\v2\DeputyManagement\DeputyManagementTrait;

class UserManagementFeatureContext extends BaseFeatureContext
{
    use AdminTrait;
    use AdminManagementTrait;
    use AuthenticationTrait;
    use FormTrait;
    use DeputyManagementTrait;
    use UserManagementTrait;
    use UserTrait;
}
