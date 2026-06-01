<?php

namespace OPG\Digideps\Backend\v2\DTO;

class ReportDto
{
    private ?int $id = null;
    private ?bool $submitted = null;
    private ?\DateTime $dueDate = null;
    private ?\DateTime $submitDate = null;
    private ?\DateTime $unSubmitDate = null;
    private ?\DateTime $startDate = null;
    private ?\DateTime $endDate = null;
    /** @var ?String[] $availableSections  */
    private ?array $availableSections = null;
    private ?StatusDto $status = null;
    private ?string $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubmitted(): ?bool
    {
        return $this->submitted;
    }

    public function getDueDate(): ?\DateTime
    {
        return $this->dueDate;
    }

    public function getSubmitDate(): ?\DateTime
    {
        return $this->submitDate;
    }

    public function getUnSubmitDate(): ?\DateTime
    {
        return $this->unSubmitDate;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function getAvailableSections(): ?array
    {
        return $this->availableSections;
    }

    public function getStatus(): ?StatusDto
    {
        return $this->status;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function setSubmitted(bool $submitted): static
    {
        $this->submitted = $submitted;

        return $this;
    }

    public function setDueDate(\DateTime $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function setSubmitDate(\DateTime $submitDate): static
    {
        $this->submitDate = $submitDate;

        return $this;
    }

    public function setUnSubmitDate(\DateTime $unSubmitDate): static
    {
        $this->unSubmitDate = $unSubmitDate;

        return $this;
    }

    public function setStartDate(\DateTime $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function setEndDate(\DateTime $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }
    /** @param String[] $availableSections */
    public function setAvailableSections(array $availableSections): static
    {
        $this->availableSections = $availableSections;

        return $this;
    }

    public function setStatus(StatusDto $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }
}
