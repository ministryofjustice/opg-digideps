<?php declare(strict_types=1);

namespace DigidepsBehat\CompletingReports;

use DigidepsBehat\Common\BaseFeatureContext;
use DigidepsBehat\Common\ButtonTrait;
use DigidepsBehat\Common\CourtOrderTrait;
use DigidepsBehat\Common\LinksTrait;
use DigidepsBehat\Common\RegionTrait;
use DigidepsBehat\Common\ReportTrait;
use DigidepsBehat\ReportManagement\ReportManagementTrait;

class CompletingReportsFeatureContext extends BaseFeatureContext
{
    use CourtOrderTrait;
    use CompletingReportsTrait;
    use LinksTrait;
    use RegionTrait;
    use ButtonTrait;
    use ReportTrait;
    use ReportManagementTrait;
}
