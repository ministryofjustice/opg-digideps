<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\EndToEnd;

use App\Tests\Behat\v2\Registration\RegistrationTrait;
use App\Tests\Behat\v2\Reporting\Sections\ReportingSectionsFeatureContext;

class EndToEndFeatureContext extends ReportingSectionsFeatureContext
{
    use EndToEndTrait;
    use RegistrationTrait;
}
