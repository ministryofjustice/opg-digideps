<?php

namespace App\Entity\Report;

use App\Entity\Traits\CreationAudit;
use JMS\Serializer\Annotation as JMS;
use App\Validator\Constraints as AppAssert;

/**
 * Checklist Information
 */
class ChecklistInformation
{
    use CreationAudit;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\Groups({"checklist-information"})
     */
    private $id;

    /**
     * @var Checklist
     *
     * @JMS\Type("App\Entity\Report\Checklist")
     * @JMS\Groups({"checklist-information-checklist"})
     *
     */
    private $checklist;

    /**
     *
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"checklist-information"})
     * @AppAssert\TextNoSpecialCharacters
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
     * @param  int   $id
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
