<?php

namespace DigidepsBehat\CourtOrderManagement;

use DigidepsBehat\Common\BaseFeatureContext;
use DigidepsBehat\Common\CourtOrderTrait;
use DigidepsBehat\Common\LinksTrait;
use DigidepsBehat\Common\RegionTrait;
use DigidepsBehat\UserManagement\DeputyManagementTrait;
use DigidepsBehat\UserTrait;

class CourtOrderManagementFeatureContext extends BaseFeatureContext
{
    use CourtOrderTrait;
    use CourtOrderManagementTrait;
    use LinksTrait;
    use RegionTrait;
    use DeputyManagementTrait;
    use UserTrait;
}
