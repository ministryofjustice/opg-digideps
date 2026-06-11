<?php

namespace OPG\Digideps\Backend\Factory;

use OPG\Digideps\Backend\Entity\UserResearch\ResearchType;
use OPG\Digideps\Backend\Entity\UserResearch\UserResearchResponse;

class UserResearchResponseFactory
{
    public function generateFromFormData(array $formData): UserResearchResponse
    {
        $userResearchResponse = new UserResearchResponse()
            ->setDeputyshipLength($formData['deputyshipLength'])
            ->setHasAccessToVideoCallDevice($formData['hasAccessToVideoCallDevice'])
            ->setSatisfaction($formData['satisfaction']);

        $researchType = new ResearchType($formData['agreedResearchTypes'])
            ->setUserResearchResponse($userResearchResponse);

        $userResearchResponse->setResearchType($researchType);

        return $userResearchResponse;
    }
}
