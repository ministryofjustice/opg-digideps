<?php

namespace App\Tests\Behat\v2\ReportManagement;

use App\Tests\Behat\v2\Common\BaseFeatureContext;
use App\Tests\Behat\v2\Reporting\Sections\ClientBenefitsCheckSectionTrait;

class ReportManagementFeatureContext extends BaseFeatureContext
{
    use ReportManagementTrait;
    use ClientBenefitsCheckSectionTrait;
}
