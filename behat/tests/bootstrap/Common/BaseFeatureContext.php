<?php

namespace DigidepsBehat\Common;

use Behat\MinkExtension\Context\MinkContext;
use DigidepsBehat\AuthenticationTrait;
use DigidepsBehat\FormTrait;
use DigidepsBehat\SiteNavigationTrait;

class BaseFeatureContext extends MinkContext
{
    use AuthenticationTrait;
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
