<?php

namespace DigidepsBehat\OrganisationManagement;

use DigidepsBehat\Common\BaseFeatureContext;
use DigidepsBehat\Common\LinksTrait;
use DigidepsBehat\Common\RegionTrait;
use DigidepsBehat\UserTrait;

class OrganisationManagementFeatureContext extends BaseFeatureContext
{
    use LinksTrait;
    use OrganisationManagementTrait;
    use RegionTrait;
    use UserTrait;
}
