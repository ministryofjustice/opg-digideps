<?php

namespace OPG\Digideps\Backend\v2\DTO;

use OPG\Digideps\Backend\Entity\Organisation;

class ClientDto
{
    private ?int $id = null;
    private ?string $caseNumber = null;
    private ?string $firstName = null;
    private ?string $lastName = null;
    private ?string $email = null;
    private ?\DateTime $archivedAt = null;
    private ?\DateTime $deletedAt = null;
    private int $reportCount = 0;
    private ?array $reports = null;
    private Organisation|OrganisationDto|array|null $organisation = null;
    private ?DeputyDto $deputy = null;
    private ?array $deputies = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCaseNumber(): ?string
    {
        return $this->caseNumber;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getArchivedAt(): ?\DateTime
    {
        return $this->archivedAt;
    }

    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    public function getReportCount(): ?int
    {
        return $this->reportCount;
    }

    public function getReports(): ?array
    {
        return $this->reports;
    }

    public function getOrganisation(): Organisation|OrganisationDto|array|null
    {
        return $this->organisation;
    }

    public function getDeputy(): ?DeputyDto
    {
        return $this->deputy;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function setCaseNumber(string $caseNumber): static
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function setEmail(string $email): static
    {
        $this->email = strtolower($email);

        return $this;
    }

    public function setArchivedAt(\DateTime $archivedAt): static
    {
        $this->archivedAt = $archivedAt;

        return $this;
    }

    public function setDeletedAt(\DateTime $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function setReportCount(int $reportCount): static
    {
        $this->reportCount = $reportCount;

        return $this;
    }

    public function setReports(array $reports): static
    {
        $this->reports = $reports;

        return $this;
    }

    public function setOrganisation(Organisation|OrganisationDto|array|null $organisation): static
    {
        $this->organisation = $organisation;

        return $this;
    }

    public function setDeputy(DeputyDto $deputy): static
    {
        $this->deputy = $deputy;

        return $this;
    }

    public function getDeputies(): ?array
    {
        return $this->deputies;
    }

    public function setDeputies(array $deputies): static
    {
        $this->deputies = $deputies;

        return $this;
    }
}
