<?php

namespace App\TestHelpers;

use App\Entity\Organisation;

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
