<?php

declare(strict_types=1);

namespace App\Model\Sirius;

class SiriusApiError
{
    /** @var string */
    private $title;
    private $code;
    private $detail;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return SiriusApiError
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @return SiriusApiError
     */
    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    /**
     * @return SiriusApiError
     */
    public function setDetail(?string $detail): self
    {
        $this->detail = $detail;

        return $this;
    }
}
