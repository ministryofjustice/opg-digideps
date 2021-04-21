<?php declare(strict_types=1);


namespace App\Service\Csv;

use App\Entity\UserResearch\UserResearchResponse;

class UserResearchResponseCsvGenerator
{
    /**
     * @var CsvBuilder
     */
    private CsvBuilder $csvBuilder;

    public function __construct(CsvBuilder $csvBuilder)
    {
        $this->csvBuilder = $csvBuilder;
    }

    /**
     * @param []UserResearchResponse $userResearchResponses
     * @return string
     */
    public function generateUserResearchResponsesCsv(array $userResearchResponses)
    {
        $headers = [
            "Satisfaction Score",
            "Comments",
            "Deputy Role",
            "Report Type",
            "Date Provided",
            "Deputyship Length Years",
            "Agreed Research Types",
            "Has Videocall Access",
            "Email",
            "Phone Number"
        ];

        $rows = [];

        foreach ($userResearchResponses as $response) {
            $satisfaction = $response->getSatisfaction();

            $rows[] = [
                $satisfaction->getScore(),
                $satisfaction->getComments(),
                $satisfaction->getDeputyrole(),
                $satisfaction->getReporttype(),
                $satisfaction->getCreated()->format('Y-m-d'),
                $this->transformDeputyshipLength($response->getDeputyShipLength()),
                $response->getAgreedResearchTypes()->getCommaSeparatedTypesAgreed(),
                $response->GetHasAccessToVideoCallDevice() ? 'Yes' : 'No',
                $response->getUser()->getEmail(),
                $response->getUser()->getPhoneMain()
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
}
