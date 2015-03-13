<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DecisionInvolvement
 *
 * @ORM\Table(name="decision_involvement")
 * @ORM\Entity
 */
class DecisionInvolvement
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="decision_involvement_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="involvement", type="text", nullable=true)
     */
    private $involvement;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report", inversedBy="decisionInvolvements")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    private $report;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_edit", type="datetime", nullable=true)
     */
    private $lastedit;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set involvement
     *
     * @param string $involvement
     * @return DecisionInvolvement
     */
    public function setInvolvement($involvement)
    {
        $this->involvement = $involvement;

        return $this;
    }

    /**
     * Get involvement
     *
     * @return string 
     */
    public function getInvolvement()
    {
        return $this->involvement;
    }

    /**
     * Set lastedit
     *
     * @param \DateTime $lastedit
     * @return DecisionInvolvement
     */
    public function setLastedit($lastedit)
    {
        $this->lastedit = $lastedit;

        return $this;
    }

    /**
     * Get lastedit
     *
     * @return \DateTime 
     */
    public function getLastedit()
    {
        return $this->lastedit;
    }

    /**
     * Set report
     *
     * @param \AppBundle\Entity\Report $report
     * @return DecisionInvolvement
     */
    public function setReport(\AppBundle\Entity\Report $report = null)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report
     *
     * @return \AppBundle\Entity\Report 
     */
    public function getReport()
    {
        return $this->report;
    }
}
