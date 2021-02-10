<?php

namespace DigidepsBehat\Common;

use Behat\MinkExtension\Context\MinkContext;

class BaseFeatureContext extends MinkContext
{
    use AuthenticationTrait;
    use DebugTrait;
    use FormTrait;
    use SiteNavigationTrait;

    protected static $dbName = 'api';

    /**
     * @return string
     */
    public function getAdminUrl(): string
    {
        return getenv('ADMIN_HOST');
    }
}
