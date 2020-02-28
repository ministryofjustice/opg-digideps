<?php

namespace DigidepsBehat\UserManagement;

use DigidepsBehat\Common\BaseFeatureContext;
use DigidepsBehat\Common\CourtOrderTrait;
use DigidepsBehat\Common\LinksTrait;
use DigidepsBehat\Common\RegionTrait;
use DigidepsBehat\Common\ReportTrait;
use DigidepsBehat\UserTrait;

class UserManagementFeatureContext extends BaseFeatureContext
{
    use LinksTrait;
    use RegionTrait;
    use UserManagementTrait;
    use UserTrait;
}
