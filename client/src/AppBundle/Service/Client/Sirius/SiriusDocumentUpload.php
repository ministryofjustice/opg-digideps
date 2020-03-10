<?php declare(strict_types=1);


namespace AppBundle\Service\Client\Sirius;


class SiriusDocumentUpload
{
    /** @var string */
    private $type;

    /** @var SiriusDocumentMetadata */
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
     * @return SiriusDocumentMetadata
     */
    public function getAttributes(): SiriusDocumentMetadata
    {
        return $this->attributes;
    }

    /**
     * @param SiriusDocumentMetadata $metadata
     * @return SiriusDocumentUpload
     */
    public function setAttributes(SiriusDocumentMetadata $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }
}
