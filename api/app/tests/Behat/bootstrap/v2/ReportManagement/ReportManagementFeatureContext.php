<?php

namespace Tests\OPG\Digideps\Backend\Behat\v2\ReportManagement;

use Tests\OPG\Digideps\Backend\Behat\v2\Common\BaseFeatureContext;
use Tests\OPG\Digideps\Backend\Behat\v2\CourtOrder\CourtOrderTrait;
use Tests\OPG\Digideps\Backend\Behat\v2\Reporting\Sections\ClientBenefitsCheckSectionTrait;

class ReportManagementFeatureContext extends BaseFeatureContext
{
    use CourtOrderTrait;
    use ReportManagementTrait;
    use ClientBenefitsCheckSectionTrait;
}
