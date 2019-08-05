<?php

namespace AppBundle\Entity\Report;

use JMS\Serializer\Annotation as JMS;

class ReportSubmissionSummary
{
    /**
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @JMS\Type("string")
     */
    private $caseNumber;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    private $dateReceived;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    private $scanDate;

    /**
     * @JMS\Type("string")
     */
    private $formType;

    /**
     * @JMS\Type("string")
     */
    private $documentType;

    /**
     * @JMS\Type("string")
     */
    private $documentId;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return ReportSubmissionSummary
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCaseNumber()
    {
        return $this->caseNumber;
    }

    /**
     * @param mixed $caseNumber
     * @return ReportSubmissionSummary
     */
    public function setCaseNumber($caseNumber)
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateReceived()
    {
        return $this->dateReceived;
    }

    /**
     * @param mixed $dateReceived
     * @return ReportSubmissionSummary
     */
    public function setDateReceived($dateReceived)
    {
        $this->dateReceived = $dateReceived;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getScanDate()
    {
        return $this->scanDate;
    }

    /**
     * @param mixed $scanDate
     * @return ReportSubmissionSummary
     */
    public function setScanDate($scanDate)
    {
        $this->scanDate = $scanDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormType()
    {
        return $this->formType;
    }

    /**
     * @param mixed $formType
     * @return ReportSubmissionSummary
     */
    public function setFormType($formType)
    {
        $this->formType = $formType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDocumentType()
    {
        return $this->documentType;
    }

    /**
     * @param mixed $documentType
     * @return ReportSubmissionSummary
     */
    public function setDocumentType($documentType)
    {
        $this->documentType = $documentType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDocumentId()
    {
        return $this->documentId;
    }

    /**
     * @param mixed $documentId
     * @return ReportSubmissionSummary
     */
    public function setDocumentId($documentId)
    {
        $this->documentId = $documentId;

        return $this;
    }
}
