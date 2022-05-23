<?php

namespace App\Tests\Behat\v2\Registration;

use App\Tests\Behat\Common\AuthenticationTrait;
use App\Tests\Behat\Common\FormTrait;
use App\Tests\Behat\v2\Common\BaseFeatureContext;

class RegistrationFeatureContext extends BaseFeatureContext
{
    use IngestTrait;
    use SelfRegistrationTrait;
    use AuthenticationTrait;
    use FormTrait;
}
