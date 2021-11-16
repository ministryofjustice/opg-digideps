<?php

declare(strict_types=1);

namespace App\Service\Csv;

use App\Entity\UserResearch\UserResearchResponse;

class UserResearchResponseCsvGenerator
{
    public function __construct(private CsvBuilder $csvBuilder)
    {
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
            if (is_null($response->getSatisfaction())) {
                continue;
            }

            $satisfaction = $response->getSatisfaction();

            $rows[] = [
                $satisfaction->getScore(),
                $satisfaction->getComments(),
                $satisfaction->getDeputyrole(),
                $satisfaction->getReporttype(),
                $satisfaction->getCreated()->format('Y-m-d'),
                $this->transformDeputyshipLength($response->getDeputyShipLength()),
                $response->getResearchType()->getCommaSeparatedTypesAgreed(),
                $response->GetHasAccessToVideoCallDevice() ? 'Yes' : 'No',
                $response->getUser()->getEmail(),
                $response->getUser()->getPhoneMain(),
            ];
        }

        return $this->csvBuilder->buildCsv($headers, $rows);
    }

    private function transformDeputyshipLength(?string $deputyshipLengthFormString)
    {
        return match ($deputyshipLengthFormString) {
            UserResearchResponse::UNDER_ONE => 'Less than 1',
            UserResearchResponse::ONE_TO_FIVE => '1 - 5',
            UserResearchResponse::SIX_TO_TEN => '6 - 10',
            UserResearchResponse::OVER_TEN => 'More than 10',
            default => 'No response',
        };
    }
}
