<?php

declare(strict_types=1);

namespace App\Sync\Model\Sirius;

use InvalidArgumentException;

class SiriusDocumentFile
{
    private string $name;
    private string $mimetype;
    private ?string $source;
    private ?string $s3Reference;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMimetype(): string
    {
        return $this->mimetype;
    }

    public function setMimetype(string $mimetype): self
    {
        $this->mimetype = $mimetype;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

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

    public function getS3Reference(): ?string
    {
        return $this->s3Reference;
    }

    public function setS3Reference(?string $s3Reference): self
    {
        $this->s3Reference = $s3Reference;

        return $this;
    }
}
