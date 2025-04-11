<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\ACL;

use App\Tests\Behat\Common\FormTrait;
use App\Tests\Behat\v2\Common\BaseFeatureContext;
use App\Tests\Behat\v2\DeputyManagement\DeputyManagementTrait;

class ACLFeatureContext extends BaseFeatureContext
{
    use ACLTrait;
    use DeputyManagementTrait;
}
