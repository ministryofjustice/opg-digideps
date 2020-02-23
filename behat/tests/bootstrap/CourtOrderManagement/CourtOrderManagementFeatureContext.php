<?php

namespace DigidepsBehat\CourtOrderManagement;

use DigidepsBehat\Common\BaseFeatureContext;
use DigidepsBehat\Common\CourtOrderTrait;

class CourtOrderManagementFeatureContext extends BaseFeatureContext
{
    use CourtOrderTrait;
    use CourtOrderManagementTrait;
}
