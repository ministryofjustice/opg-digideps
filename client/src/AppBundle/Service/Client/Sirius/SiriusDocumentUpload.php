<?php declare(strict_types=1);


namespace AppBundle\Service\Client\Sirius;


class SiriusDocumentUpload
{
    /** @var string */
    private $caseRef;

    /** @var string */
    private $documentType;

    /** @var string */
    private $documentSubType;

    /** @var string */
    private $direction;

    /** @var SiriusDocumentMetadata */
    private $metadata;

    /** @var SiriusDocumentFile */
    private $file;

    /**
     * @return string
     */
    public function getCaseRef(): string
    {
        return $this->caseRef;
    }

    /**
     * @param string $caseRef
     * @return SiriusDocumentUpload
     */
    public function setCaseRef(string $caseRef): self
    {
        $this->caseRef = $caseRef;

        return $this;
    }

    /**
     * @return string
     */
    public function getDocumentType(): string
    {
        return $this->documentType;
    }

    /**
     * @param string $documentType
     * @return SiriusDocumentUpload
     */
    public function setDocumentType(string $documentType): self
    {
        $this->documentType = $documentType;

        return $this;
    }

    /**
     * @return string
     */
    public function getDocumentSubType(): string
    {
        return $this->documentSubType;
    }

    /**
     * @param string $documentSubType
     * @return SiriusDocumentUpload
     */
    public function setDocumentSubType(string $documentSubType): self
    {
        $this->documentSubType = $documentSubType;

        return $this;
    }

    /**
     * @return string
     */
    public function getDirection(): string
    {
        return $this->direction;
    }

    /**
     * @param string $direction
     * @return SiriusDocumentUpload
     */
    public function setDirection(string $direction): self
    {
        $this->direction = $direction;

        return $this;
    }

    /**
     * @return SiriusDocumentMetadata
     */
    public function getMetadata(): SiriusDocumentMetadata
    {
        return $this->metadata;
    }

    /**
     * @param SiriusDocumentMetadata $metadata
     * @return SiriusDocumentUpload
     */
    public function setMetadata(SiriusDocumentMetadata $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * @return SiriusDocumentFile
     */
    public function getFile(): SiriusDocumentFile
    {
        return $this->file;
    }

    /**
     * @param SiriusDocumentFile $file
     * @return SiriusDocumentUpload
     */
    public function setFile(SiriusDocumentFile $file): self
    {
        $this->file = $file;

        return $this;
    }
}
