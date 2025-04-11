<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\ReportSubmission;

use App\Tests\Behat\v2\Common\BaseFeatureContext;
use App\Tests\Behat\v2\Common\ReportTrait;
use App\Tests\Behat\v2\Reporting\Sections\DocumentsSectionTrait;

class ReportSubmissionFeatureContext extends BaseFeatureContext
{
    use ReportSubmissionTrait;
    use DocumentsSectionTrait;
}
