<?php

namespace OPG\Digideps\Frontend\Entity\Report;

class UnsubmittedSection
{
    public function __construct(
        private readonly string $id,
        private readonly bool $present
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function isPresent(): bool
    {
        return $this->present;
    }
}
