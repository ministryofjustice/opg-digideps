<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Registration;

use App\Tests\Behat\v2\Common\BaseFeatureContext;

class RegistrationFeatureContext extends BaseFeatureContext
{
    use IngestTrait;
    use RegistrationTrait;
}
