<?php declare(strict_types=1);


namespace AppBundle\Model\Sirius;


class SiriusDocumentUpload
{
    /** @var string */
    private $type;

    /** @var SiriusReportPdfDocumentMetadata */
    private $attributes;

    /** @var SiriusDocumentFile */
    private $file;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return SiriusDocumentUpload
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return SiriusMetadataInterface
     */
    public function getAttributes(): SiriusMetadataInterface
    {
        return $this->attributes;
    }

    /**
     * @param SiriusMetadataInterface $attributes
     * @return SiriusDocumentUpload
     */
    public function setAttributes(SiriusMetadataInterface $attributes): self
    {
        $this->attributes = $attributes;

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
