<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use OPG\Digideps\Frontend\Entity\Traits\CreationAudit;
use JMS\Serializer\Annotation as JMS;

class ChecklistInformation
{
    use CreationAudit;

    #[JMS\Type('integer')]
    #[JMS\Groups(['checklist-information'])]
    private int $id;

    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\Checklist')]
    #[JMS\Groups(['checklist-information-checklist'])]
    private Checklist $checklist;

    #[JMS\Type('string')]
    #[JMS\Groups(['checklist-information'])]
    private string $information;

    public function __construct(Checklist $checklist, string $information)
    {
        $this->setChecklist($checklist);
        $this->setInformation(trim($information));
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getChecklist(): Checklist
    {
        return $this->checklist;
    }

    public function setChecklist(Checklist $checklist): static
    {
        $this->checklist = $checklist;
        return $this;
    }

    public function getInformation(): string
    {
        return $this->information;
    }

    public function setInformation(string $information): static
    {
        $this->information = $information;
        return $this;
    }
}
