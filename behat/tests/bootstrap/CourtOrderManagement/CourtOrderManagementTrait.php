<?php

namespace DigidepsBehat\CourtOrderManagement;

trait CourtOrderManagementTrait
{
    /**
     * @When a super admin discharges the deputy from :caseNumber
     */
    public function aSuperAdminDischargesDeputyFromClient($caseNumber)
    {
        $this->iAmLoggedInToAdminAsWithPassword('super-admin@publicguardian.gov.uk', 'Abcd1234');
        $this->visitAdminPath("/admin/client/case-number/$caseNumber/details");
        $this->clickLink('Discharge deputy');
        $this->clickLink('Discharge deputy');
    }
}
