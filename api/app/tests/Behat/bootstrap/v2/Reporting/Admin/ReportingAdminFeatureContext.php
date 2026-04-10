<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\Reporting\Admin;

use Tests\OPG\Digideps\Backend\Behat\SearchTrait;
use Tests\OPG\Digideps\Backend\Behat\v2\AdminManagement\AdminManagementTrait;
use Tests\OPG\Digideps\Backend\Behat\v2\ClientManagement\ClientManagementTrait;
use Tests\OPG\Digideps\Backend\Behat\v2\Common\BaseFeatureContext;
use Tests\OPG\Digideps\Backend\Behat\v2\Reporting\Sections\ClientBenefitsCheckSectionTrait;

class ReportingAdminFeatureContext extends BaseFeatureContext
{
    use ClientBenefitsCheckSectionTrait;
    use ClientManagementTrait;
    use ReportingChecklistTrait;
    use SearchTrait;
    use AdminManagementTrait;
}
