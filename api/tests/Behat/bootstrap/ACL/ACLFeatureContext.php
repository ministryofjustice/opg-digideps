<?php

declare(strict_types=1);

namespace App\Tests\Behat\ACL;

use App\Tests\Behat\Common\BaseFeatureContext;
use App\Tests\Behat\Common\CourtOrderTrait;
use App\Tests\Behat\Common\RegionTrait;
use App\Tests\Behat\Common\SiteNavigationTrait;
use App\Tests\Behat\Common\UserOrganisationTrait;

class ACLFeatureContext extends BaseFeatureContext
{
    use CourtOrderTrait;
    use RegionTrait;
    use SiteNavigationTrait;
    use UserOrganisationTrait;
}
