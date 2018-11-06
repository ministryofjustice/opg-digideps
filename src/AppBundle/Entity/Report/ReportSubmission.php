<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\ReportInterface;
use AppBundle\Entity\Traits\CreationAudit;
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
     * @var Report
     *
     * @JMS\Type("AppBundle\Entity\Ndr\Ndr")
     */
    private $ndr;

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
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $downloadable;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  int              $id
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
     * @param  Report           $report
     * @return ReportSubmission
     */
    public function setReport($report)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return Report
     */
    public function getNdr()
    {
        return $this->ndr;
    }

    /**
     * @param  Report           $ndr
     * @return ReportSubmission
     */
    public function setNdr($ndr)
    {
        $this->ndr = $ndr;

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
     * @param  array            $documents
     * @return ReportSubmission
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasReportPdf()
    {
        foreach ($this->documents as $document) {
            if($document->isReportPdf()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return User
     */
    public function getArchivedBy()
    {
        return $this->archivedBy;
    }

    /**
     * @param  User             $archivedBy
     * @return ReportSubmission
     */
    public function setArchivedBy($archivedBy)
    {
        $this->archivedBy = $archivedBy;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDownloadable()
    {
        return $this->downloadable;
    }

    /**
     * @param bool $downloadable
     *
     * @return ReportSubmission
     */
    public function setDownloadable($downloadable)
    {
        $this->downloadable = $downloadable;

        return $this;
    }

    /**
     * @return string
     */
    public function getZipName()
    {
        /* @var $report ReportInterface */
        $report = $this->getReport() ? $this->getReport() : $this->getNdr();
        $client = $report->getClient();

        return ($report instanceof Ndr ? 'NdrReport-' : 'Report_')
            . $client->getCaseNumber()
            . '_' . $report->getStartDate()->format('Y')
            . ($report instanceof Ndr ? '' : ('_' . $report->getEndDate()->format('Y')))
            // DDPB-2049 add report submission id to avoid collision with multi downloads
            . '_' . $this->getId()
            . '.zip';
    }
}
