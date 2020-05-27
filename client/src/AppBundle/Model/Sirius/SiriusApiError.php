<?php declare(strict_types=1);


namespace AppBundle\Model\Sirius;

class SiriusApiError
{
    /** @var string */
    private $title, $code, $description;

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     * @return SiriusApiError
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     * @return SiriusApiError
     */
    public function setCode(?string $code): self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return SiriusApiError
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }
}
