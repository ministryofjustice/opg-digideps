<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\CourtOrder;

use Tests\OPG\Digideps\Backend\Behat\Common\LinksTrait;
use Tests\OPG\Digideps\Backend\Behat\Common\RegionTrait;
use Tests\OPG\Digideps\Backend\Behat\Common\SiteNavigationTrait;
use Tests\OPG\Digideps\Backend\Behat\v2\Common\BaseFeatureContext;
use Tests\OPG\Digideps\Backend\Behat\v2\Registration\SelfRegistrationTrait;

class CourtOrderFeatureContext extends BaseFeatureContext
{
    use CourtOrderTrait;
    use LinksTrait;
    use RegionTrait;
    use SelfRegistrationTrait;
    use SiteNavigationTrait;
}
