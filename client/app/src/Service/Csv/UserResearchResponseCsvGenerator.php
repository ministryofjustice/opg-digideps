<?php

declare(strict_types=1);

namespace App\Service\Csv;

use App\Entity\UserResearch\UserResearchResponse;
use DateTime;

class UserResearchResponseCsvGenerator
{
    private CsvBuilder $csvBuilder;

    public function __construct(CsvBuilder $csvBuilder)
    {
        $this->csvBuilder = $csvBuilder;
    }

    /**
     * @param []UserResearchResponse $userResearchResponses
     *
     * @return string
     */
    public function generateUserResearchResponsesCsv(array $userResearchResponses)
    {
        $headers = [
            'Satisfaction Score',
            'Comments',
            'Deputy Role',
            'Report Type',
            'Date Provided',
            'Deputyship Length Years',
            'Agreed Research Types',
            'Has Videocall Access',
            'Email',
            'Phone Number',
        ];

        $rows = [];

        foreach ($userResearchResponses as $response) {
            if (isset($response['satisfaction']) && empty($response['satisfaction'])) {
                continue;
            }

            $satisfaction = $response['satisfaction'];
            $user = $response['user'];
            $dateProvided = (new DateTime($satisfaction['created']['date']))->format('Y-m-d');

            $rows[] = [
                $satisfaction['score'],
                $satisfaction['comments'],
                $satisfaction['deputyrole'],
                $satisfaction['reporttype'],
                $dateProvided,
                $this->transformDeputyshipLength($response['deputyshipLength']),
                $this->getCommaSeparatedTypesAgreedFromArrayData($response['researchType']),
                $response['hasAccessToVideoCallDevice'] ? 'Yes' : 'No',
                $user['email'],
                $user['phoneMain'],
            ];
        }

        return $this->csvBuilder->buildCsv($headers, $rows);
    }

    private function transformDeputyshipLength(?string $deputyshipLengthFormString)
    {
        switch ($deputyshipLengthFormString) {
            case UserResearchResponse::UNDER_ONE:
                return 'Less than 1';
            case UserResearchResponse::ONE_TO_FIVE:
                return '1 - 5';
            case UserResearchResponse::SIX_TO_TEN:
                return '6 - 10';
            case UserResearchResponse::OVER_TEN:
                return 'More than 10';
        }

        return 'No response';
    }

    public function getCommaSeparatedTypesAgreedFromArrayData(array $researchTypeArray)
    {
        $types = [];

        foreach ($researchTypeArray as $propName => $value) {
            if ($value && 'id' !== $propName) {
                $types[] = $propName;
            }
        }

        return implode(',', $types);
    }
}
