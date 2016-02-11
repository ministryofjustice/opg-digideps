<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

use Doctrine\ORM\QueryBuilder;

/**
 * @JMS\ExclusionPolicy("NONE")
 * @ORM\Table(name="concern")
 * @ORM\Entity
 */
class Concern 
{
    /**
     * @var integer
     *
     * @JMS\Groups({"basic"})
     * @JMS\Type("integer")
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="concern_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Report", inversedBy="concern")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    private $report;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"transactions","basic"})
     * @ORM\Column(name="do_you_expect_decisions", type="text",nullable=true)
     */
    private $doYouExpectFinancialDecisions;

    
    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"transactions","basic"})
     * @ORM\Column( name="do_you_have_concerns", type="text", nullable=true)
     */
    private $doYouHaveConcerns;

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
     * Set report
     *
     * @param Report $report
     * @return Contact
     */
    public function setReport(Report $report = null)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report
     *
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }


    public function getDoYouExpectFinancialDecisions()
    {
        return $this->doYouExpectFinancialDecisions;
    }

    public function getDoYouHaveConcerns()
    {
        return $this->doYouHaveConcerns;
    }

    public function setDoYouExpectFinancialDecisions($doYouExpectFinancialDecisions)
    {
        $this->doYouExpectFinancialDecisions = $doYouExpectFinancialDecisions;
        return $this;
    }

    public function setDoYouHaveConcerns($doYouHaveConcerns)
    {
        $this->doYouHaveConcerns = $doYouHaveConcerns;
        return $this;
    }



}
