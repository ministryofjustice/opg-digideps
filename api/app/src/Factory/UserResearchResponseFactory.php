<?php

namespace OPG\Digideps\Backend\Factory;

use OPG\Digideps\Backend\Entity\UserResearch\ElectronicDeviceType;
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

        $electronicDeviceTypes = $formData['electronicDeviceTypes'];

        if (!is_array($electronicDeviceTypes)) {
            throw new \InvalidArgumentException('electronicDeviceTypes must be an array');
        }

        $electronicDeviceType = new ElectronicDeviceType($electronicDeviceTypes)
            ->setUserResearchResponse($userResearchResponse);

        $userResearchResponse->setElectronicDeviceType($electronicDeviceType);

        return $userResearchResponse;
    }
}
