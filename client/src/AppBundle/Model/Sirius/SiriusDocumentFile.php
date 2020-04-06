<?php declare(strict_types=1);


namespace AppBundle\Model\Sirius;


class SiriusDocumentFile
{
    /** @var string */
    private $filename;

    /** @var string */
    private $mimetype;

    /** @var string */
    private $source;

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return SiriusDocumentFile
     */
    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return string
     */
    public function getMimetype(): string
    {
        return $this->mimetype;
    }

    /**
     * @param string $filename
     * @return SiriusDocumentFile
     */
    public function setMimetype(string $mimetype): self
    {
        $this->mimetype = $mimetype;

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
