<?php

declare(strict_types=1);

namespace DigidepsBehat\DocumentSynchronisation;

use DigidepsBehat\Common\BaseFeatureContext;
use DigidepsBehat\Common\CourtOrderTrait;
use DigidepsBehat\Common\LinksTrait;
use DigidepsBehat\Common\RegionTrait;
use DigidepsBehat\Common\ReportTrait;

class DocumentSynchronisationFeatureContext extends BaseFeatureContext
{
    use CourtOrderTrait;
    use LinksTrait;
    use DocumentSynchronisationTrait;
    use RegionTrait;
    use ReportTrait;
}
