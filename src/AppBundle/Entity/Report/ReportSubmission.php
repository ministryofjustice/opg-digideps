<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Traits\CreationAudit;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

class ReportSubmission
{
    use CreationAudit;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @var Report
     *
     * @JMS\Type("AppBundle\Entity\Report\Report")
     */
    private $report;

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\Document>")
     */
    private $documents;

    /**
     * @var User
     *
     * @JMS\Type("AppBundle\Entity\User")
     */
    private $archivedBy;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ReportSubmission
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param Report $report
     * @return ReportSubmission
     */
    public function setReport($report)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return Document[]
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param array $documents
     * @return ReportSubmission
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;

        return $this;
    }

    /**
     * @return User
     */
    public function getArchivedBy()
    {
        return $this->archivedBy;
    }

    /**
     * @param User $archivedBy
     * @return ReportSubmission
     */
    public function setArchivedBy($archivedBy)
    {
        $this->archivedBy = $archivedBy;

        return $this;
    }


    /**
     * @return string
     */
    public function getZipName()
    {
        $report = $this->getReport();
        $client = $this->getReport()->getClient();

        return 'Report_' . $client->getCaseNumber() . '_' . $report->getStartDate()->format('Y') . '_' . $report->getEndDate()->format('Y') . '.zip';

    }

}
