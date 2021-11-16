<?php

declare(strict_types=1);

namespace App\Model\Sirius;

class SiriusDocumentUpload
{
    /** @var string */
    private $type;

    /** @var SiriusReportPdfDocumentMetadata */
    private $attributes;

    /** @var SiriusDocumentFile */
    private $file;

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return SiriusDocumentUpload
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAttributes(): ?SiriusMetadataInterface
    {
        return $this->attributes;
    }

    /**
     * @return SiriusDocumentUpload
     */
    public function setAttributes(?SiriusMetadataInterface $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getFile(): SiriusDocumentFile
    {
        return $this->file;
    }

    /**
     * @return SiriusDocumentUpload
     */
    public function setFile(SiriusDocumentFile $file): self
    {
        $this->file = $file;

        return $this;
    }
}
