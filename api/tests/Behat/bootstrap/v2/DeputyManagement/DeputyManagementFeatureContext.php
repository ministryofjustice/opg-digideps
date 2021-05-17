<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\DeputyManagement;

use App\Tests\Behat\Common\LinksTrait;
use App\Tests\Behat\Common\RegionTrait;
use App\Tests\Behat\UserTrait;
use App\Tests\Behat\v2\Common\BaseFeatureContext;

class DeputyManagementFeatureContext extends BaseFeatureContext
{
    use LinksTrait;
    use RegionTrait;
    use DeputyManagementTrait;
    use UserTrait;
}
