<?php declare(strict_types=1);

namespace DigidepsBehat\DeputyManagement;

use DigidepsBehat\Common\BaseFeatureContext;
use DigidepsBehat\Common\LinksTrait;
use DigidepsBehat\Common\RegionTrait;
use DigidepsBehat\UserTrait;

class DeputyManagementFeatureContext extends BaseFeatureContext
{
    use LinksTrait;
    use RegionTrait;
    use DeputyManagementTrait;
    use UserTrait;
}
