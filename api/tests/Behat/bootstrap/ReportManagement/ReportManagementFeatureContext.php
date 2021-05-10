<?php

namespace App\Tests\Behat\ReportManagement;

use App\Tests\Behat\Common\BaseFeatureContext;
use App\Tests\Behat\Common\CourtOrderTrait;
use App\Tests\Behat\Common\LinksTrait;
use App\Tests\Behat\Common\RegionTrait;
use App\Tests\Behat\Common\ReportTrait;

class ReportManagementFeatureContext extends BaseFeatureContext
{
    use CourtOrderTrait;
    use LinksTrait;
    use RegionTrait;
    use ReportManagementTrait;
    use ReportTrait;
}
