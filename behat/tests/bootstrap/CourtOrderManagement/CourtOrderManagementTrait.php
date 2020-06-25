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

    /**
     * @Then a court order should exist between :deputy and :caseNumber
     */
    public function aCourtOrderShouldExistBetweenAnd($deputy, $caseNumber)
    {
        $result = null;

        $query = "
SELECT count(co.id) 
FROM court_order co 
JOIN court_order_deputy cod on cod.court_order_id = co.id
WHERE cod.email = '$deputy'
AND co.case_number = '$caseNumber'
";
        $command = sprintf('psql %s -c "%s" 2>&1', self::$dbName, $query);
        exec($command, $result);

        // The actual COUNT is found at 3rd element in $result.
        if ($result[2] < 1) {
            throw new \Exception('Expected court order to exist but it does not');
        }
    }

}
