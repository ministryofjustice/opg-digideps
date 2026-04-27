<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\ClientManagement;

use Tests\OPG\Digideps\Backend\Behat\v2\DeputyManagement\DeputyManagementTrait;
use Tests\OPG\Digideps\Backend\Behat\v2\Reporting\Sections\ReportingSectionsFeatureContext;

class ClientManagementFeatureContext extends ReportingSectionsFeatureContext
{
    use ClientManagementTrait;
    use DeputyManagementTrait;
}
