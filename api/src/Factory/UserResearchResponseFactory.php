<?php


namespace App\Factory;

use App\Entity\UserResearch\ResearchType;
use App\Entity\UserResearch\UserResearchResponse;

class UserResearchResponseFactory
{
    /**
     * @param array $formData
     * @return mixed
     */
    public function generateFromFormData(array $formData)
    {
        $userResearchResponse = (new UserResearchResponse())
            ->setDeputyshipLength($formData['deputyshipLength'])
            ->setHasAccessToVideoCallDevice($formData['hasAccessToVideoCallDevice'])
            ->setSatisfaction($formData['satisfaction']);

        $researchType = (new ResearchType($formData['agreedResearchTypes']))
            ->setUserResearchResponse($userResearchResponse);

        $userResearchResponse->setResearchType($researchType);

        return $userResearchResponse;
    }
}
