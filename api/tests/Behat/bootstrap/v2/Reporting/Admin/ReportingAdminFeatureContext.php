<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Admin;

use App\Tests\Behat\SearchTrait;
use App\Tests\Behat\v2\AdminManagement\AdminManagementTrait;
use App\Tests\Behat\v2\ClientManagement\ClientManagementTrait;
use App\Tests\Behat\v2\Common\BaseFeatureContext;
use App\Tests\Behat\v2\Reporting\Sections\ClientBenefitsCheckSectionTrait;

class ReportingAdminFeatureContext extends BaseFeatureContext
{
    use ClientBenefitsCheckSectionTrait;
    use ClientManagementTrait;
    use ReportingChecklistTrait;
    use SearchTrait;
    use AdminManagementTrait;
}
