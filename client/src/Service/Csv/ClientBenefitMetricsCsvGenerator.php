<?php

declare(strict_types=1);

namespace App\Service\Csv;

use DateTime;
use Exception;

class ClientBenefitMetricsCsvGenerator
{
    public function __construct(private CsvBuilder $csvBuilder)
    {
    }

    /**
     * @throws Exception
     */
    public function generateClientBenefitsMetricCsv(array $clientBenefitResponses): string
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
        $dateLastCheckedFormatted = '';

        foreach ($clientBenefitResponses as $response) {
            if (null !== $response['dateLastCheckedEntitlement']) {
                $dateLastCheckedFormatted = (new DateTime($response['dateLastCheckedEntitlement']))->format('Y-m-d');
            }

            $rows[] = [
                $response['deputy_type'],
                $response['whenLastCheckedEntitlement'],
                $response['doOthersReceiveMoneyOnClientsBehalf'],
                $dateLastCheckedFormatted,
                $response['neverCheckedExplanation'],
                $response['dontKnowMoneyExplanation'],
                $response['created'],
            ];
        }

        return $this->csvBuilder->buildCsv($headers, $rows);
    }
}
