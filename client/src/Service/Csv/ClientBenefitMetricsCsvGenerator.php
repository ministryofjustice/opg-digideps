<?php

declare(strict_types=1);

namespace App\Service\Csv;

use App\Entity\CasRec;
use App\Entity\User;
use DateTime;

class ClientBenefitMetricsCsvGenerator
{
    private CsvBuilder $csvBuilder;

    public function __construct(CsvBuilder $csvBuilder)
    {
        $this->csvBuilder = $csvBuilder;
    }

    /**
     * @return string
     */
    public function generateClientBenefitsMetricCsv(array $clientBenefitResponses)
    {
        $headers = [
            'Deputy Type',
            'Last Check Entitlement',
            'Do Others Receive Money on Clients Behalf',
            'Date Last Checked',
            'Never Checked Explanation',
            'Don\'t Know Explanation',
            'Created On',
        ];

        $rows = [];

        foreach ($clientBenefitResponses as $response) {
            $deputyType = CasRec::REALM_PA === User::$depTypeIdToRealm[$response['deputy_type']] ? 'pa' : 'prof';

            if (null !== $response['date_last_checked_entitlement']) {
                $dateLastCheckedFormatted = (new DateTime($response['date_last_checked_entitlement']))->format('Y-m-d');
            }

            $rows[] = [
                $deputyType,
                $response['when_last_checked_entitlement'],
                $response['do_others_receive_money_on_clients_behalf'],
                $dateLastCheckedFormatted,
                $response['never_checked_explanation'],
                $response['dont_know_money_explanation'],
                $response['created_at'],
            ];
        }

        return $this->csvBuilder->buildCsv($headers, $rows);
    }
}
