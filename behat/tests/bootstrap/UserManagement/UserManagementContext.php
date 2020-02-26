<?php declare(strict_types=1);

namespace DigidepsBehat\UserManagement;

use DigidepsBehat\Common\BaseFeatureContext;
use DigidepsBehat\UserTrait;

class UserManagementContext extends BaseFeatureContext
{
    use UserTrait;
    use UserManagementTrait;
}
