<?php declare(strict_types=1);

namespace DigidepsBehat\UserManagement;

use DigidepsBehat\Common\BaseFeatureContext;
use DigidepsBehat\Common\LinksTrait;
use DigidepsBehat\Common\RegionTrait;
use DigidepsBehat\UserTrait;

class UserManagementFeatureContext extends BaseFeatureContext
{
    use LinksTrait;
    use RegionTrait;
    use UserManagementTrait;
    use UserTrait;
}
