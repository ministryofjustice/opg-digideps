<?php

namespace DigidepsBehat\Common;

use Behat\MinkExtension\Context\MinkContext;

class BaseFeatureContext extends MinkContext
{
    /**
     * @return string
     */
    public function getAdminUrl(): string
    {
        return getenv('ADMIN_HOST');
    }
}
