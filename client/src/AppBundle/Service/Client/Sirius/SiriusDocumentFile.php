<?php declare(strict_types=1);


namespace AppBundle\Service\Client\Sirius;


class SiriusDocumentFile
{
    /** @var string */
    private $fileName;

    /** @var string */
    private $mimeType;

    /** @var string */
    private $source;

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     * @return SiriusDocumentFile
     */
    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     * @return SiriusDocumentFile
     */
    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     * @return SiriusDocumentFile
     */
    public function setSource(string $source): self
    {
        $this->source = $source;

        return $this;
    }
}
