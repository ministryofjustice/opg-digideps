<?php

declare(strict_types=1);

namespace DigidepsBehat\ContactDetails;

use DigidepsBehat\Common\BaseFeatureContext;
use DigidepsBehat\Common\RegionTrait;
use DigidepsBehat\UserTrait;

class ContactDetailsFeatureContext extends BaseFeatureContext
{
    use UserTrait;
    use RegionTrait;
}
