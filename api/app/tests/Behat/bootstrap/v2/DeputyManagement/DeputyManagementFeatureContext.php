<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\DeputyManagement;

use Tests\OPG\Digideps\Backend\Behat\Common\LinksTrait;
use Tests\OPG\Digideps\Backend\Behat\Common\RegionTrait;
use Tests\OPG\Digideps\Backend\Behat\UserTrait;
use Tests\OPG\Digideps\Backend\Behat\v2\Common\AdminTrait;
use Tests\OPG\Digideps\Backend\Behat\v2\Common\BaseFeatureContext;

class DeputyManagementFeatureContext extends BaseFeatureContext
{
    use LinksTrait;
    use RegionTrait;
    use DeputyManagementTrait;
    use UserTrait;
    use AdminTrait;
}
