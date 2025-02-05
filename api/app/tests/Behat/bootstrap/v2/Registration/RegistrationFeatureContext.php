<?php

namespace App\Tests\Behat\v2\Registration;

use App\Tests\Behat\Common\AuthenticationTrait;
use App\Tests\Behat\Common\FormTrait;
use App\Tests\Behat\Common\SiteNavigationTrait;
use App\Tests\Behat\CourtOrderManagement\CourtOrderManagementTrait;
use App\Tests\Behat\v2\AdminManagement\AdminManagementTrait;
use App\Tests\Behat\v2\Common\BaseFeatureContext;
use App\Tests\Behat\v2\Common\FixturesTrait;

class RegistrationFeatureContext extends BaseFeatureContext
{
    use ActivateTrait;
    use AdminManagementTrait;
    use AuthenticationTrait;
    use CourtOrderManagementTrait;
    use FixturesTrait;
    use FormTrait;
    use IngestTrait;
    use SelfRegistrationTrait;
    use SiteNavigationTrait;
}
