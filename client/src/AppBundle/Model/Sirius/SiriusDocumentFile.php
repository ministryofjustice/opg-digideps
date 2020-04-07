<?php declare(strict_types=1);


namespace AppBundle\Model\Sirius;

use InvalidArgumentException;

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
        // Ensure string is base64
        if(empty(htmlspecialchars(base64_decode($source, true)))) {
            throw new InvalidArgumentException('Source must be base64 encoded');
        }

        $this->source = $source;

        return $this;
    }
}
