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
            ->setHasAccessToVideoCallDevice($formData['hasAccessToVideoCallDevice']);

        $researchType = (new ResearchType($formData['agreedResearchTypes']))
            ->setUserResearchResponse($userResearchResponse);

        $userResearchResponse->setAgreedResearchTypes($researchType);

        return $userResearchResponse;
    }
}
