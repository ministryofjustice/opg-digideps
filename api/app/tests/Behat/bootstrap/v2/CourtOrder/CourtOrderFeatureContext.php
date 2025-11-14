<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\CourtOrder;

use App\Tests\Behat\Common\LinksTrait;
use App\Tests\Behat\Common\RegionTrait;
use App\Tests\Behat\Common\SiteNavigationTrait;
use App\Tests\Behat\v2\Common\BaseFeatureContext;
use App\Tests\Behat\v2\Registration\SelfRegistrationTrait;

class CourtOrderFeatureContext extends BaseFeatureContext
{
    use CourtOrderTrait;
    use LinksTrait;
    use RegionTrait;
    use SelfRegistrationTrait;
    use SiteNavigationTrait;
}
