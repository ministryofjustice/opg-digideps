<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\ClientManagement;

use App\Tests\Behat\v2\DeputyManagement\DeputyManagementTrait;
use App\Tests\Behat\v2\Reporting\Sections\ReportingSectionsFeatureContext;

class ClientManagementFeatureContext extends ReportingSectionsFeatureContext
{
    use ClientManagementTrait;
    use DeputyManagementTrait;
}
