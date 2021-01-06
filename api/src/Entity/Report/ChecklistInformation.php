<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Traits\CreationAudit;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Checklist.
 *
 * @ORM\Table(name="checklist_information",
 *     indexes={
 *     @ORM\Index(name="ix_checklist_information_checklist_id", columns={"checklist_id"}),
 *     @ORM\Index(name="ix_checklist_information_created_by", columns={"created_by"})
 *     })
 *
 * @ORM\Entity()
 */
class ChecklistInformation
{
    use CreationAudit;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\Groups({"checklist-information"})
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="checklist_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Checklist
     *
     * @JMS\Type("AppBundle\Entity\Report\Checklist")
     * @JMS\Groups({"checklist-information-checklist"})
     *
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\Checklist", inversedBy="checklistInformation", cascade={"persist"})
     * @ORM\JoinColumn(name="checklist_id", referencedColumnName="id", onDelete="CASCADE", nullable=false   )
     */
    private $checklist;

    /**
     * @var string
     *
     * @JMS\Groups({"checklist-information"})
     *
     * @ORM\Column(name="information", type="text", nullable=false)
     */
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
