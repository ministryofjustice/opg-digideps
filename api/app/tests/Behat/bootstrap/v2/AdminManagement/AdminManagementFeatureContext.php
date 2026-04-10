<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\AdminManagement;

use Tests\OPG\Digideps\Backend\Behat\v2\Common\AdminTrait;
use Tests\OPG\Digideps\Backend\Behat\v2\Common\BaseFeatureContext;

class AdminManagementFeatureContext extends BaseFeatureContext
{
    use AdminTrait;
    use AdminManagementTrait;
}
