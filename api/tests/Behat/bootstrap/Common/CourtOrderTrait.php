<?php

namespace App\Tests\Behat\Common;

use Behat\Gherkin\Node\TableNode;

trait CourtOrderTrait
{
    /**
     * @Given the following court orders exist:
     */
    public function theFollowingCourtOrdersExist(TableNode $table)
    {
        $this->iAmLoggedInToAdminAsWithPassword('super-admin@publicguardian.gov.uk', 'DigidepsPass1234');

        foreach ($table as $row) {
            $queryString = http_build_query([
                'case-number' => $row['client'],
                'court-date' => $row['court_date'],
                'deputy-email' => $row['deputy'].'@behat-test.com',
            ]);

            $url = sprintf('/admin/fixtures/court-orders?%s', $queryString);
            $this->visitAdminPath($url);

            $activated = is_null($row['activated']) || 'true' == $row['activated'];
            $this->fillField('court_order_fixture_activated', $activated);
            $this->fillField('court_order_fixture_deputyType', $row['deputy_type']);
            $this->fillField('court_order_fixture_reportType', $this->resolveReportType($row));
            $this->fillField('court_order_fixture_reportStatus', $row['completed'] ? 'readyToSubmit' : 'notStarted');
            $this->fillField('court_order_fixture_orgSizeClients', $row['orgSizeClients'] ? $row['orgSizeClients'] : 1);
            $this->fillField('court_order_fixture_orgSizeUsers', $row['orgSizeUsers'] ? $row['orgSizeUsers'] : 1);

            $this->pressButton('court_order_fixture_submit');
        }
    }

    /**
     * @param $row
     */
    private function resolveReportType($row): string
    {
        $typeFromFeatureFile = strtolower($row['report_type']);

        switch ($typeFromFeatureFile) {
            case 'health and welfare':
                return '104';
            case 'property and financial affairs high assets':
                return '102';
            case 'property and financial affairs low assets':
                return '103';
            case 'high assets with health and welfare':
                return '102-4';
            case 'low assets with health and welfare':
                return '103-4';
            case 'ndr':
                return 'ndr';
            default:
                return '102';
        }
    }
}
