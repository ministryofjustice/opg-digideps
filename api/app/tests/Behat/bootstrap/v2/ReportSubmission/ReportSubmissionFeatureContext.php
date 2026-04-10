<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\ReportSubmission;

use Tests\OPG\Digideps\Backend\Behat\v2\Common\BaseFeatureContext;
use Tests\OPG\Digideps\Backend\Behat\v2\CourtOrder\CourtOrderTrait;
use Tests\OPG\Digideps\Backend\Behat\v2\Reporting\Sections\DocumentsSectionTrait;

class ReportSubmissionFeatureContext extends BaseFeatureContext
{
    use CourtOrderTrait;
    use ReportSubmissionTrait;
    use DocumentsSectionTrait;
}
