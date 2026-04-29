<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\AppNotification;

use Tests\OPG\Digideps\Backend\Behat\v2\Common\AssertTrait;
use Tests\OPG\Digideps\Backend\Behat\v2\Common\BaseFeatureContext;

class AppNotificationFeatureContext extends BaseFeatureContext
{
    use AppNotificationTrait;
    use AssertTrait;
}
