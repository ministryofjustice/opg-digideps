<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\AdminManagement;

use App\Tests\Behat\v2\Common\AdminTrait;
use App\Tests\Behat\v2\Common\BaseFeatureContext;

class AdminManagementFeatureContext extends BaseFeatureContext
{
    use AdminTrait;
    use AdminManagementTrait;
}
