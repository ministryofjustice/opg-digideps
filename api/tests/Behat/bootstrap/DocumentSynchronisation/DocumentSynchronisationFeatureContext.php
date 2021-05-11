<?php

declare(strict_types=1);

namespace App\Tests\Behat\DocumentSynchronisation;

use App\Tests\Behat\Common\BaseFeatureContext;
use App\Tests\Behat\Common\CourtOrderTrait;
use App\Tests\Behat\Common\LinksTrait;
use App\Tests\Behat\Common\RegionTrait;
use App\Tests\Behat\Common\ReportTrait;

class DocumentSynchronisationFeatureContext extends BaseFeatureContext
{
    use CourtOrderTrait;
    use LinksTrait;
    use DocumentSynchronisationTrait;
    use RegionTrait;
    use ReportTrait;
}
