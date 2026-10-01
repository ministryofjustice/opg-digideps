<?php

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;

class ReportSubmissionSummary
{
    #[JMS\Type('integer')]
    private $id;

    #[JMS\Type('string')]
    private $caseNumber;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    private $dateReceived;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    private $scanDate;

    #[JMS\Type('string')]
    private $formType;

    #[JMS\Type('string')]
    private $documentType;

    #[JMS\Type('string')]
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
     */
    public function setId($id): static
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
     */
    public function setCaseNumber($caseNumber): static
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
     */
    public function setDateReceived($dateReceived): static
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
     */
    public function setScanDate($scanDate): static
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
     */
    public function setFormType($formType): static
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
     */
    public function setDocumentType($documentType): static
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
     */
    public function setDocumentId($documentId): static
    {
        $this->documentId = $documentId;

        return $this;
    }
}
