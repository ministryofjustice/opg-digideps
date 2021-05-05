<?php

declare(strict_types=1);

namespace App\Tests\Behat\DeputyManagement;

use App\Tests\Behat\Common\BaseFeatureContext;
use App\Tests\Behat\Common\LinksTrait;
use App\Tests\Behat\Common\RegionTrait;
use App\Tests\Behat\UserTrait;

class DeputyManagementFeatureContext extends BaseFeatureContext
{
    use LinksTrait;
    use RegionTrait;
    use DeputyManagementTrait;
    use UserTrait;
}
