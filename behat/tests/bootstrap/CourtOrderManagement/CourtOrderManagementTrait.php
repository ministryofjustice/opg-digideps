<?php

namespace DigidepsBehat\CourtOrderManagement;

trait CourtOrderManagementTrait
{
    /**
     * @When a super admin discharges the deputy from :caseNumber
     */
    public function aSuperAdminDischargesDeputyFromClient($caseNumber)
    {
        $this->iAmLoggedInToAdminAsWithPassword('admin@publicguardian.gov.uk', 'Abcd1234');
        $this->clickLink('Clients');
        $this->clickLink('John ' . $caseNumber . '-client');
        $this->clickLink('Discharge deputy');
        $this->clickLink('Discharge deputy');
    }
}
