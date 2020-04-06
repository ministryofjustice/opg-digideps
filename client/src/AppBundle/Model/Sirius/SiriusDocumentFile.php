<?php declare(strict_types=1);


namespace AppBundle\Model\Sirius;


class SiriusDocumentFile
{
    /** @var string */
    private $name;

    /** @var string */
    private $mimetype;

    /** @var string */
    private $source;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return SiriusDocumentFile
     */
    public function setName(string $name): self
    {
        $this->name = $name;

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
     * @param string $mimetype
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
