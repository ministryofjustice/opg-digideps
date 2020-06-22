<?php declare(strict_types=1);

namespace DigidepsBehat\ACL;

use DigidepsBehat\Common\BaseFeatureContext;
use DigidepsBehat\Common\CourtOrderTrait;
use DigidepsBehat\Common\RegionTrait;
use DigidepsBehat\Common\SiteNavigationTrait;
use DigidepsBehat\Common\UserOrganisationTrait;

class ACLfeatureContext extends BaseFeatureContext
{
    use CourtOrderTrait;
    use RegionTrait;
    use SiteNavigationTrait;
    use UserOrganisationTrait;
}
