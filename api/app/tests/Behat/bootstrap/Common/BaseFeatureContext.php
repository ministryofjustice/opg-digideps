<?php

namespace App\Tests\Behat\Common;

use Behat\MinkExtension\Context\MinkContext;

class BaseFeatureContext extends MinkContext
{
    use AuthenticationTrait;
    use DebugTrait;
    use FormTrait;
    use SiteNavigationTrait;

    protected static $dbName = 'api';

    public function getAdminUrl(): string
    {
        return getenv('ADMIN_HOST');
    }
}
