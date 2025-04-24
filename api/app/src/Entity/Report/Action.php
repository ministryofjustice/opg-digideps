<?php

namespace App\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="action")
 *
 * @ORM\Entity
 */
class Action
{
    /**
     * @var int
     *
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="action_id_seq", allocationSize=1, initialValue=1)
     */
    #[JMS\Type('integer')]
    private $id;

    /**
     * @var Report
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Report\Report", inversedBy="action")
     *
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * @var string yes|no|null
     *
     *
     *
     * @ORM\Column(name="do_you_expect_decisions", type="string", length=4, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['action'])]
    private $doYouExpectFinancialDecisions;

    /**
     * @var string yes|no|null
     *
     *
     *
     * @ORM\Column(name="do_you_expect_decisions_details", type="text", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['action'])]
    private $doYouExpectFinancialDecisionsDetails;

    /**
     * @var string yes|no|null
     *
     *
     *
     * @ORM\Column( name="do_you_have_concerns", type="string", length=4, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['action'])]
    private $doYouHaveConcerns;

    /**
     * @var string yes|no|null
     *
     *
     *
     * @ORM\Column( name="do_you_have_concerns_details", type="text", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['action'])]
    private $doYouHaveConcernsDetails;

    public function __construct(Report $report)
    {
        $this->report = $report;
        $report->setAction($this);
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set report.
     *
     * @param Report $report
     *
     * @return Contact
     */
    public function setReport(Report $report = null)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report.
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

    public function getDoYouExpectFinancialDecisionsDetails()
    {
        return $this->doYouExpectFinancialDecisionsDetails;
    }

    public function getDoYouHaveConcernsDetails()
    {
        return $this->doYouHaveConcernsDetails;
    }

    public function setDoYouExpectFinancialDecisionsDetails($doYouExpectFinancialDecisionsDetails)
    {
        $this->doYouExpectFinancialDecisionsDetails = $doYouExpectFinancialDecisionsDetails;

        return $this;
    }

    public function setDoYouHaveConcernsDetails($doYouHaveConcernsDetails)
    {
        $this->doYouHaveConcernsDetails = $doYouHaveConcernsDetails;

        return $this;
    }

    public function cleanUpUnusedData()
    {
        if ('no' == $this->doYouExpectFinancialDecisions) {
            $this->doYouExpectFinancialDecisionsDetails = null;
        }

        if ('no' == $this->doYouHaveConcerns) {
            $this->doYouHaveConcernsDetails = null;
        }
    }
}
