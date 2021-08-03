<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Admin;

use App\Tests\Behat\SearchTrait;
use App\Tests\Behat\v2\Common\BaseFeatureContext;

class ReportingAdminFeatureContext extends BaseFeatureContext
{
    use ReportingChecklistTrait;
    use SearchTrait;
}
