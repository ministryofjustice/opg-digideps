<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

class UnsubmittedSection
{
    /**
     * Store section identifier
     */
    private string $id;

    /**
     * Store checkbox value
     */
    private bool $present;

    /**
     * UnsubmittedSection constructor
     */
    public function __construct(string $id, bool $present)
    {
        $this->id = $id;
        $this->present = $present;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function isPresent(): bool
    {
        return $this->present;
    }

    public function setPresent(bool $present): static
    {
        $this->present = $present;

        return $this;
    }
}
