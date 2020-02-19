<?php

namespace DigidepsBehat\ReportManagement;

use DigidepsBehat\Common\BaseFeatureContext;
use DigidepsBehat\Common\CourtOrderTrait;
use DigidepsBehat\Common\LinksTrait;
use DigidepsBehat\Common\RegionTrait;
use DigidepsBehat\Common\ReportTrait;

class ReportManagementFeatureContext extends BaseFeatureContext
{
    use CourtOrderTrait;
    use LinksTrait;
    use RegionTrait;
    use ReportManagementTrait;
    use ReportTrait;
}
