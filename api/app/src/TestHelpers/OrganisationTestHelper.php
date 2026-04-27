<?php

namespace OPG\Digideps\Backend\TestHelpers;

use OPG\Digideps\Backend\Entity\Organisation;

class OrganisationTestHelper
{
    public function createOrganisation(string $orgName, string $emailIdentifier)
    {
        return (new Organisation())
            ->setName($orgName)
            ->setEmailIdentifier($emailIdentifier)
            ->setIsActivated(true);
    }
}
