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

    /**
     * @var int
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['checklist-information'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'checklist_id_seq', allocationSize: 1, initialValue: 1)]
    private $id;

    /**
     * @var Checklist
     */
    #[JMS\Type('OPG\Digideps\Backend\Entity\Report\Checklist')]
    #[JMS\Groups(['checklist-information-checklist'])]
    #[ORM\JoinColumn(name: 'checklist_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Checklist::class, cascade: ['persist'], inversedBy: 'checklistInformation')]
    private $checklist;

    /**
     * @var string
     */
    #[JMS\Groups(['checklist-information'])]
    #[ORM\Column(name: 'information', type: 'text', nullable: false)]
    private $information;

    public function __construct(Checklist $checklist, $information)
    {
        $this->setChecklist($checklist);
        $this->setInformation(trim($information));
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Checklist
     */
    public function getChecklist()
    {
        return $this->checklist;
    }

    /**
     * @param Checklist $checklist
     */
    public function setChecklist($checklist)
    {
        $this->checklist = $checklist;
    }

    /**
     * @return string
     */
    public function getInformation()
    {
        return $this->information;
    }

    /**
     * @param string $information
     */
    public function setInformation($information)
    {
        $this->information = $information;
    }
}
