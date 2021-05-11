<?php

declare(strict_types=1);

namespace App\Tests\Behat\ContactDetails;

use App\Tests\Behat\Common\BaseFeatureContext;
use App\Tests\Behat\Common\RegionTrait;
use App\Tests\Behat\UserTrait;

class ContactDetailsFeatureContext extends BaseFeatureContext
{
    use UserTrait;
    use RegionTrait;
}
