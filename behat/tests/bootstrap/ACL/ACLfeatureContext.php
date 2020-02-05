<?php

namespace DigidepsBehat\ACL;

use DigidepsBehat\Common\BaseFeatureContext;
use DigidepsBehat\Common\CourtOrderTrait;
use DigidepsBehat\Common\UserOrganisationTrait;

class ACLfeatureContext extends BaseFeatureContext
{
    use CourtOrderTrait;
    use UserOrganisationTrait;
}
