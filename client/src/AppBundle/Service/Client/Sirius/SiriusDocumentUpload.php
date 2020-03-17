<?php declare(strict_types=1);


namespace AppBundle\Service\Client\Sirius;


class SiriusDocumentUpload
{
    /** @var string */
    private $type;

    /** @var SiriusReportPdfDocumentMetadata */
    private $attributes;

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
}
