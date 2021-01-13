<?php declare(strict_types=1);


namespace App\Model\Sirius;

use InvalidArgumentException;

class SiriusDocumentFile
{
    /** @var string */
    private $name;

    /** @var string */
    private $mimetype;

    /** @var string|null */
    private $source;

    /** @var string|null */
    private $s3Reference;

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
     * @return string|null
     */
    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * @param string|null $source
     * @return SiriusDocumentFile
     */
    public function setSource(?string $source): self
    {
        if (!is_null($source)) {
            // Ensure string is base64
            if (empty(base64_decode($source, true))) {
                throw new InvalidArgumentException('Source must be base64 encoded');
            }
        }

        $this->source = $source;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getS3Reference(): ?string
    {
        return $this->s3Reference;
    }

    /**
     * @param string|null $s3Reference
     * @return SiriusDocumentFile
     */
    public function setS3Reference(?string $s3Reference): self
    {
        $this->s3Reference = $s3Reference;

        return $this;
    }
}
