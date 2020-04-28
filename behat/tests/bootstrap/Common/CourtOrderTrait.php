<?php

namespace DigidepsBehat\Common;

use Behat\Gherkin\Node\TableNode;

trait CourtOrderTrait
{
    /**
     * @Given the following court orders exist:
     *
     * @param TableNode $table
     */
    public function theFollowingCourtOrdersExist(TableNode $table)
    {
        $this->iAmLoggedInToAdminAsWithPassword('admin@publicguardian.gov.uk', 'Abcd1234');

        foreach ($table as $row) {
            $queryString = http_build_query([
                'case-number' => $row['client'],
                'court-date' => $row['court_date'],
                'deputy-email' => $row['deputy'] . '@behat-test.com'
            ]);

            $url = sprintf('/admin/fixtures/court-orders?%s', $queryString);
            $this->visitAdminPath($url);

            $this->fillField('court_order_fixture_deputyType', $row['deputy_type']);
            $this->fillField('court_order_fixture_reportType', $this->resolveReportType($row));
            $this->fillField('court_order_fixture_reportStatus', $row['completed'] ? 'readyToSubmit' : 'notStarted');
            $this->pressButton('court_order_fixture_submit');
        }
    }

    /**
     * @param $row
     * @return string
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
