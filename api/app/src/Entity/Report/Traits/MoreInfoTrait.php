<?php

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait MoreInfoTrait
{
    /**
     * @var ?string yes|no|null
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"action-more-info"})
     *
     * @ORM\Column(name="action_more_info", type="string", length=3, nullable=true)
     */
    private ?string $actionMoreInfo = null;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"action-more-info"})
     *
     * @ORM\Column(name="action_more_info_details", type="text", nullable=true)
     */
    private ?string $actionMoreInfoDetails;

    public function getActionMoreInfo(): ?string
    {
        return $this->actionMoreInfo;
    }

    public function setActionMoreInfo(string $actionMoreInfo): static
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
