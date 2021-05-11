<?php

declare(strict_types=1);

namespace App\Tests\Behat\Reporting;

use App\Tests\Behat\Common\BaseFeatureContext;
use App\Tests\Behat\Common\CourtOrderTrait;
use App\Tests\Behat\Common\LinksTrait;
use App\Tests\Behat\Common\RegionTrait;
use App\Tests\Behat\Common\ReportTrait;
use App\Tests\Behat\ReportManagement\ReportManagementTrait;

class ReportingFeatureContext extends BaseFeatureContext
{
    use ReportTrait;
    use LinksTrait;
    use RegionTrait;
    use CourtOrderTrait;
    use ReportManagementTrait;
}
