<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportMoreInfoTrait
{
    #[JMS\Type('string')]
    #[JMS\Groups(['more-info'])]
    #[Assert\NotBlank(message: 'action.actionMoreInfo.notBlank', groups: ['more-info'])]
    private ?string $actionMoreInfo = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['more-info'])]
    #[Assert\NotBlank(message: 'action.actionMoreInfoDetails.notBlank', groups: ['more-info-details'])]
    private ?string $actionMoreInfoDetails = null;

    public function getActionMoreInfo(): ?string
    {
        return $this->actionMoreInfo;
    }

    public function setActionMoreInfo(?string $actionMoreInfo): static
    {
        $this->actionMoreInfo = $actionMoreInfo;

        return $this;
    }

    public function getActionMoreInfoDetails(): ?string
    {
        return $this->actionMoreInfoDetails;
    }

    public function setActionMoreInfoDetails(?string $actionMoreInfoDetails): static
    {
        $this->actionMoreInfoDetails = $actionMoreInfoDetails;

        return $this;
    }
}
