<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Traits\CreationAudit;

#[ORM\Table(name: 'checklist_information')]
#[ORM\Index(columns: ['checklist_id'], name: 'ix_checklist_information_checklist_id')]
#[ORM\Index(columns: ['created_by'], name: 'ix_checklist_information_created_by')]
#[ORM\Entity]
#[ORM\Index(columns: ['checklist_id'], name: 'ix_checklist_information_checklist_id')]
#[ORM\Index(columns: ['created_by'], name: 'ix_checklist_information_created_by')]
class ChecklistInformation
{
    use CreationAudit;

    #[JMS\Type('integer')]
    #[JMS\Groups(['checklist-information'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'checklist_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[JMS\Type('OPG\Digideps\Backend\Entity\Report\Checklist')]
    #[JMS\Groups(['checklist-information-checklist'])]
    #[ORM\JoinColumn(name: 'checklist_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Checklist::class, cascade: ['persist'], inversedBy: 'checklistInformation')]
    private Checklist $checklist;

    #[JMS\Groups(['checklist-information'])]
    #[ORM\Column(name: 'information', type: 'text', nullable: false)]
    private string $information;

    public function __construct(Checklist $checklist, string $information)
    {
        $this->setChecklist($checklist);
        $this->setInformation(trim($information));
    }

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function setId(int $id): static
    {
        if ($this->id === null) {
            $this->id = $id;
        } elseif ($id === 0) {
            throw new \DomainException('You may not set the id of an entity to zero.');
        } else {
            throw new \LogicException('You may not set the id of an entity more than once.');
        }

        return $this;
    }

    public function getChecklist(): Checklist
    {
        return $this->checklist;
    }

    public function setChecklist(Checklist $checklist): void
    {
        $this->checklist = $checklist;
    }

    public function getInformation(): string
    {
        return $this->information;
    }

    public function setInformation(string $information): void
    {
        $this->information = $information;
    }
}
