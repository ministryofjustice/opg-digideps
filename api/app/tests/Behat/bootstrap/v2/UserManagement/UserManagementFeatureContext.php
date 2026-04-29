<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\UserManagement;

use Tests\OPG\Digideps\Backend\Behat\Common\AuthenticationTrait;
use Tests\OPG\Digideps\Backend\Behat\Common\FormTrait;
use Tests\OPG\Digideps\Backend\Behat\UserTrait;
use Tests\OPG\Digideps\Backend\Behat\v2\AdminManagement\AdminManagementTrait;
use Tests\OPG\Digideps\Backend\Behat\v2\Common\AdminTrait;
use Tests\OPG\Digideps\Backend\Behat\v2\Common\BaseFeatureContext;
use Tests\OPG\Digideps\Backend\Behat\v2\DeputyManagement\DeputyManagementTrait;

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
